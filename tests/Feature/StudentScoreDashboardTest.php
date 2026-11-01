<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Mail\QuizbeeSessionSummary;
use Tests\TestCase;

class StudentScoreDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('role');
            $table->string('section')->nullable();
            $table->unsignedInteger('multiplayer_wins')->default(0);
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
            $table->integer('correct');
            $table->integer('total');
            $table->decimal('percent', 5, 2);
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
            $table->text('explanation')->nullable();
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

        Schema::create('quizbee_attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->string('subject', 100);
            $table->text('question');
            $table->string('correct_answer_key', 20);
            $table->text('correct_answer_text');
            $table->string('student_answer_key', 20)->nullable();
            $table->text('student_answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('teacher_id');
            $table->text('note');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('student_notes');
        Schema::dropIfExists('quizbee_attempt_questions');
        Schema::dropIfExists('quizbee_attempts');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('student_scores');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_gameplay_quiz_and_exam_scores_are_saved_to_the_logged_in_students_dashboard(): void
    {
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Student',
            'last_name' => 'One',
            'email' => 'student@example.test',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $anotherStudentId = DB::table('users')->insertGetId([
            'first_name' => 'Student',
            'last_name' => 'Two',
            'email' => 'other@example.test',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
            'user_name' => 'Student One',
        ])->postJson('/api/save_score', [
            'subject' => 'math',
            'type' => 'quiz',
            'correct' => 8,
            'total' => 10,
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/save_score', [
            'subject' => 'math',
            'type' => 'quizbee',
            'quarter' => 4,
            'sequence_number' => 1,
            'correct' => 6,
            'total' => 10,
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/save_score', [
            'student_id' => $anotherStudentId,
            'subject' => 'science',
            'type' => 'exam',
            'quarter' => 2,
            'sequence_number' => 1,
            'correct' => 9,
            'total' => 10,
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/save_score', [
            'subject' => 'math',
            'type' => 'assessment',
            'quarter' => 3,
            'sequence_number' => 1,
            'correct' => 7,
            'total' => 10,
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('student_scores', [
            'student_id' => $studentId,
            'subject' => 'math',
            'type' => 'quiz',
            'quarter' => 1,
            'sequence_number' => 1,
            'percent' => 80,
        ]);
        $this->assertDatabaseHas('student_scores', [
            'student_id' => $studentId,
            'subject' => 'science',
            'type' => 'exam',
            'percent' => 90,
        ]);
        $this->assertDatabaseMissing('student_scores', [
            'student_id' => $studentId,
            'type' => 'quizbee',
        ]);
        $this->assertDatabaseMissing('student_scores', [
            'student_id' => $anotherStudentId,
        ]);
        DB::table('student_scores')->insert([
            'student_id' => $studentId,
            'subject' => 'math',
            'type' => 'quizbee',
            'quarter' => 3,
            'sequence_number' => 1,
            'correct' => 7,
            'total' => 10,
            'percent' => 70,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/student/dashboard')
            ->assertOk()
            ->assertSee('/gameplay?uid=' . $studentId, false)
            ->assertSee('MATH')
            ->assertSee('QUIZ')
            ->assertSee('8/10')
            ->assertSee('SCIENCE')
            ->assertSee('EXAM')
            ->assertSee('9/10')
            ->assertDontSee('7/10')
            ->assertDontSee('70%');
    }

    public function test_high_score_endpoint_accepts_student_id_for_stateless_gameplay_requests(): void
    {
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'High',
            'last_name' => 'Scorer',
            'email' => 'high.scorer@example.test',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_scores')->insert([
            'student_id' => $studentId,
            'subject' => 'english',
            'type' => 'quiz',
            'quarter' => 1,
            'sequence_number' => 1,
            'correct' => 8,
            'total' => 10,
            'percent' => 80,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/student_high_score?student_id=' . $studentId . '&subject=english')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('highest_correct', 8)
            ->assertJsonPath('highest_percent', 80)
            ->assertJsonPath('total_attempts', 1);
    }

    public function test_quizbee_answers_are_saved_to_history_and_emailed_without_becoming_a_grade(): void
    {
        Mail::fake();

        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Bee',
            'last_name' => 'Student',
            'email' => 'bee@example.test',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $questionId = DB::table('questions')->insertGetId([
            'subject' => 'math',
            'type' => 'quizbee',
            'quarter' => 1,
            'sequence_number' => 1,
            'question' => 'What is 2 + 2?',
            'choice_a' => '3',
            'choice_b' => '4',
            'choice_c' => '5',
            'choice_d' => '6',
            'answer' => 'B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $attemptId = (string) \Illuminate\Support\Str::uuid();
        DB::table('quizbee_attempts')->insert([
            'id' => $attemptId,
            'student_id' => $studentId,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
            'quizbee_access_student_id' => $studentId,
            'quizbee_access_until' => now()->addMinutes(30),
            'quizbee_attempt_id' => $attemptId,
        ]);

        $this->getJson('/api/get_question?subject=math&type=assessment&mode=quizbee&quarter=1&sequence_number=1')
            ->assertOk()
            ->assertJsonPath('id', $questionId);

        $this->postJson('/api/quizbee/record-answer', [
            'question_id' => $questionId,
            'selected_key' => 'A',
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/save_score', [
            'subject' => 'math',
            'type' => 'assessment',
            'correct' => 1,
            'total' => 1,
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('quizbee_attempt_questions', [
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'correct_answer_key' => 'B',
            'correct_answer_text' => '4',
            'student_answer_key' => 'A',
            'student_answer_text' => '3',
            'is_correct' => false,
        ]);
        $this->assertNotNull(DB::table('quizbee_attempt_questions')
            ->where('attempt_id', $attemptId)
            ->where('question_id', $questionId)
            ->value('answered_at'));
        $this->assertDatabaseHas('quizbee_attempts', [
            'id' => $attemptId,
        ]);
        $this->assertNotNull(DB::table('quizbee_attempts')->where('id', $attemptId)->value('email_sent_at'));
        $this->assertDatabaseMissing('student_scores', [
            'student_id' => $studentId,
            'type' => 'quizbee',
        ]);
        Mail::assertSent(QuizbeeSessionSummary::class, function (QuizbeeSessionSummary $mail): bool {
            return $mail->studentName === 'Bee Student'
                && count($mail->answers) === 1
                && $mail->answers[0]->question === 'What is 2 + 2?'
                && $mail->answers[0]->student_answer_text === '3'
                && !(bool) $mail->answers[0]->is_correct
                && $mail->answers[0]->correct_answer_text === '4';
        });

        $this->get('/student/dashboard')
            ->assertOk()
            ->assertSee('QUIZBEE HISTORY')
            ->assertSee('What is 2 + 2?')
            ->assertSee('Your answer (incorrect) (A):')
            ->assertSee('3')
            ->assertSee('Correct answer (B):')
            ->assertSee('4');
    }

    public function test_student_dashboard_remains_available_before_quizbee_history_migration(): void
    {
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Pending',
            'last_name' => 'Migration',
            'email' => 'pending@example.test',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::dropIfExists('quizbee_attempt_questions');
        Schema::dropIfExists('quizbee_attempts');

        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
        ])->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Quizbee history is temporarily unavailable');
    }

    public function test_multiplayer_wins_increment_for_the_logged_in_student_and_return_badge_tier(): void
    {
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Winner',
            'last_name' => 'Student',
            'email' => 'winner@example.test',
            'role' => 'student',
            'multiplayer_wins' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
        ])->postJson('/api/multiplayer/victory')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('wins', 10)
            ->assertJsonPath('tier', 'bronze');

        $this->assertDatabaseHas('users', [
            'id' => $studentId,
            'multiplayer_wins' => 10,
        ]);

        for ($wins = 11; $wins <= 15; $wins++) {
            $this->postJson('/api/multiplayer/victory')
                ->assertOk()
                ->assertJsonPath('wins', $wins)
                ->assertJsonPath('tier', $wins >= 15 ? 'silver' : 'bronze');
        }

        for ($wins = 16; $wins <= 20; $wins++) {
            $this->postJson('/api/multiplayer/victory')
                ->assertOk()
                ->assertJsonPath('wins', $wins)
                ->assertJsonPath('tier', $wins >= 20 ? 'gold' : 'silver');
        }
    }

    public function test_multiplayer_victory_requires_a_student_session(): void
    {
        $this->postJson('/api/multiplayer/victory')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_dashboard_displays_the_multiplayer_trophy_tier_from_saved_wins(): void
    {
        $studentId = DB::table('users')->insertGetId([
            'first_name' => 'Badge',
            'last_name' => 'Student',
            'email' => 'badge@example.test',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $studentId,
            'user_role' => 'student',
            'user_name' => 'Badge',
        ]);

        foreach ([0 => 'unranked', 10 => 'bronze', 15 => 'silver', 20 => 'gold'] as $wins => $tier) {
            DB::table('users')->where('id', $studentId)->update(['multiplayer_wins' => $wins]);
            $this->get('/student/dashboard')
                ->assertOk()
                ->assertSee('reward-card tier-' . $tier, false);
        }
    }
}
