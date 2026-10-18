<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrototypeRemovalTest extends TestCase
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

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
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

        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('subject');
            $table->string('type');
            $table->integer('quarter');
            $table->integer('sequence_number');
            $table->integer('correct');
            $table->integer('total');
            $table->decimal('percent', 5, 2);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('student_scores');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_prototype_is_rejected_by_question_and_score_apis(): void
    {
        $teacherId = $this->createUser('teacher', 'teacher@example.test');
        $studentId = $this->createUser('student', 'student@example.test');
        $classId = DB::table('classes')->insertGetId([
            'name' => 'Grade 7',
            'section' => 'Grade 7',
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_questions.php', [
            'class_id' => $classId,
            'subject' => 'english',
            'type' => 'prototype',
            'quarter' => 1,
            'sequence_number' => 1,
            'questions' => [[
                'question' => 'Question?',
                'choice_a' => 'A',
                'choice_b' => 'B',
                'choice_c' => 'C',
                'choice_d' => 'D',
                'answer' => 'A',
            ]],
        ])->assertUnprocessable()->assertJsonValidationErrors('type');

        $this->postJson('/api/save_score', [
            'student_id' => $studentId,
            'class_id' => $classId,
            'subject' => 'english',
            'type' => 'prototype',
            'quarter' => 1,
            'sequence_number' => 1,
            'correct' => 1,
            'total' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('type');

        $this->postJson('/backend/save_prototype_grade.php', [
            'student_id' => $studentId,
            'grade' => 80,
        ])->assertNotFound();
    }

    public function test_gameplay_question_lookup_never_falls_back_to_legacy_prototype_questions(): void
    {
        DB::table('questions')->insert([
            'class_id' => null,
            'subject' => 'prototype',
            'type' => 'prototype',
            'quarter' => 1,
            'sequence_number' => 1,
            'question' => 'Legacy prototype question?',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/get_question?subject=english')
            ->assertNotFound()
            ->assertJsonPath('completed', true);
    }

    public function test_teacher_dashboard_no_longer_displays_prototype_tools_or_grades(): void
    {
        $teacherId = $this->createUser('teacher', 'dashboard.teacher@example.test');

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->get('/teacher/dashboard')
            ->assertOk()
            ->assertDontSee('PROTOTYPE');
    }

    private function createUser(string $role, string $email): int
    {
        return DB::table('users')->insertGetId([
            'first_name' => ucfirst($role),
            'last_name' => 'Test',
            'email' => $email,
            'password' => 'unused',
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
