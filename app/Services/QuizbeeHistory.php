<?php

namespace App\Services;

use App\Mail\QuizbeeSessionSummary;
use App\Support\QuestionAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuizbeeHistory
{
    public function activeAttemptId(string $type): ?string
    {
        if (
            $type !== 'quizbee'
            || session('user_role') !== 'student'
            || !session('user_id')
            || session('quizbee_access_student_id') != session('user_id')
            || !session('quizbee_access_until')
            || now()->gte(\Illuminate\Support\Carbon::parse(session('quizbee_access_until')))
        ) {
            return null;
        }

        $attemptId = session('quizbee_attempt_id');
        if (!$attemptId) {
            return null;
        }

        $attempt = DB::table('quizbee_attempts')
            ->where('id', $attemptId)
            ->where('student_id', session('user_id'))
            ->whereNull('email_sent_at')
            ->first(['id']);

        return $attempt?->id;
    }

    public function recordAnsweredQuestions(string $attemptId, array $questionIds): void
    {
        DB::table('quizbee_attempt_questions')
            ->where('attempt_id', $attemptId)
            ->whereIn('question_id', $questionIds)
            ->whereNull('answered_at')
            ->update([
                'answered_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function recordServedQuestion(string $attemptId, object $question): void
    {
        $answerKey = QuestionAnswer::normalizeKey($question->answer, [
            'A' => $question->choice_a,
            'B' => $question->choice_b,
            'C' => $question->choice_c,
            'D' => $question->choice_d,
        ]);
        $answerText = match ($answerKey) {
            'A' => $question->choice_a,
            'B' => $question->choice_b,
            'C' => $question->choice_c,
            'D' => $question->choice_d,
            default => $answerKey,
        };

        DB::table('quizbee_attempt_questions')->insertOrIgnore([
            'attempt_id' => $attemptId,
            'question_id' => $question->id,
            'subject' => $question->subject,
            'question' => $question->question,
            'correct_answer_key' => $answerKey,
            'correct_answer_text' => $answerText,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function completeActiveAttempt(): void
    {
        if (session('user_role') !== 'student' || !session('user_id') || !session('quizbee_attempt_id')) {
            return;
        }

        $this->completeAttempt((string) session('quizbee_attempt_id'), (int) session('user_id'));
    }

    public function completeAttempt(string $attemptId, int $studentId): void
    {
        $attempt = DB::table('quizbee_attempts')
            ->where('id', $attemptId)
            ->where('student_id', $studentId)
            ->first();

        if (!$attempt || $attempt->email_sent_at) {
            return;
        }

        DB::table('quizbee_attempt_questions')
            ->where('attempt_id', $attemptId)
            ->whereNull('answered_at')
            ->update(['answered_at' => now(), 'updated_at' => now()]);

        $answers = DB::table('quizbee_attempt_questions')
            ->where('attempt_id', $attemptId)
            ->whereNotNull('answered_at')
            ->orderBy('id')
            ->get();

        if (!$attempt->completed_at) {
            DB::table('quizbee_attempts')
                ->where('id', $attemptId)
                ->whereNull('completed_at')
                ->update(['completed_at' => now(), 'updated_at' => now()]);
        }

        $student = DB::table('users')
            ->where('id', $studentId)
            ->where('role', 'student')
            ->first(['first_name', 'last_name', 'email']);

        if (!$student || $answers->isEmpty()) {
            return;
        }

        Mail::to($student->email)->send(new QuizbeeSessionSummary(
            studentName: trim($student->first_name . ' ' . $student->last_name),
            answers: $answers->all(),
        ));

        DB::table('quizbee_attempts')
            ->where('id', $attemptId)
            ->update(['email_sent_at' => now(), 'updated_at' => now()]);
    }
}
