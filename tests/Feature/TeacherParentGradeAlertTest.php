<?php

namespace Tests\Feature;

use App\Mail\ParentLowGradeAlert;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherParentGradeAlertTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('parent_email')->nullable();
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
            $table->decimal('correct', 6, 2);
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

    public function test_teacher_can_filter_class_scores_by_quarter(): void
    {
        [$teacherId, $studentId, $classId] = $this->createClassRoster('parent@example.test');
        $this->createScore($classId, $studentId, 'english', 'grade', 1, 60, 100);
        $this->createScore($classId, $studentId, 'math', 'quiz', 2, 90, 100);

        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->getJson('/backend/get_class_students.php?class_id=' . $classId . '&quarter=1')
            ->assertOk()
            ->assertJsonPath('students.0.score', 60)
            ->assertJsonPath('students.0.quarter', 'Q1')
            ->assertJsonPath('students.0.subject', 'Multiple')
            ->assertJsonPath('students.0.has_parent_email', true);
    }

    public function test_teacher_can_email_parent_for_failing_filtered_score(): void
    {
        Mail::fake();
        [$teacherId, $studentId, $classId] = $this->createClassRoster('parent@example.test');
        $this->createScore($classId, $studentId, 'english', 'grade', 1, 60, 100);
        $this->createScore($classId, $studentId, 'english', 'grade', 2, 95, 100);

        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->postJson('/backend/email_parent_low_grade.php', [
                'class_id' => $classId,
                'student_id' => $studentId,
                'subject' => 'english',
                'type' => 'grade',
                'quarter' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Mail::assertSent(ParentLowGradeAlert::class, function (ParentLowGradeAlert $mail) {
            return $mail->hasTo('parent@example.test')
                && $mail->studentName === 'Student Student'
                && $mail->grade === 60.0
                && $mail->quarter === 1;
        });
    }

    public function test_parent_alert_rejects_non_failing_scores_missing_parent_email_and_wrong_teacher(): void
    {
        Mail::fake();
        [$teacherId, $studentId, $classId] = $this->createClassRoster('parent@example.test');
        $this->createScore($classId, $studentId, 'english', 'grade', 1, 75, 100);

        $payload = [
            'class_id' => $classId,
            'student_id' => $studentId,
            'subject' => 'english',
            'type' => 'grade',
            'quarter' => '1',
        ];
        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->postJson('/backend/email_parent_low_grade.php', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        DB::table('users')->where('id', $studentId)->update(['parent_email' => null]);
        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->postJson('/backend/email_parent_low_grade.php', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $otherTeacherId = $this->createUser('teacher', 'other@example.test');
        DB::table('classes')->where('id', $classId)->update(['teacher_id' => $otherTeacherId]);
        DB::table('student_scores')->where('class_id', $classId)->update(['correct' => 60, 'percent' => 60]);
        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->postJson('/backend/email_parent_low_grade.php', $payload)
            ->assertNotFound()
            ->assertJsonPath('success', false);

        Mail::assertNothingSent();
    }

    private function createClassRoster(string $parentEmail): array
    {
        $teacherId = $this->createUser('teacher', 'teacher@example.test');
        $studentId = $this->createUser('student', 'student@example.test', $parentEmail);
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

    private function createUser(string $role, string $email, ?string $parentEmail = null): int
    {
        return DB::table('users')->insertGetId([
            'first_name' => ucfirst($role),
            'last_name' => 'Student',
            'email' => $email,
            'parent_email' => $parentEmail,
            'password' => 'unused',
            'role' => $role,
            'section' => 'Grade 7',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createScore(int $classId, int $studentId, string $subject, string $type, int $quarter, int $correct, int $total): void
    {
        DB::table('student_scores')->insert([
            'student_id' => $studentId,
            'class_id' => $classId,
            'subject' => $subject,
            'type' => $type,
            'quarter' => $quarter,
            'sequence_number' => 1,
            'correct' => $correct,
            'total' => $total,
            'percent' => ($correct / $total) * 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
