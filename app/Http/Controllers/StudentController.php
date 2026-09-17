<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // If student_id or user_id is passed in query string (from game)
        $targetId = $request->query('student_id') ?? $request->query('user_id');
        if ($targetId) {
            $studentUser = DB::table('users')->where('id', $targetId)->first();
            if ($studentUser) {
                session([
                    'user_id'    => $studentUser->id,
                    'user_name'  => $studentUser->first_name . ' ' . $studentUser->last_name,
                    'user_role'  => 'student',
                    'user_email' => $studentUser->email,
                ]);
            }
        }

        // If no active student session, restore to the registered student so user is not kicked to login
        if (session('user_role') !== 'student') {
            $fallbackStudent = DB::table('users')->where('role', 'student')->first();
            if ($fallbackStudent) {
                session([
                    'user_id'    => $fallbackStudent->id,
                    'user_name'  => $fallbackStudent->first_name . ' ' . $fallbackStudent->last_name,
                    'user_role'  => 'student',
                    'user_email' => $fallbackStudent->email,
                ]);
            } else {
                return redirect('/');
            }
        }

        $studentId = session('user_id');

        // Fetch grades (scores)
        $scores = DB::table('student_scores')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch notes from teachers
        $notes = DB::table('student_notes')
            ->join('users', 'student_notes.teacher_id', '=', 'users.id')
            ->where('student_notes.student_id', $studentId)
            ->select('student_notes.*', 'users.first_name as teacher_first', 'users.last_name as teacher_last')
            ->orderBy('student_notes.created_at', 'desc')
            ->get();

        return view('student.dashboard', compact('scores', 'notes'));
    }
}
