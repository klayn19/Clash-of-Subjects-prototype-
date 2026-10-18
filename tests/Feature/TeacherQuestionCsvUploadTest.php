<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherQuestionCsvUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('section')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('subject');
            $table->string('type');
            $table->integer('quarter');
            $table->integer('sequence_number');
            $table->text('question');
            $table->string('choice_a');
            $table->string('choice_b');
            $table->string('choice_c');
            $table->string('choice_d');
            $table->string('answer');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_teacher_can_append_csv_questions_to_each_supported_type(): void
    {
        [$teacherId, $classId] = $this->createTeacherClass();
        DB::table('questions')->insert($this->questionRow($classId, 'Existing question?'));

        foreach (['assessment', 'exam', 'quiz'] as $type) {
            $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
                ->post('/backend/bulk_upload_questions.php', [
                    'class_id' => $classId,
                    'subject' => 'math',
                    'type' => $type,
                    'quarter' => 2,
                    'sequence_number' => 1,
                    'questions_file' => UploadedFile::fake()->createWithContent(
                        'questions.csv',
                        "question,choice_a,choice_b,choice_c,choice_d,answer\n\"What is 2 + 2?\",3,4,5,6,B\n\"Which value has a comma, too?\",one,two,three,four,D\n"
                    ),
                ], ['Accept' => 'application/json'])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('inserted', 2);
        }

        $this->assertDatabaseCount('questions', 7);
        $this->assertDatabaseHas('questions', [
            'question' => 'Existing question?',
            'type' => 'quiz',
        ]);
        $this->assertDatabaseHas('questions', [
            'question' => 'Which value has a comma, too?',
            'type' => 'assessment',
            'answer' => 'D',
        ]);
        $this->assertDatabaseHas('questions', [
            'question' => 'What is 2 + 2?',
            'type' => 'exam',
            'choice_b' => '4',
            'answer' => 'B',
        ]);
    }

    public function test_invalid_csv_row_rejects_entire_upload_without_inserting_partial_questions(): void
    {
        [$teacherId, $classId] = $this->createTeacherClass();
        DB::table('questions')->insert($this->questionRow($classId, 'Existing question?'));

        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->post('/backend/bulk_upload_questions.php', [
                'class_id' => $classId,
                'subject' => 'english',
                'type' => 'quiz',
                'quarter' => 1,
                'sequence_number' => 1,
                'questions_file' => UploadedFile::fake()->createWithContent(
                    'questions.csv',
                    "question,choice_a,choice_b,choice_c,choice_d,answer\nValid question?,A,B,C,D,A\nInvalid question?,A,B,C,D,E\n"
                ),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'CSV row 3 answer must be A, B, C, or D.');

        $this->assertDatabaseCount('questions', 1);
    }

    public function test_teacher_cannot_upload_to_another_teachers_class(): void
    {
        [$teacherId, $classId] = $this->createTeacherClass();
        $otherTeacherId = DB::table('users')->insertGetId([
            'first_name' => 'Other',
            'last_name' => 'Teacher',
            'email' => 'other-teacher@example.test',
            'password' => 'unused',
            'role' => 'teacher',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('classes')->where('id', $classId)->update(['teacher_id' => $otherTeacherId]);

        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->post('/backend/bulk_upload_questions.php', [
                'class_id' => $classId,
                'subject' => 'english',
                'type' => 'quiz',
                'quarter' => 1,
                'sequence_number' => 1,
                'questions_file' => UploadedFile::fake()->createWithContent(
                    'questions.csv',
                    "question,choice_a,choice_b,choice_c,choice_d,answer\nQuestion?,A,B,C,D,A\n"
                ),
            ], ['Accept' => 'application/json'])
            ->assertNotFound()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_non_teacher_cannot_upload_questions(): void
    {
        [, $classId] = $this->createTeacherClass();

        $this->withSession(['user_id' => 999, 'user_role' => 'student'])
            ->post('/backend/bulk_upload_questions.php', [
                'class_id' => $classId,
                'subject' => 'english',
                'type' => 'quiz',
                'quarter' => 1,
                'sequence_number' => 1,
                'questions_file' => UploadedFile::fake()->createWithContent(
                    'questions.csv',
                    "question,choice_a,choice_b,choice_c,choice_d,answer\nQuestion?,A,B,C,D,A\n"
                ),
            ], ['Accept' => 'application/json'])
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('questions', 0);
    }

    private function createTeacherClass(): array
    {
        $teacherId = DB::table('users')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'Teacher',
            'email' => 'teacher@example.test',
            'password' => 'unused',
            'role' => 'teacher',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classId = DB::table('classes')->insertGetId([
            'name' => 'Grade 7',
            'section' => 'Grade 7',
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$teacherId, $classId];
    }

    private function questionRow(int $classId, string $question): array
    {
        return [
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'quiz',
            'quarter' => 2,
            'sequence_number' => 1,
            'question' => $question,
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
