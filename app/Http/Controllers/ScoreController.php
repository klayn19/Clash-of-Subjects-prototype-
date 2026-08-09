<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
{
    /**
     * Called by Unity after a quiz/exam session.
     * POST /api/save_score
     * Body: { student_id, class_id, subject, type, quarter, sequence_number, correct, total }
     */
    public function store(Request $request)
    {
        $studentId = (int) ($request->input('student_id') ?: session('user_id'));
        if (!$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated student session'
            ], 401);
        }

        // Auto-resolve class_id if missing
        $classId = $request->input('class_id');
        if (empty($classId)) {
            $enrolledClass = DB::table('class_students')
                ->where('student_id', $studentId)
                ->first();
            if ($enrolledClass) {
                $classId = $enrolledClass->class_id;
            }
        }

        $subject = strtolower(trim($request->input('subject', 'english')));
        $type    = strtolower(trim($request->input('type', 'quiz')));
        if (!in_array($type, ['quiz', 'exam', 'assessment', 'prototype'])) {
            $type = 'quiz';
        }

        $quarter        = max(1, min(4, (int) $request->input('quarter', 1)));
        $sequenceNumber = max(1, (int) $request->input('sequence_number', 1));
        $correct        = max(0, (int) $request->input('correct', 0));
        $total          = max(1, (int) $request->input('total', 1));
        $mistakes       = max(0, $total - $correct);
        $percent        = round(($correct / $total) * 100, 2);

        // Fetch student's previous highest score for this subject/type
        $prevHighest = DB::table('student_scores')
            ->where('student_id', $studentId)
            ->where('subject', $subject)
            ->where('type', $type)
            ->select(
                DB::raw('MAX(correct) as max_correct'),
                DB::raw('MAX(percent) as max_percent')
            )
            ->first();

        $prevMaxCorrect = $prevHighest ? (int) $prevHighest->max_correct : 0;
        $prevMaxPercent = $prevHighest ? (float) $prevHighest->max_percent : 0;

        $isNewHighScore = ($prevHighest && $prevHighest->max_correct !== null)
            ? ($correct > $prevMaxCorrect || ($correct === $prevMaxCorrect && $percent > $prevMaxPercent))
            : true;

        DB::table('student_scores')->insert([
            'student_id'      => $studentId,
            'class_id'        => $classId ?: null,
            'subject'         => $subject,
            'type'            => $type,
            'quarter'         => $quarter,
            'sequence_number' => $sequenceNumber,
            'correct'         => $correct,
            'total'           => $total,
            'percent'         => $percent,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $highestCorrect = max($prevMaxCorrect, $correct);
        $highestPercent = max($prevMaxPercent, $percent);

        return response()->json([
            'success'           => true,
            'message'           => 'Score saved successfully',
            'correct'           => $correct,
            'total'             => $total,
            'mistakes'          => $mistakes,
            'percent'           => $percent,
            'highest_correct'   => $highestCorrect,
            'highest_percent'   => $highestPercent,
            'is_new_high_score' => $isNewHighScore,
        ]);
    }

    /**
     * Get highest score for a student
     * GET /api/student_high_score?student_id=X&subject=math
     */
    public function getHighScore(Request $request)
    {
        $studentId = $request->query('student_id', session('user_id'));
        if (!$studentId) {
            return response()->json(['success' => false, 'message' => 'Student ID required'], 400);
        }

        $query = DB::table('student_scores')->where('student_id', $studentId);

        if ($request->filled('subject')) {
            $query->where('subject', strtolower(trim($request->subject)));
        }

        $highest = $query->select(
            DB::raw('MAX(correct) as max_correct'),
            DB::raw('MAX(percent) as max_percent'),
            DB::raw('COUNT(*) as total_attempts')
        )->first();

        return response()->json([
            'success'         => true,
            'highest_correct' => $highest ? (int) $highest->max_correct : 0,
            'highest_percent' => $highest ? (float) $highest->max_percent : 0,
            'total_attempts'  => $highest ? (int) $highest->total_attempts : 0,
        ]);
    }
}
