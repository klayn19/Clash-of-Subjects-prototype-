<?php

namespace App\Http\Controllers;

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
     * Fetch 15 random general knowledge questions for the chosen subject.
     */
    public function getQuestions(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|in:math,science,english',
        ]);

        $subject = strtolower(trim($request->subject));

        // Retrieve 15 random general knowledge questions for the subject
        $questions = DB::table('questions')
            ->whereNull('class_id')
            ->where('subject', $subject)
            ->where('type', 'quiz')
            ->inRandomOrder()
            ->limit(15)
            ->get();

        // If not enough questions exist in the database, return error
        if ($questions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No questions found for this subject.',
            ], 404);
        }

        // Return questions format
        return response()->json([
            'success' => true,
            'subject' => $subject,
            'questions' => $questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'A' => $q->choice_a,
                    'B' => $q->choice_b,
                    'C' => $q->choice_c,
                    'D' => $q->choice_d,
                    'answer' => $q->answer,
                    'explanation' => $q->explanation,
                ];
            }),
        ]);
    }

    /**
     * Unity GK Arena AI endpoint — called by the GK Arena scene in Unity.
     * GET /backend/generate_ai_question.php?grade_level=Grade+8&difficulty=5&subject=math&answered=1,2,3
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

        // Parse already-answered IDs so we don't repeat them.
        $answeredIds = array_filter(array_map('intval', explode(',', $answered)));

        $query = DB::table('questions')->whereNull('class_id');

        // Filter by subject when provided and valid.
        if (in_array($subject, ['math', 'science', 'english'])) {
            $query->where('subject', $subject);
        }

        // Exclude already-answered questions.
        if (!empty($answeredIds)) {
            $query->whereNotIn('id', $answeredIds);
        }

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
            // Fallback: try without difficulty filter before giving up.
            $fallback = DB::table('questions')
                ->whereNull('class_id')
                ->when(!empty($answeredIds), fn($q) => $q->whereNotIn('id', $answeredIds))
                ->inRandomOrder()
                ->first();

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
            'answer'      => $question->answer,
            'explanation' => $question->explanation ?? '',
            'subject'     => $question->subject,
            'difficulty'  => $question->difficulty ?? '',
        ]);
    }
}
