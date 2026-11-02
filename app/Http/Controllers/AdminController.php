<?php

namespace App\Http\Controllers;

use App\Mail\TeacherWelcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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
        $teacherSections = DB::table('classes')
            ->whereNotNull('section')
            ->orderBy('section')
            ->get(['teacher_id', 'section'])
            ->groupBy('teacher_id')
            ->map(fn ($assignments) => $assignments->pluck('section')->unique()->values());

        return view('admin.dashboard', compact('students', 'scoreMap', 'teachers', 'sections', 'teacherSections'));
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

        $scores = DB::table('student_scores')
            ->where('student_id', $id)
            ->orderBy('subject')
            ->get();
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
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:users,id',
            'lrn'        => ['nullable', 'string', 'regex:/^[0-9]{12}$/'],
            'is_verified' => 'sometimes|boolean',
            'section'    => 'nullable|string|max:100|exists:sections,name',
            'parent_email' => 'nullable|email|max:150',
        ], [
            'lrn.regex' => 'LRN must be exactly 12 numeric digits.',
            'is_verified.boolean' => 'LRN verification must be selected or cleared.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if ($request->boolean('is_verified') && !$request->filled('lrn')) {
            return response()->json([
                'success' => false,
                'message' => 'Enter the student LRN before verifying it.',
            ], 422);
        }

        $user = User::find($request->student_id);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $cleanedLrn = $request->filled('lrn') ? trim($request->lrn) : null;

        // Ensure LRN is unique if changed
        if ($cleanedLrn && $cleanedLrn !== $user->lrn) {
            $exists = User::where('lrn', $cleanedLrn)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'An account with this LRN already exists. Only one account per LRN is allowed.'
                ], 422);
            }
        }

        $user->lrn = $cleanedLrn;
        $user->section = $request->section ? trim($request->section) : null;
        $user->parent_email = $request->filled('parent_email') ? trim($request->parent_email) : null;

        $rosterEntry = $cleanedLrn
            ? DB::table('student_rosters')
                ->join('sections', 'student_rosters.section_id', '=', 'sections.id')
                ->where('student_rosters.lrn', $cleanedLrn)
                ->first(['sections.name as section'])
            : null;

        $user->is_verified = $rosterEntry !== null;
        if ($rosterEntry) {
            $user->section = $rosterEntry->section;
        } elseif ($cleanedLrn && $request->boolean('is_verified')) {
            $user->is_verified = true;
        }
        if (!$cleanedLrn) {
            $user->is_verified = false;
        }
        $user->save();

        Section::syncStudentRoster((int) $user->id);

        return response()->json([
            'success' => true,
            'verified' => (bool) $user->is_verified,
            'message' => $user->is_verified
                ? 'Student updated and LRN verified.'
                : 'Student updated. The LRN is not verified.',
        ]);
    }

    public function deleteStudent(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
        ]);

        $student = User::where('id', $validated['student_id'])
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        DB::transaction(function () use ($student): void {
            foreach (['class_students', 'student_scores', 'student_notes', 'quizbee_access_codes'] as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->where('student_id', $student->id)->delete();
                }
            }

            if (DB::getSchemaBuilder()->hasTable('resetpassword')) {
                DB::table('resetpassword')->where('email', $student->email)->delete();
            }

            $student->delete();
        });

        return response()->json(['success' => true, 'message' => 'Student account and related records were removed.']);
    }

    public function registerTeacher(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return back()->with('error', 'Unauthorized');
        }

        $request->validate([
            'first_name'       => ['required', 'string', 'max:100', 'regex:/^[^\d]+$/u'],
            'last_name'        => ['required', 'string', 'max:100', 'regex:/^[^\d]+$/u'],
            'email'            => 'required|email|unique:users,email',
            'subject'          => 'required|in:english,math,science',
            'section_ids'      => 'nullable|array',
            'section_ids.*'    => 'integer|distinct|exists:sections,id',
            'password'         => ['required', 'min:6', 'regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}$/'],
            'confirm_password' => 'required|same:password',
        ], [
            'first_name.regex' => 'First name cannot contain numbers.',
            'last_name.regex' => 'Last name cannot contain numbers.',
            'password.regex' => 'Password must include a number, an uppercase letter, and a special character.',
        ]);

        $teacher = DB::transaction(function () use ($request) {
            $teacher = User::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'age'        => 0,
                'email'      => $request->email,
                'subject'    => $request->subject,
                'password'   => Hash::make($request->password),
                'role'       => 'teacher',
            ]);

            foreach (Section::whereIn('id', $request->input('section_ids', []))->get() as $section) {
                $classId = DB::table('classes')->insertGetId([
                    'name' => $section->name,
                    'section' => $section->name,
                    'teacher_id' => $teacher->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Section::syncClassRoster($classId);
            }

            return $teacher;
        });

        Mail::to($teacher->email)->send(new TeacherWelcome(
            trim($teacher->first_name . ' ' . $teacher->last_name),
            ucfirst($teacher->subject),
        ));

        return back()->with('success', 'Teacher account created successfully!');
    }

    public function assignSectionToTeacher(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:users,id',
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'required|integer|distinct|exists:sections,id',
        ]);
        $teacher = User::where('id', $validated['teacher_id'])
            ->where('role', 'teacher')
            ->first();

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
        }

        $sections = Section::whereIn('id', $validated['section_ids'])->get();
        $classIds = DB::transaction(function () use ($teacher, $sections) {
            $classIds = [];
            foreach ($sections as $section) {
                $alreadyAssigned = DB::table('classes')
                    ->where('teacher_id', $teacher->id)
                    ->where('section', $section->name)
                    ->exists();
                if ($alreadyAssigned) {
                    continue;
                }

                $classId = DB::table('classes')->insertGetId([
                    'name' => $section->name,
                    'section' => $section->name,
                    'teacher_id' => $teacher->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Section::syncClassRoster($classId);
                $classIds[] = $classId;
            }

            return $classIds;
        });

        return response()->json([
            'success' => true,
            'message' => $classIds
                ? 'Selected sections assigned to teacher.'
                : 'All selected sections are already assigned to this teacher.',
            'class_ids' => $classIds,
        ]);
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
        $studentIds = User::where('role', 'student')->where('section', $oldName)->pluck('id');

        $section->name = $newName;
        $section->save();

        if ($oldName !== $newName) {
            User::where('role', 'student')->where('section', $oldName)->update(['section' => $newName]);
            DB::table('classes')
                ->where('section', $oldName)
                ->where('name', $oldName)
                ->update(['name' => $newName, 'section' => $newName, 'updated_at' => now()]);
            DB::table('classes')
                ->where('section', $oldName)
                ->update(['section' => $newName, 'updated_at' => now()]);

            foreach ($studentIds as $studentId) {
                Section::syncStudentRoster((int) $studentId);
            }
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
        $classIds = DB::table('classes')->where('section', $sectionName)->pluck('id');
        $section->delete();

        User::where('role', 'student')->where('section', $sectionName)->update(['section' => null]);
        DB::table('classes')->where('section', $sectionName)->update(['section' => null, 'updated_at' => now()]);
        if ($classIds->isNotEmpty()) {
            DB::table('class_students')->whereIn('class_id', $classIds)->delete();
        }

        return response()->json(['success' => true]);
    }
}
