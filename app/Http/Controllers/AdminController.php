<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Section;

class AdminController extends Controller
{
    public function index()
    {
        if (session('user_role') !== 'admin') {
            return redirect('/');
        }

        $students = User::where('role', 'student')->get();

        // Aggregate scores per student: total correct / total questions
        $scoreMap = DB::table('student_scores')
            ->select(
                'student_id',
                DB::raw('SUM(correct) as total_correct'),
                DB::raw('SUM(total) as total_questions'),
                DB::raw('ROUND(SUM(correct) / NULLIF(SUM(total), 0) * 100, 2) as overall_percent')
            )
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $teachers = User::where('role', 'teacher')->orderBy('last_name')->get();
        $sections = Section::orderBy('name')->get();

        return view('admin.dashboard', compact('students', 'scoreMap', 'teachers', 'sections'));
    }

    public function getStudentGrades($id)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $student = User::find($id);
        if (!$student || $student->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Student not found']);
        }

        $scores = DB::table('student_scores')->where('student_id', $id)->orderBy('subject')->get();
        $overallCorrect  = $scores->sum('correct');
        $overallTotal    = $scores->sum('total');
        $overallPercent  = $overallTotal > 0 ? round(($overallCorrect / $overallTotal) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'student' => [
                'name'    => $student->first_name . ' ' . $student->last_name,
                'lrn'     => $student->lrn,
                'section' => $student->section,
            ],
            'scores'  => $scores,
            'overall' => [
                'correct' => $overallCorrect,
                'total'   => $overallTotal,
                'percent' => $overallPercent,
            ],
        ]);
    }

    public function updateStudent(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'student_id' => 'required|integer',
            'lrn'        => 'nullable|string',
            'section'    => 'nullable|string',
        ]);

        $user = User::find($request->student_id);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Student not found']);
        }

        // Ensure LRN is unique if changed
        if ($request->lrn && $request->lrn !== $user->lrn) {
            $exists = User::where('lrn', $request->lrn)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'LRN already in use']);
            }
        }

        $user->lrn = $request->lrn;
        $user->section = $request->section;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Student updated successfully']);
    }

    public function registerTeacher(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return back()->with('error', 'Unauthorized');
        }

        $request->validate([
            'first_name'       => 'required|string',
            'last_name'        => 'required|string',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'age'        => 0, // Teachers might not have age inputted
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'teacher',
        ]);

        return back()->with('success', 'Teacher account created successfully!');
    }

    public function addSection(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'name' => 'required|string|unique:sections,name',
        ]);

        $section = Section::create([
            'name' => $request->name,
        ]);

        return response()->json(['success' => true, 'section' => $section]);
    }

    public function updateSection(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'section_id' => 'required|integer',
            'name'       => 'required|string|unique:sections,name,' . $request->section_id,
        ]);

        $section = Section::find($request->section_id);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found']);
        }

        $oldName = $section->name;
        $newName = $request->name;

        $section->name = $newName;
        $section->save();

        if ($oldName !== $newName) {
            User::where('role', 'student')->where('section', $oldName)->update(['section' => $newName]);
        }

        return response()->json(['success' => true]);
    }

    public function deleteSection(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'section_id' => 'required|integer',
        ]);

        $section = Section::find($request->section_id);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found']);
        }

        $sectionName = $section->name;
        $section->delete();

        User::where('role', 'student')->where('section', $sectionName)->update(['section' => null]);

        return response()->json(['success' => true]);
    }
}

