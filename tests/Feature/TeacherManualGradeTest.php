<?php

namespace Tests\Feature;

use App\Mail\ParentLowGradeAlert;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            $table->string('parent_email')->nullable();
            $table->string('password');
            $table->string('role');
            $table->string('section')->nullable();
            $table->string('subject')->nullable();
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
        Mail::fake();
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

        Mail::assertNothingSent();
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
            'subject' => 'math',
            'quarter' => 1,
            'grade' => 95,
        ])->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('student_scores', 0);
    }

    public function test_student_game_score_without_class_id_is_assigned_to_their_matching_subject_class(): void
    {
        [$teacherId, $studentId, $classId] = $this->createClassRoster();

        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
        ])->postJson('/api/save_score', [
            'subject' => 'math',
            'type' => 'quiz',
            'quarter' => 2,
            'sequence_number' => 3,
            'correct' => 8,
            'total' => 10,
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('student_scores', [
            'student_id' => $studentId,
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'quiz',
            'quarter' => 2,
            'sequence_number' => 3,
        ]);
    }

    public function test_teacher_submissions_include_legacy_scores_for_students_in_the_selected_class(): void
    {
        [$teacherId, $studentId, $classId] = $this->createClassRoster();
        DB::table('student_scores')->insert([
            'student_id' => $studentId,
            'class_id' => null,
            'subject' => 'math',
            'type' => 'exam',
            'quarter' => 1,
            'sequence_number' => 1,
            'correct' => 6,
            'total' => 10,
            'percent' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->getJson('/backend/get_student_analytics.php?class_id=' . $classId)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('analytics.0.first_name', 'Student')
            ->assertJsonPath('analytics.0.type', 'exam')
            ->assertJsonPath('analytics.0.correct', 6);
    }

    public function test_saving_a_failing_grade_sends_a_parent_email(): void
    {
        Mail::fake();
        [$teacherId, $studentId, $classId] = $this->createClassRoster('parent@example.test');

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_manual_grade.php', [
            'class_id' => $classId,
            'student_id' => $studentId,
            'subject' => 'math',
            'quarter' => 2,
            'grade' => 74,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('notification_status', 'sent');

        Mail::assertSent(ParentLowGradeAlert::class, function (ParentLowGradeAlert $mail) {
            return $mail->hasTo('parent@example.test')
                && $mail->studentName === 'Student Test'
                && $mail->subjectName === 'math'
                && $mail->grade === 74.0
                && $mail->quarter === 2;
        });
    }

    public function test_failing_grade_is_saved_with_a_warning_when_parent_email_is_missing(): void
    {
        Mail::fake();
        [$teacherId, $studentId, $classId] = $this->createClassRoster();

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_manual_grade.php', [
            'class_id' => $classId,
            'student_id' => $studentId,
            'subject' => 'math',
            'quarter' => 1,
            'grade' => 60,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('notification_status', 'missing_parent_email')
            ->assertJsonPath('message', 'Grade saved, but no parent/guardian email is registered, so no alert was sent.');

        $this->assertDatabaseHas('student_scores', [
            'student_id' => $studentId,
            'type' => 'grade',
            'percent' => 60,
        ]);
        Mail::assertNothingSent();
    }

    private function createClassRoster(?string $parentEmail = null, string $teacherSubject = 'math'): array
    {
        $teacherId = $this->createUser('teacher', 'teacher@example.test', null);
        DB::table('users')->where('id', $teacherId)->update(['subject' => $teacherSubject]);
        $studentId = $this->createUser('student', 'student@example.test', 'Grade 7', $parentEmail);
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

    private function createUser(string $role, string $email, ?string $section, ?string $parentEmail = null): int
    {
        return DB::table('users')->insertGetId([
            'lrn' => null,
            'first_name' => ucfirst($role),
            'last_name' => 'Test',
            'email' => $email,
            'parent_email' => $parentEmail,
            'password' => 'unused',
            'role' => $role,
            'section' => $section,
            'subject' => $role === 'teacher' ? 'math' : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
