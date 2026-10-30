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
            $table->string('section')->nullable();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('section')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->timestamps();
        });

        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('student_id');
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
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_teacher_can_append_csv_questions_to_each_supported_type(): void
    {
        [$teacherId, $classId] = $this->createTeacherClass();
        DB::table('questions')->insert($this->questionRow($classId, 'Existing question?'));

        foreach (['assessment', 'quizbee', 'exam', 'quiz'] as $type) {
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

        $this->assertDatabaseCount('questions', 9);
        $this->assertSame(4, DB::table('questions')->where('type', 'quizbee')->count());
        $this->assertDatabaseHas('questions', [
            'question' => 'Existing question?',
            'type' => 'quiz',
        ]);
        $this->assertDatabaseHas('questions', [
            'question' => 'Which value has a comma, too?',
            'type' => 'quizbee',
            'answer' => 'D',
        ]);
        $this->assertDatabaseHas('questions', [
            'question' => 'What is 2 + 2?',
            'type' => 'exam',
            'choice_b' => '4',
            'answer' => 'B',
        ]);

        $response = $this->get('/api/get_question?class_id=' . $classId . '&subject=math&type=assessment&quarter=2&sequence_number=1')
            ->assertOk();
        $this->assertContains($response->json('question'), [
            'What is 2 + 2?',
            'Which value has a comma, too?',
        ]);
    }

    public function test_student_without_explicit_class_id_can_fetch_questions_from_any_linked_class(): void
    {
        [$teacherId, $firstClassId] = $this->createTeacherClass();
        $secondClassId = DB::table('classes')->insertGetId([
            'name' => 'Grade 7 English',
            'section' => 'Grade 7',
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@example.test',
            'password' => 'unused',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('class_students')->insert([
            ['class_id' => $firstClassId, 'student_id' => $studentId, 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => $secondClassId, 'student_id' => $studentId, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('questions')->insert(array_replace(
            $this->questionRow($secondClassId, 'Which sentence is an exam question?'),
            ['subject' => 'english', 'type' => 'exam', 'quarter' => 1]
        ));

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->get('/api/get_question?subject=english&type=exam&quarter=1')
            ->assertOk()
            ->assertJsonPath('question', 'Which sentence is an exam question?');
    }

    public function test_student_class_membership_takes_precedence_over_stale_unlinked_class_id(): void
    {
        [, $classId] = $this->createTeacherClass();
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'stale-class-student@example.test',
            'password' => 'unused',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('class_students')->insert([
            'class_id' => $classId,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('questions')->insert(array_replace(
            $this->questionRow($classId, 'Which question matches the enrolled class?'),
            ['subject' => 'english', 'type' => 'quizbee', 'quarter' => 1]
        ));

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->get('/api/get_question?class_id=99&subject=english&type=assessment&quarter=1')
            ->assertOk()
            ->assertJsonPath('question', 'Which question matches the enrolled class?');
    }

    public function test_quiz_number_endpoint_returns_distinct_numbers_for_selected_question_set(): void
    {
        [, $classId] = $this->createTeacherClass();
        DB::table('questions')->insert([
            array_replace($this->questionRow($classId, 'Quizbee question one A'), [
                'subject' => 'english',
                'type' => 'quizbee',
                'quarter' => 1,
            ]),
            array_replace($this->questionRow($classId, 'Quizbee question one B'), [
                'sequence_number' => 1,
                'subject' => 'english',
                'type' => 'quizbee',
                'quarter' => 1,
            ]),
            array_replace($this->questionRow($classId, 'Quizbee question two'), [
                'sequence_number' => 2,
                'subject' => 'english',
                'type' => 'quizbee',
                'quarter' => 1,
            ]),
            array_replace($this->questionRow($classId, 'Other quarter'), [
                'sequence_number' => 3,
                'subject' => 'english',
                'type' => 'quizbee',
                'quarter' => 2,
            ]),
            array_replace($this->questionRow($classId, 'Other subject'), [
                'sequence_number' => 4,
                'subject' => 'math',
                'type' => 'quizbee',
                'quarter' => 1,
            ]),
        ]);

        $this->getJson('/backend/get_quiz_numbers.php?class_id=' . $classId . '&subject=English&type=assessment&quarter=1')
            ->assertOk()
            ->assertExactJson(['quiz_numbers' => [1, 2]]);
    }

    public function test_quiz_number_endpoint_returns_empty_list_when_no_questions_match(): void
    {
        [, $classId] = $this->createTeacherClass();

        $this->getJson('/backend/get_quiz_numbers.php?class_id=' . $classId . '&subject=english&type=quiz&quarter=1')
            ->assertOk()
            ->assertExactJson(['quiz_numbers' => []]);
    }

    public function test_quiz_number_endpoint_uses_student_class_scope_instead_of_requested_class(): void
    {
        [$teacherId, $linkedClassId] = $this->createTeacherClass();
        $otherClassId = DB::table('classes')->insertGetId([
            'name' => 'Other class',
            'section' => 'Grade 8',
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'quiz-number-student@example.test',
            'password' => 'unused',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('class_students')->insert([
            'class_id' => $linkedClassId,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('questions')->insert([
            array_replace($this->questionRow($linkedClassId, 'Linked class question'), [
                'subject' => 'english',
                'type' => 'quizbee',
                'quarter' => 1,
                'sequence_number' => 1,
            ]),
            array_replace($this->questionRow($otherClassId, 'Other class question'), [
                'subject' => 'english',
                'type' => 'quizbee',
                'quarter' => 1,
                'sequence_number' => 2,
            ]),
        ]);

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->getJson('/backend/get_quiz_numbers.php?class_id=' . $otherClassId . '&subject=english&type=assessment&quarter=1')
            ->assertOk()
            ->assertExactJson(['quiz_numbers' => [1]]);
    }

    public function test_quiz_number_filters_question_fetch_using_question_sequence_number(): void
    {
        [, $classId] = $this->createTeacherClass();
        DB::table('questions')->insert([
            array_replace($this->questionRow($classId, 'Question in quiz one'), [
                'sequence_number' => 1,
            ]),
            array_replace($this->questionRow($classId, 'Question in quiz two'), [
                'sequence_number' => 2,
            ]),
        ]);

        $this->getJson('/api/get_question?class_id=' . $classId . '&subject=math&type=quiz&quarter=2&quiz_number=2')
            ->assertOk()
            ->assertJsonPath('question', 'Question in quiz two');
    }

    public function test_student_can_fetch_questions_from_section_class_without_roster_link(): void
    {
        [, $classId] = $this->createTeacherClass();
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'unlinked-student@example.test',
            'password' => 'unused',
            'role' => 'student',
            'section' => 'Grade 7',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('questions')->insert(array_replace(
            $this->questionRow($classId, 'Which exam question belongs to this section?'),
            ['subject' => 'english', 'type' => 'exam', 'quarter' => 1]
        ));

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->get('/api/get_question?subject=english&type=exam&quarter=1')
            ->assertOk()
            ->assertJsonPath('question', 'Which exam question belongs to this section?');
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
