<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherManualGradeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('lrn')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('age')->nullable();
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

        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('subject');
            $table->string('type');
            $table->integer('quarter');
            $table->integer('sequence_number');
            $table->decimal('correct', 5, 2);
            $table->integer('total');
            $table->decimal('percent', 5, 2);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('student_scores');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_teacher_can_save_a_separate_grade_for_an_enrolled_student(): void
    {
        [$teacherId, $studentId, $classId] = $this->createClassRoster();

        $payload = [
            'class_id' => $classId,
            'student_id' => $studentId,
            'subject' => 'math',
            'quarter' => 2,
            'grade' => 88,
        ];

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_manual_grade.php', $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('student_scores', [
            'student_id' => $studentId,
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'grade',
            'quarter' => 2,
            'sequence_number' => 1,
            'correct' => 88,
            'total' => 100,
            'percent' => 88,
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_manual_grade.php', $payload)
            ->assertOk();

        $this->assertDatabaseHas('student_scores', [
            'student_id' => $studentId,
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'grade',
            'quarter' => 2,
            'sequence_number' => 2,
        ]);

        $adminId = $this->createUser('admin', 'admin@example.test', null);
        $this->withSession([
            'user_id' => $adminId,
            'user_role' => 'admin',
        ])->getJson('/backend/admin/get_student_grades/' . $studentId)
            ->assertOk()
            ->assertJsonPath('scores.0.type', 'grade');
    }

    public function test_teacher_cannot_save_grade_for_a_student_outside_their_class(): void
    {
        [$teacherId, , $classId] = $this->createClassRoster();
        $otherStudentId = $this->createUser('student', 'other.student@example.test', null);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_manual_grade.php', [
            'class_id' => $classId,
            'student_id' => $otherStudentId,
            'subject' => 'english',
            'quarter' => 1,
            'grade' => 95,
        ])->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('student_scores', 0);
    }

    private function createClassRoster(): array
    {
        $teacherId = $this->createUser('teacher', 'teacher@example.test', null);
        $studentId = $this->createUser('student', 'student@example.test', 'Grade 7');
        $classId = DB::table('classes')->insertGetId([
            'name' => 'Grade 7',
            'section' => 'Grade 7',
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('class_students')->insert([
            'class_id' => $classId,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$teacherId, $studentId, $classId];
    }

    private function createUser(string $role, string $email, ?string $section): int
    {
        return DB::table('users')->insertGetId([
            'lrn' => null,
            'first_name' => ucfirst($role),
            'last_name' => 'Test',
            'email' => $email,
            'password' => 'unused',
            'role' => $role,
            'section' => $section,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
