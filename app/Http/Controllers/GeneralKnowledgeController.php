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
}
