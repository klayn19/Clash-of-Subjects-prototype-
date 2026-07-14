<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        if (session('user_role') !== 'student') {
            return redirect('/');
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
