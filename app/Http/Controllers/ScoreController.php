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
        $request->validate([
            'student_id'      => 'required|integer|exists:users,id',
            'class_id'        => 'nullable|integer',
            'subject'         => 'required|string|max:100',
            'type'            => 'required|in:quiz,exam,assessment,prototype',
            'quarter'         => 'required|integer|between:1,4',
            'sequence_number' => 'required|integer|min:1',
            'correct'         => 'required|integer|min:0',
            'total'           => 'required|integer|min:1',
        ]);

        $studentId = (int) $request->student_id;
        $subject   = strtolower(trim($request->subject));
        $correct   = (int) $request->correct;
        $total     = (int) $request->total;
        $mistakes  = max(0, $total - $correct);
        $percent   = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        // Fetch student's previous highest score for this subject/type (or overall)
        $prevHighest = DB::table('student_scores')
            ->where('student_id', $studentId)
            ->where('subject', $subject)
            ->where('type', $request->type)
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
            'class_id'        => $request->class_id ?: null,
            'subject'         => $subject,
            'type'            => $request->type,
            'quarter'         => $request->quarter,
            'sequence_number' => $request->sequence_number,
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
