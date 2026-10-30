<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $role = session('user_role');
        $userId = session('user_id');

        if (!$userId || !in_array($role, ['student', 'teacher'], true)) {
            return response()->json(['success' => false, 'message' => 'Please log in to view the leaderboard.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'subject' => 'required|in:english,math,science',
        ]);

        $classQuery = DB::table('classes')->where('classes.id', $validated['class_id']);
        if ($role === 'student') {
            $classQuery->join('class_students', 'classes.id', '=', 'class_students.class_id')
                ->where('class_students.student_id', $userId);
        } else {
            $classQuery->where('teacher_id', $userId);
        }

        $class = $classQuery->first(['classes.id']);
        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found or not accessible.'], 404);
        }

        $subjectScores = DB::table('student_scores')
            ->select('student_id', DB::raw('AVG(percent) as average_percent'))
            ->where('class_id', $class->id)
            ->where('subject', $validated['subject'])
            ->groupBy('student_id');

        $students = DB::table('class_students')
            ->join('users', 'class_students.student_id', '=', 'users.id')
            ->leftJoinSub($subjectScores, 'subject_scores', function ($join) {
                $join->on('users.id', '=', 'subject_scores.student_id');
            })
            ->where('class_students.class_id', $class->id)
            ->where('users.role', 'student')
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.multiplayer_wins',
                'subject_scores.average_percent'
            )
            ->orderByRaw('CASE WHEN subject_scores.average_percent IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('subject_scores.average_percent')
            ->orderByDesc('users.multiplayer_wins')
            ->orderBy('users.last_name')
            ->orderBy('users.first_name')
            ->get()
            ->values()
            ->map(function ($student, $index) {
                return [
                    'rank' => $index + 1,
                    'student_id' => (int) $student->id,
                    'name' => trim($student->first_name . ' ' . $student->last_name),
                    'average_percent' => $student->average_percent === null
                        ? null
                        : round((float) $student->average_percent, 2),
                    'multiplayer_wins' => (int) ($student->multiplayer_wins ?? 0),
                ];
            });

        return response()->json([
            'success' => true,
            'subject' => $validated['subject'],
            'students' => $students,
        ]);
    }
}
