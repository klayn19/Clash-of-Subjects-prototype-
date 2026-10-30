<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('role');
            $table->unsignedInteger('multiplayer_wins')->default(0);
        });
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('section')->nullable();
            $table->unsignedBigInteger('teacher_id');
        });
        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('student_id');
        });
        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('subject');
            $table->decimal('percent', 5, 2);
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

    public function test_student_can_view_their_class_leaderboard_ranked_by_average_and_wins(): void
    {
        $teacherId = $this->addUser('Teacher', 'One', 'teacher');
        $classId = DB::table('classes')->insertGetId([
            'name' => 'Grade 8 - A',
            'section' => 'Grade 8 - A',
            'teacher_id' => $teacherId,
        ]);
        $scoreTieId = $this->addUser('Alex', 'Able', 'student', 1);
        $tieWinnerId = $this->addUser('Blake', 'Baker', 'student', 6);
        $noScoreId = $this->addUser('Casey', 'Clark', 'student', 20);

        foreach ([$scoreTieId, $tieWinnerId, $noScoreId] as $studentId) {
            DB::table('class_students')->insert(['class_id' => $classId, 'student_id' => $studentId]);
        }
        DB::table('student_scores')->insert([
            ['student_id' => $scoreTieId, 'class_id' => $classId, 'subject' => 'math', 'percent' => 80],
            ['student_id' => $scoreTieId, 'class_id' => $classId, 'subject' => 'math', 'percent' => 100],
            ['student_id' => $tieWinnerId, 'class_id' => $classId, 'subject' => 'math', 'percent' => 90],
            ['student_id' => $tieWinnerId, 'class_id' => $classId, 'subject' => 'science', 'percent' => 100],
        ]);

        $this->withSession(['user_id' => $scoreTieId, 'user_role' => 'student'])
            ->getJson("/leaderboard?class_id={$classId}&subject=math")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('students.0.student_id', $tieWinnerId)
            ->assertJsonPath('students.0.average_percent', 90)
            ->assertJsonPath('students.0.multiplayer_wins', 6)
            ->assertJsonPath('students.1.student_id', $scoreTieId)
            ->assertJsonPath('students.1.average_percent', 90)
            ->assertJsonPath('students.2.student_id', $noScoreId)
            ->assertJsonPath('students.2.average_percent', null);
    }

    public function test_student_cannot_view_a_class_they_do_not_belong_to(): void
    {
        $teacherId = $this->addUser('Teacher', 'One', 'teacher');
        $studentId = $this->addUser('Student', 'One', 'student');
        $classId = DB::table('classes')->insertGetId([
            'name' => 'Grade 8 - B',
            'section' => 'Grade 8 - B',
            'teacher_id' => $teacherId,
        ]);

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->getJson("/leaderboard?class_id={$classId}&subject=english")
            ->assertNotFound();
    }

    public function test_teacher_can_view_only_their_own_class_leaderboard(): void
    {
        $teacherId = $this->addUser('Teacher', 'One', 'teacher');
        $otherTeacherId = $this->addUser('Teacher', 'Two', 'teacher');
        $ownClassId = DB::table('classes')->insertGetId([
            'name' => 'Grade 8 - A',
            'section' => 'Grade 8 - A',
            'teacher_id' => $teacherId,
        ]);
        $otherClassId = DB::table('classes')->insertGetId([
            'name' => 'Grade 8 - B',
            'section' => 'Grade 8 - B',
            'teacher_id' => $otherTeacherId,
        ]);

        $this->withSession(['user_id' => $teacherId, 'user_role' => 'teacher'])
            ->getJson("/leaderboard?class_id={$ownClassId}&subject=science")
            ->assertOk()
            ->assertJsonPath('students', []);

        $this->getJson("/leaderboard?class_id={$otherClassId}&subject=science")
            ->assertNotFound();
    }

    private function addUser(string $firstName, string $lastName, string $role, int $wins = 0): int
    {
        return (int) DB::table('users')->insertGetId([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'multiplayer_wins' => $wins,
        ]);
    }
}
