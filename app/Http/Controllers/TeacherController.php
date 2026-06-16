<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowGradeAlert;

class TeacherController extends Controller
{
    // ─── TEACHER DASHBOARD ────────────────────────────────────────
    public function index()
    {
        if (session('user_role') !== 'teacher') {
            return redirect('/');
        }

        $teacherId = session('user_id');

        $classes = DB::table('classes')
            ->leftJoin('class_students', 'classes.id', '=', 'class_students.class_id')
            ->select(
                'classes.id',
                'classes.name',
                'classes.section',
                DB::raw('COUNT(class_students.student_id) as student_count')
            )
            ->where('classes.teacher_id', $teacherId)
            ->groupBy('classes.id', 'classes.name', 'classes.section')
            ->orderBy('classes.id')
            ->get();

        return view('teacher.dashboard', compact('classes'));
    }

    // ─── SAVE / UPDATE CLASS ──────────────────────────────────────
    public function saveClass(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'name'    => 'required|string|max:255',
            'section' => 'nullable|string|max:255',
        ]);

        $teacherId = session('user_id');

        if ($request->filled('id')) {
            // Update existing class (only if it belongs to this teacher)
            $updated = DB::table('classes')
                ->where('id', $request->id)
                ->where('teacher_id', $teacherId)
                ->update([
                    'name'       => $request->name,
                    'section'    => $request->section,
                    'updated_at' => now(),
                ]);

            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Class not found or not yours']);
            }

            return response()->json(['success' => true, 'message' => 'Class updated successfully']);
        }

        // Create new class
        $classId = DB::table('classes')->insertGetId([
            'name'       => $request->name,
            'section'    => $request->section,
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Class created successfully', 'class_id' => $classId]);
    }

    // ─── DELETE CLASS ─────────────────────────────────────────────
    public function deleteClass(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 403);
        }

        $request->validate([
            'class_id' => 'required|integer',
        ]);

        $teacherId = session('user_id');

        $class = DB::table('classes')->where('id', $request->class_id)->where('teacher_id', $teacherId)->first();
        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found or unauthorized.'], 404);
        }

        DB::table('questions')->where('class_id', $request->class_id)->delete();
        DB::table('student_scores')->where('class_id', $request->class_id)->delete();
        DB::table('classes')->where('id', $request->class_id)->delete();

        return response()->json(['success' => true]);
    }

    // ─── SAVE QUESTIONS ───────────────────────────────────────────
    // Accepts an array of questions to insert/replace for a class+subject+type+quarter.
    // Frontend should POST:
    //   { class_id, subject, type, quarter, questions: [{question, choice_a, …, answer}, …] }
    public function saveQuestions(Request $request)
    {
        \Log::info('saveQuestions hit. Session role: ' . session('user_role'));
        
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'class_id'             => 'nullable|integer',
            'subject'              => 'nullable|string|max:100',
            'type'                 => 'required|in:quiz,exam,assessment,prototype',
            'quarter'              => 'nullable|integer|between:1,4',
            'sequence_number'      => 'nullable|integer|min:1',
            'questions'            => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.choice_a' => 'required|string',
            'questions.*.choice_b' => 'required|string',
            'questions.*.choice_c' => 'required|string',
            'questions.*.choice_d' => 'required|string',
            'questions.*.answer'   => 'required|in:A,B,C,D',
        ]);

        $teacherId = session('user_id');

        if ($request->type !== 'prototype') {
            // Verify the class belongs to this teacher
            $class = DB::table('classes')
                ->where('id', $request->class_id)
                ->where('teacher_id', $teacherId)
                ->first();

            if (!$class) {
                return response()->json(['success' => false, 'message' => 'Class not found or not yours']);
            }

            // Delete old questions for this class + subject + type + quarter + sequence_number (full replace)
            DB::table('questions')
                ->where('class_id', $request->class_id)
                ->where('subject',  $request->subject)
                ->where('type',     $request->type)
                ->where('quarter',  $request->quarter)
                ->where('sequence_number', $request->sequence_number)
                ->delete();
        }

        // Bulk insert new questions
        $rows = [];
        foreach ($request->questions as $q) {
            $rows[] = [
                'class_id'        => $request->type === 'prototype' ? null : $request->class_id,
                'subject'         => $request->type === 'prototype' ? 'prototype' : strtolower(trim($request->subject)),
                'type'            => $request->type,
                'quarter'         => $request->type === 'prototype' ? 1 : $request->quarter,
                'sequence_number' => $request->type === 'prototype' ? 1 : $request->sequence_number,
                'question'        => $q['question'],
                'choice_a'        => $q['choice_a'],
                'choice_b'        => $q['choice_b'],
                'choice_c'        => $q['choice_c'],
                'choice_d'        => $q['choice_d'],
                'answer'          => strtoupper($q['answer']),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        DB::table('questions')->insert($rows);

        return response()->json([
            'success' => true,
            'message' => count($rows) . ' question(s) saved successfully',
        ]);
    }

    // ─── GET QUESTION FOR UNITY GAME ─────────────────────────────
    // Called by Unity via GET /api/get_question
    // Params: class_id, subject, type, quarter, student_id (optional, to avoid repeats)
    public function getQuestion(Request $request)
    {
        \Log::info('getQuestion hit: ', $request->all());
        $request->validate([
            'class_id'        => 'nullable|integer',
            'subject'         => 'required|string',
            'type'            => 'nullable|in:quiz,exam,assessment',
            'quarter'         => 'nullable|integer|between:1,4',
            'sequence_number' => 'nullable|integer|min:1',
        ]);

        $query = DB::table('questions');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        } else {
            $query->whereNull('class_id');
        }
        
        $query->where('subject',  strtolower(trim($request->subject)));

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('quarter')) {
            $query->where('quarter', $request->quarter);
        }

        if ($request->filled('sequence_number')) {
            $query->where('sequence_number', $request->sequence_number);
        }

        // Avoid repeating recently-seen questions for this student session
        // Unity passes answered IDs as a comma-separated string: ?answered=1,2,5
        if ($request->filled('answered')) {
            $answeredIds = array_filter(
                array_map('intval', explode(',', $request->answered))
            );
            if (!empty($answeredIds)) {
                $query->whereNotIn('id', $answeredIds);
            }
        }

        $question = $query->inRandomOrder()->first();

        // If no regular question found, try to fetch a prototype question
        if (!$question) {
            $prototypeQuery = DB::table('questions')->where('type', 'prototype');
            
            if ($request->filled('answered') && !empty($answeredIds)) {
                $prototypeQuery->whereNotIn('id', $answeredIds);
            }

            $question = $prototypeQuery->inRandomOrder()->first();
        }

        if (!$question) {
            return response()->json([
                'error' => 'No questions found for this class/subject/quarter. Ask your teacher to add questions.',
            ], 404);
        }

        return response()->json([
            'id'       => $question->id,
            'question' => $question->question,
            'A'        => $question->choice_a,
            'B'        => $question->choice_b,
            'C'        => $question->choice_c,
            'D'        => $question->choice_d,
            'answer'   => $question->answer,
        ]);
    }

    // ─── GET ALL STUDENTS (search) ────────────────────────────────
    // GET /backend/get_students.php?search=juan
    public function getStudents(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $search = $request->input('search', '');

        $students = DB::table('users')
            ->where('role', 'student')
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%");
            })
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('last_name')
            ->get();

        foreach ($students as $student) {
            $student->name = trim($student->first_name . ' ' . $student->last_name);
        }

        return response()->json(['success' => true, 'students' => $students]);
    }

    // ─── GET STUDENTS IN A CLASS ──────────────────────────────────
    // GET /backend/get_class_students.php?class_id=3
    public function getClassStudents(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate(['class_id' => 'required|integer']);

        $teacherId = session('user_id');

        // Verify class belongs to teacher
        $class = DB::table('classes')
            ->where('id', $request->class_id)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found']);
        }

        $students = DB::table('class_students')
            ->join('users', 'class_students.student_id', '=', 'users.id')
            ->where('class_students.class_id', $request->class_id)
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderBy('users.last_name')
            ->get();

        $subjectFilter = $request->query('subject', 'all');
        $typeFilter    = $request->query('type', 'all');

        foreach ($students as $student) {
            $student->name = trim($student->first_name . ' ' . $student->last_name);

            $scoreQuery = DB::table('student_scores')
                ->where('student_id', $student->id)
                ->where('class_id', $request->class_id);

            if ($subjectFilter !== 'all') {
                $scoreQuery->where('subject', $subjectFilter);
            }
            if ($typeFilter !== 'all') {
                $scoreQuery->where('type', $typeFilter);
            }

            $scores = $scoreQuery->get();

            if ($scores->isEmpty()) {
                $student->score   = 0;
                $student->total   = 0;
                $student->correct = 0;
                $student->subject = '—';
                $student->type    = '—';
            } else {
                $totalCorrect = $scores->sum('correct');
                $totalItems   = $scores->sum('total');
                $student->correct = $totalCorrect;
                $student->total   = $totalItems;
                $student->score   = $totalItems > 0 ? round(($totalCorrect / $totalItems) * 100) : 0;

                $student->subject = $subjectFilter !== 'all' ? $subjectFilter : 'Multiple';
                $student->type    = $typeFilter !== 'all' ? $typeFilter : 'Multiple';
            }
        }

        return response()->json(['success' => true, 'students' => $students]);
    }

    // ─── ENROLL STUDENT ───────────────────────────────────────────
    // POST /backend/enroll_student.php  { class_id, student_id }
    public function enrollStudent(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'class_id'      => 'required|integer|exists:classes,id',
            'student_ids'   => 'required|array',
            'student_ids.*' => 'integer|exists:users,id',
        ]);

        $teacherId = session('user_id');

        $class = DB::table('classes')
            ->where('id', $request->class_id)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found']);
        }

        $enrolledCount = 0;

        foreach ($request->student_ids as $studentId) {
            // Prevent duplicate enrollment
            $exists = DB::table('class_students')
                ->where('class_id',   $request->class_id)
                ->where('student_id', $studentId)
                ->exists();

            if (!$exists) {
                DB::table('class_students')->insert([
                    'class_id'   => $request->class_id,
                    'student_id' => $studentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $enrolledCount++;
            }
        }

        return response()->json(['success' => true, 'message' => $enrolledCount . ' student(s) enrolled successfully']);
    }

    // ─── UNENROLL STUDENT ─────────────────────────────────────────
    // POST /backend/unenroll_student.php  { class_id, student_id }
    public function unenrollStudent(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'class_id'   => 'required|integer',
            'student_id' => 'required|integer',
        ]);

        $teacherId = session('user_id');

        $class = DB::table('classes')
            ->where('id', $request->class_id)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found']);
        }

        DB::table('class_students')
            ->where('class_id',   $request->class_id)
            ->where('student_id', $request->student_id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Student unenrolled successfully']);
    }

    // ─── GET STUDENT SCORES ───────────────────────────────────────
    // GET /backend/get_student_scores.php?class_id=3
    public function getStudentScores(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'class_id'   => 'nullable|integer',
            'student_id' => 'nullable|integer'
        ]);

        if (!$request->filled('class_id') && !$request->filled('student_id')) {
            return response()->json(['success' => false, 'message' => 'Missing class_id or student_id']);
        }

        $teacherId = session('user_id');

        $query = DB::table('student_scores')
            ->join('users', 'student_scores.student_id', '=', 'users.id')
            ->select(
                'users.first_name',
                'users.last_name',
                'student_scores.subject',
                'student_scores.type',
                'student_scores.correct',
                'student_scores.total',
                'student_scores.percent',
                'student_scores.created_at'
            );

        if ($request->filled('class_id')) {
            $class = DB::table('classes')
                ->where('id', $request->class_id)
                ->where('teacher_id', $teacherId)
                ->first();

            if (!$class) {
                return response()->json(['success' => false, 'message' => 'Class not found']);
            }
            $query->where('student_scores.class_id', $request->class_id);
        }

        if ($request->filled('student_id')) {
            $query->where('student_scores.student_id', $request->student_id);
        }

        $scores = $query->orderBy('users.last_name')
            ->orderBy('student_scores.created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'scores' => $scores]);
    }

    // ─── GET STUDENT ANALYTICS (GRAPHS) ───────────────────────────
    // GET /backend/get_student_analytics.php?class_id=3
    public function getStudentAnalytics(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'class_id' => 'required|integer'
        ]);

        $teacherId = session('user_id');

        $class = DB::table('classes')
            ->where('id', $request->class_id)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found']);
        }

        // Fetch scores for the class, with correct/wrong logic, plus created_at for sorting
        $scores = DB::table('student_scores')
            ->join('users', 'student_scores.student_id', '=', 'users.id')
            ->where('student_scores.class_id', $request->class_id)
            ->select(
                'users.id as student_id',
                'users.first_name',
                'users.last_name',
                'student_scores.subject',
                'student_scores.type',
                'student_scores.quarter',
                'student_scores.sequence_number',
                'student_scores.correct',
                'student_scores.total',
                'student_scores.created_at'
            )
            ->orderBy('student_scores.created_at', 'desc')
            ->get();

        // Calculate wrong points
        $analytics = $scores->map(function ($score) {
            $score->wrong = $score->total - $score->correct;
            return $score;
        });

        return response()->json(['success' => true, 'analytics' => $analytics]);
    }

    // ─── SAVE PROTOTYPE GRADE ───────────────────────────────────────────
    public function savePrototypeGrade(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'student_id' => 'required|integer',
            'grade' => 'required|numeric|min:60|max:100',
        ]);

        DB::table('student_scores')->insert([
            'student_id' => $request->student_id,
            'class_id' => null,
            'subject' => 'prototype',
            'type' => 'prototype',
            'quarter' => 1,
            'sequence_number' => 1,
            'correct' => $request->grade,
            'total' => 100,
            'percent' => $request->grade,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send low grade email alert if grade is below 75%
        $this->sendLowGradeAlertIfNeeded($request->student_id, 'prototype', 'prototype', (float) $request->grade);

        return response()->json(['success' => true, 'message' => 'Prototype grade saved successfully!']);
    }

    // ─── SAVE STUDENT NOTE ──────────────────────────────────────────────
    public function saveStudentNote(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'note'       => 'required|string|max:1000',
        ]);

        $teacherId = session('user_id');

        DB::table('student_notes')->insert([
            'teacher_id' => $teacherId,
            'student_id' => $request->student_id,
            'note'       => $request->note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Note sent to student successfully!']);
    }

    // ─── LOW GRADE EMAIL ALERT HELPER ─────────────────────────────────────
    private function sendLowGradeAlertIfNeeded(int $studentId, string $subject, string $type, float $grade): void
    {
        if ($grade >= 75) {
            return; // No alert needed
        }

        $student = DB::table('users')->where('id', $studentId)->first();

        if (!$student || empty($student->email)) {
            return;
        }

        $studentName = trim($student->first_name . ' ' . $student->last_name);

        try {
            Mail::to($student->email)->send(
                new LowGradeAlert($studentName, $student->email, $subject, $type, $grade)
            );
        } catch (\Exception $e) {
            \Log::warning('LowGradeAlert email failed for student ' . $studentId . ': ' . $e->getMessage());
        }
    }
}