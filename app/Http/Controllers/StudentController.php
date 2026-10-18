<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        if (session('user_role') !== 'student' || !session('user_id')) {
            return redirect('/');
        }

        $studentId = session('user_id');

        // Fetch grades (scores)
        $scores = DB::table('student_scores')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        $bestSubjectScores = [];
        foreach (['english', 'math', 'science'] as $subject) {
            $bestSubjectScores[$subject] = $scores
                ->where('subject', $subject)
                ->sortByDesc('percent')
                ->first();
        }

        // Fetch notes from teachers
        $notes = DB::table('student_notes')
            ->join('users', 'student_notes.teacher_id', '=', 'users.id')
            ->where('student_notes.student_id', $studentId)
            ->select('student_notes.*', 'users.first_name as teacher_first', 'users.last_name as teacher_last')
            ->orderBy('student_notes.created_at', 'desc')
            ->get();
        $unreadNotes = $notes->whereNull('read_at')->count();

        return view('student.dashboard', compact('scores', 'notes', 'unreadNotes', 'bestSubjectScores'));
    }

    public function markNoteRead(int $noteId)
    {
        if (session('user_role') !== 'student' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a student.'], 403);
        }

        $note = DB::table('student_notes')
            ->where('id', $noteId)
            ->where('student_id', session('user_id'))
            ->first(['id', 'read_at']);

        if (!$note) {
            return response()->json(['success' => false, 'message' => 'Message not found.'], 404);
        }

        if (!$note->read_at) {
            DB::table('student_notes')
                ->where('id', $note->id)
                ->where('student_id', session('user_id'))
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'updated_at' => now()]);
        }

        $unreadCount = DB::table('student_notes')
            ->where('student_id', session('user_id'))
            ->whereNull('read_at')
            ->count();

        return response()->json(['success' => true, 'unread_count' => $unreadCount]);
    }

    public function markAllNotesRead()
    {
        if (session('user_role') !== 'student' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a student.'], 403);
        }

        DB::table('student_notes')
            ->where('student_id', session('user_id'))
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return response()->json(['success' => true, 'unread_count' => 0]);
    }

    public function redeemQuizBeeCode(Request $request)
    {
        if (session('user_role') !== 'student' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a student to use QuizBee.'], 403);
        }

        $validated = $request->validate([
            'code' => 'required|string|regex:/^\d{6}$/',
        ]);

        $appKey = (string) config('app.key');
        if ($appKey === '') {
            \Log::error('QuizBee code validation is unavailable because the application key is not configured.');
            return response()->json(['success' => false, 'message' => 'QuizBee access is temporarily unavailable. Please contact your teacher.'], 500);
        }

        $codeHash = hash_hmac('sha256', $validated['code'], $appKey);
        $now = now();

        $accessCode = DB::transaction(function () use ($codeHash, $now) {
            $accessCode = DB::table('quizbee_access_codes')
                ->where('code_hash', $codeHash)
                ->where('student_id', session('user_id'))
                ->whereNull('redeemed_at')
                ->where('expires_at', '>', $now)
                ->lockForUpdate()
                ->first();

            if (!$accessCode) {
                return null;
            }

            $updated = DB::table('quizbee_access_codes')
                ->where('id', $accessCode->id)
                ->whereNull('redeemed_at')
                ->where('expires_at', '>', $now)
                ->update(['redeemed_at' => $now, 'updated_at' => $now]);

            return $updated === 1 ? $accessCode : null;
        });

        if (!$accessCode) {
            return response()->json(['success' => false, 'message' => 'That code is invalid, expired, already used, or was issued to another student.'], 422);
        }

        $student = DB::table('users')
            ->where('id', session('user_id'))
            ->where('role', 'student')
            ->first(['id', 'first_name', 'last_name', 'email']);

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Your student account could not be verified. Please log in again.'], 403);
        }

        session([
            'quizbee_access_until' => $accessCode->expires_at,
            'quizbee_access_student_id' => $student->id,
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('gameplay', [
                'mode' => 'quizbee',
                'uid' => $student->id,
                'email' => $student->email,
                'fn' => $student->first_name,
                'ln' => $student->last_name,
            ]),
        ]);
    }
}
