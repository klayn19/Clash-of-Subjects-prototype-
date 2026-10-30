<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuizBeeAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(str_repeat('k', 32))]);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->timestamps();
        });

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('student_id');
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('quizbee_access_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('student_id');
            $table->char('code_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quizbee_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('student_id');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('quizbee_access_codes');
        Schema::dropIfExists('quizbee_attempts');
        Schema::dropIfExists('student_notes');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_teacher_issues_code_and_student_can_redeem_it_once(): void
    {
        $teacherId = $this->createUser('teacher');
        $studentId = $this->createUser('student');

        $issueResponse = $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/issue_quizbee_code.php', ['student_id' => $studentId]);

        $issueResponse->assertOk()->assertJsonPath('success', true);
        $code = $issueResponse->json('code');
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $note = DB::table('student_notes')->where('student_id', $studentId)->first();
        $this->assertNotNull($note);
        $this->assertStringContainsString($code, $note->note);

        $redeemResponse = $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
        ])->postJson('/backend/redeem_quizbee_code.php', ['code' => $code])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertStringContainsString('mode=quizbee', $redeemResponse->json('redirect_url'));

        $accessCode = DB::table('quizbee_access_codes')->first();
        $this->assertNotNull($accessCode->redeemed_at);
        $this->assertDatabaseHas('quizbee_attempts', [
            'student_id' => $studentId,
        ]);

        $quizbeeGameplay = $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
            'quizbee_access_until' => $accessCode->expires_at,
            'quizbee_access_student_id' => $studentId,
        ])->get('/gameplay?mode=quizbee&uid=' . $studentId);
        $quizbeeGameplay->assertOk()
            ->assertSee('unitygameQuizbee\\/Build\\/unitygameQuizbee.loader.js', false);
        $this->assertUnityAssetsShareVersion($quizbeeGameplay->getContent());

        $regularGameplay = $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
        ])->get('/gameplay');
        $regularGameplay->assertOk()
            ->assertSee('unitygame\\/Build\\/unityFinal.loader.js', false);
        $this->assertUnityAssetsShareVersion($regularGameplay->getContent());

        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
        ])->postJson('/backend/redeem_quizbee_code.php', ['code' => $code])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_code_cannot_be_redeemed_by_another_student(): void
    {
        $teacherId = $this->createUser('teacher');
        $intendedStudentId = $this->createUser('student');
        $otherStudentId = $this->createUser('student');

        $code = $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/issue_quizbee_code.php', ['student_id' => $intendedStudentId])
            ->assertOk()
            ->json('code');

        $this->withSession([
            'user_id' => $otherStudentId,
            'user_role' => 'student',
        ])->postJson('/backend/redeem_quizbee_code.php', ['code' => $code])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertNull(DB::table('quizbee_access_codes')->value('redeemed_at'));
    }

    public function test_expired_code_cannot_be_redeemed(): void
    {
        $teacherId = $this->createUser('teacher');
        $studentId = $this->createUser('student');

        $code = $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/issue_quizbee_code.php', ['student_id' => $studentId])
            ->assertOk()
            ->json('code');

        DB::table('quizbee_access_codes')->update(['expires_at' => now()->subMinute()]);

        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
        ])->postJson('/backend/redeem_quizbee_code.php', ['code' => $code])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    private function createUser(string $role): int
    {
        return DB::table('users')->insertGetId([
            'first_name' => ucfirst($role),
            'last_name' => 'Tester',
            'email' => $role . '-' . uniqid() . '@example.test',
            'password' => 'unused',
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assertUnityAssetsShareVersion(string $html): void
    {
        preg_match_all('/data-(?:loader|data|framework|code)-url="[^"]*\?v=([^"]+)"/', $html, $matches);

        $this->assertCount(4, $matches[1]);
        $this->assertCount(1, array_unique($matches[1]));
    }
}
