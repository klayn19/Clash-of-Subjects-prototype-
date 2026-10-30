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
     * Unity GK Arena AI endpoint — called by the GK Arena scene in Unity.
     * GET /backend/generate_ai_question.php?grade_level=Grade+8&difficulty=5&subject=math&quarter=2&answered=1,2,3
     *
     * Returns ONE random question from the questions table that the student
     * has not yet answered in this session.
     */
    public function generateAiQuestion(Request $request)
    {
        // Accept optional filters; all are optional so Unity can call freely.
        $subject    = strtolower(trim($request->input('subject', '')));
        $grade      = $request->input('grade_level', '');   // e.g. "Grade 8"
        $answered   = $request->input('answered', '');      // comma-separated IDs already answered
        $difficulty = $request->input('difficulty', '');    // numeric 1-5 or Easy/Average/Difficult
        $request->validate([
            'quarter' => 'sometimes|nullable|integer|between:1,4',
        ]);
        $quarter = $request->filled('quarter') ? (int) $request->input('quarter') : null;

        // Parse already-answered IDs so we don't repeat them.
        $answeredIds = array_filter(array_map('intval', explode(',', $answered)));

        $baseQuery = DB::table('questions')->whereNull('class_id');

        // Filter by subject when provided and valid.
        if (in_array($subject, ['math', 'science', 'english'])) {
            $baseQuery->where('subject', $subject);
        }

        if ($quarter !== null) {
            $baseQuery->where('quarter', $quarter);
        }

        // Exclude already-answered questions.
        if (!empty($answeredIds)) {
            $baseQuery->whereNotIn('id', $answeredIds);
        }

        $query = clone $baseQuery;

        // Map numeric difficulty (1-5) to label if needed.
        if ($difficulty !== '') {
            $diffMap = ['1' => 'easy', '2' => 'easy', '3' => 'average', '4' => 'difficult', '5' => 'difficult'];
            $diffLabel = $diffMap[$difficulty] ?? strtolower($difficulty);
            if (in_array($diffLabel, ['easy', 'average', 'difficult'])) {
                $query->where('difficulty', $diffLabel);
            }
        }

        $question = $query->inRandomOrder()->first();

        if (!$question) {
            // Retry without difficulty, but keep the subject and quarter filters.
            $fallback = $baseQuery->inRandomOrder()->first();

            if (!$fallback) {
                return response()->json([
                    'success' => false,
                    'message' => 'No questions available.',
                ], 404);
            }

            $question = $fallback;
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
