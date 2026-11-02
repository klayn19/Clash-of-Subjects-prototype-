<?php

namespace App\Http\Controllers;

use App\Support\QuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralKnowledgeController extends Controller
{
    /**
     * Display the General Knowledge Arena.
     */
    public function index()
    {
        if (session('user_role') !== 'student') {
            return redirect('/');
        }

        return view('student.general_knowledge');
    }

    /**
     * Fetch 15 random general knowledge questions for the chosen subject and quarter.
     */
    public function getQuestions(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|in:math,science,english',
            'quarter' => 'required|integer|between:1,4',
        ]);

        $subject = strtolower(trim($request->subject));
        $quarter = (int) $request->quarter;

        // Retrieve 15 random general knowledge questions for the selected subject and quarter.
        $questions = DB::table('questions')
            ->whereNull('class_id')
            ->where('subject', $subject)
            ->where('type', 'quiz')
            ->where('quarter', $quarter)
            ->inRandomOrder()
            ->limit(15)
            ->get();

        // If not enough questions exist in the database, return error
        if ($questions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No questions found for this subject and quarter.',
            ], 404);
        }

        // Return questions format
        return response()->json([
            'success' => true,
            'subject' => $subject,
            'quarter' => $quarter,
            'questions' => $questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'A' => $q->choice_a,
                    'B' => $q->choice_b,
                    'C' => $q->choice_c,
                    'D' => $q->choice_d,
                    'answer' => QuestionAnswer::normalizeKey($q->answer, [
                        'A' => $q->choice_a,
                        'B' => $q->choice_b,
                        'C' => $q->choice_c,
                        'D' => $q->choice_d,
                    ]),
                    'explanation' => $q->explanation,
                ];
            }),
        ]);
    }

    /**
     * Unity game question endpoint.
     * Returns one random Quiz or Exam question across the three subjects,
     * excluding question IDs already answered in the current game.
     */
    public function generateAiQuestion(Request $request)
    {
        $answered = $request->input('answered', '');
        $difficulty = $request->input('difficulty', '');

        $answeredIds = array_values(array_filter(
            array_map('intval', explode(',', (string) $answered)),
            static fn (int $id): bool => $id > 0
        ));

        $baseQuery = DB::table('questions')
            ->whereIn(DB::raw('LOWER(subject)'), ['english', 'math', 'science'])
            ->whereIn('type', ['quiz', 'exam']);

        if (session('user_role') === 'student' && session('user_id')) {
            $studentId = (int) session('user_id');
            $studentSection = DB::table('users')
                ->where('id', $studentId)
                ->where('role', 'student')
                ->value('section');

            $classIds = $studentSection
                ? DB::table('classes')->where('section', $studentSection)->pluck('id')
                : DB::table('class_students')->where('student_id', $studentId)->pluck('class_id');

            $baseQuery->where(function ($query) use ($classIds) {
                $query->whereNull('class_id');
                if ($classIds->isNotEmpty()) {
                    $query->orWhereIn('class_id', $classIds);
                }
            });
        } else {
            $baseQuery->whereNull('class_id');
        }

        if (!empty($answeredIds)) {
            $baseQuery->whereNotIn('id', $answeredIds);
        }

        $diffMap = ['1' => 'easy', '2' => 'easy', '3' => 'average', '4' => 'difficult', '5' => 'difficult'];
        $diffLabel = $diffMap[$difficulty] ?? strtolower($difficulty);
        $validDifficulty = in_array($diffLabel, ['easy', 'average', 'difficult'], true)
            ? $diffLabel
            : null;
        $subjects = (clone $baseQuery)
            ->selectRaw('LOWER(subject) as subject')
            ->distinct()
            ->pluck('subject')
            ->all();
        shuffle($subjects);

        $question = null;
        foreach ($subjects as $subject) {
            $subjectQuery = (clone $baseQuery)->whereRaw('LOWER(subject) = ?', [$subject]);
            $difficultyQuery = clone $subjectQuery;
            if ($validDifficulty !== null) {
                $difficultyQuery->where('difficulty', $validDifficulty);
            }

            $question = $difficultyQuery->inRandomOrder()->first()
                ?? $subjectQuery->inRandomOrder()->first();
            if ($question) {
                break;
            }
        }

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'No questions available.',
            ], 404);
        }

        return response()->json([
            'success'     => true,
            'id'          => $question->id,
            'question'    => $question->question,
            'A'           => $question->choice_a,
            'B'           => $question->choice_b,
            'C'           => $question->choice_c,
            'D'           => $question->choice_d,
            'answer'      => QuestionAnswer::normalizeKey($question->answer, [
                'A' => $question->choice_a,
                'B' => $question->choice_b,
                'C' => $question->choice_c,
                'D' => $question->choice_d,
            ]),
            'explanation' => $question->explanation ?? '',
            'definition'  => $question->explanation ?? '',
            'subject'     => $question->subject,
            'difficulty'  => $question->difficulty ?? '',
        ]);
    }
}
