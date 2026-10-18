<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowGradeAlert;
use App\Mail\ParentLowGradeAlert;
use App\Models\Section;

class TeacherController extends Controller
{
    // ─── TEACHER DASHBOARD ────────────────────────────────────────
    public function index()
    {
        if (session('user_role') !== 'teacher') {
            return redirect('/');
        }

        $teacherId = session('user_id');

        $teacherClassIds = DB::table('classes')
            ->where('teacher_id', $teacherId)
            ->pluck('id');
        foreach ($teacherClassIds as $classId) {
            Section::syncClassRoster((int) $classId);
        }

        $classes = DB::table('classes')
            ->leftJoin('class_students', 'classes.id', '=', 'class_students.class_id')
            ->select(
                'classes.id',
                'classes.name',
                'classes.section',
                DB::raw('COUNT(DISTINCT class_students.student_id) as student_count')
            )
            ->where('classes.teacher_id', $teacherId)
            ->groupBy('classes.id', 'classes.name', 'classes.section')
            ->orderBy('classes.id')
            ->get();

        $sections = Section::orderBy('name')->get();

        return view('teacher.dashboard', compact('classes', 'sections'));
    }

    // ─── SAVE / UPDATE CLASS ──────────────────────────────────────
    public function saveClass(Request $request)
    {
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $teacherId = session('user_id');

        if ($request->filled('id')) {
            $request->validate([
                'id' => 'required|integer',
                'section_id' => 'required|integer|exists:sections,id',
            ]);

            $section = Section::findOrFail($request->section_id);
            $class = DB::table('classes')
                ->where('id', $request->id)
                ->where('teacher_id', $teacherId)
                ->first();

            if (!$class) {
                return response()->json(['success' => false, 'message' => 'Class not found or not yours']);
            }

            $alreadyAssigned = DB::table('classes')
                ->where('teacher_id', $teacherId)
                ->where('section', $section->name)
                ->where('id', '!=', $class->id)
                ->exists();

            if ($alreadyAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a class for this section.',
                ], 422);
            }

            DB::table('classes')->where('id', $class->id)->update([
                'name' => $section->name,
                'section' => $section->name,
                'updated_at' => now(),
            ]);
            Section::syncClassRoster((int) $class->id);

            return response()->json(['success' => true, 'message' => 'Section updated successfully']);
        }

        $request->validate([
            'section_id' => 'required|integer|exists:sections,id',
        ]);

        $section = Section::findOrFail($request->section_id);
        $alreadyAssigned = DB::table('classes')
            ->where('teacher_id', $teacherId)
            ->where('section', $section->name)
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a class for this section.',
            ], 422);
        }

        $classId = DB::table('classes')->insertGetId([
            'name'       => $section->name,
            'section'    => $section->name,
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Section::syncClassRoster($classId);

        return response()->json([
            'success' => true,
            'message' => 'Section added successfully.',
            'class_id' => $classId,
        ]);
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
            'class_id'             => 'required|integer|exists:classes,id',
            'subject'              => 'required|string|max:100',
            'type'                 => 'required|in:quiz,exam,assessment',
            'quarter'              => 'required|integer|between:1,4',
            'sequence_number'      => 'required|integer|min:1',
            'questions'            => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.choice_a' => 'required|string',
            'questions.*.choice_b' => 'required|string',
            'questions.*.choice_c' => 'required|string',
            'questions.*.choice_d' => 'required|string',
            'questions.*.answer'   => 'required|in:A,B,C,D',
        ]);

        $teacherId = session('user_id');

        $class = DB::table('classes')
            ->where('id', $request->class_id)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found or not yours']);
        }

        DB::table('questions')
            ->where('class_id', $request->class_id)
            ->where('subject',  $request->subject)
            ->where('type',     $request->type)
            ->where('quarter',  $request->quarter)
            ->where('sequence_number', $request->sequence_number)
            ->delete();

        // Bulk insert new questions
        $rows = [];
        foreach ($request->questions as $q) {
            $rows[] = [
                'class_id'        => $request->class_id,
                'subject'         => strtolower(trim($request->subject)),
                'type'            => $request->type,
                'quarter'         => $request->quarter,
                'sequence_number' => $request->sequence_number,
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
            'inserted' => count($rows),
            'skipped' => 0,
            'message' => count($rows) . ' question(s) saved successfully',
        ]);
    }

    public function bulkUploadQuestions(Request $request)
    {
        if (session('user_role') !== 'teacher' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a teacher to upload questions.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'subject' => 'required|in:english,math,science',
            'type' => 'required|in:quiz,exam,assessment',
            'quarter' => 'required|integer|between:1,4',
            'sequence_number' => 'required|integer|min:1',
            'questions_file' => 'required|file|extensions:csv|max:5120',
        ]);

        $class = DB::table('classes')
            ->where('id', $validated['class_id'])
            ->where('teacher_id', session('user_id'))
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found or not yours.'], 404);
        }

        $handle = fopen($validated['questions_file']->getRealPath(), 'rb');
        if ($handle === false) {
            \Log::error('Could not open uploaded question CSV.');
            return response()->json(['success' => false, 'message' => 'Could not read the uploaded CSV file.'], 500);
        }

        $requiredHeaders = ['question', 'choice_a', 'choice_b', 'choice_c', 'choice_d', 'answer'];
        $headerIndexes = [];
        $questions = [];
        $rowNumber = 0;
        $parseError = null;

        try {
            $headers = fgetcsv($handle, null, ',', '"', '\\');
            $rowNumber++;
            if ($headers === false) {
                $parseError = 'The CSV file is empty.';
            } else {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
                $normalizedHeaders = array_map(
                    static fn ($header) => strtolower(trim((string) $header)),
                    $headers
                );

                if (count($normalizedHeaders) !== count(array_unique($normalizedHeaders))) {
                    $parseError = 'The CSV header contains duplicate column names.';
                } else {
                    foreach ($requiredHeaders as $requiredHeader) {
                        $index = array_search($requiredHeader, $normalizedHeaders, true);
                        if ($index === false) {
                            $parseError = 'CSV is missing the required column: ' . $requiredHeader . '.';
                            break;
                        }
                        $headerIndexes[$requiredHeader] = $index;
                    }
                }
            }

            while ($parseError === null && ($record = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
                $rowNumber++;
                if (count(array_filter($record, static fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                if (count($questions) >= 500) {
                    $parseError = 'A CSV upload may contain no more than 500 questions.';
                    break;
                }
                if (count($record) !== count($headers)) {
                    $parseError = 'CSV row ' . $rowNumber . ' has a different number of columns than the header.';
                    break;
                }

                $question = [];
                foreach ($requiredHeaders as $requiredHeader) {
                    $question[$requiredHeader] = trim((string) $record[$headerIndexes[$requiredHeader]]);
                }

                foreach (['question', 'choice_a', 'choice_b', 'choice_c', 'choice_d'] as $requiredField) {
                    if ($question[$requiredField] === '') {
                        $parseError = 'CSV row ' . $rowNumber . ' is missing ' . $requiredField . '.';
                        break;
                    }
                }
                if ($parseError !== null) {
                    break;
                }

                if (mb_strlen($question['question']) > 10000) {
                    $parseError = 'CSV row ' . $rowNumber . ' question exceeds 10,000 characters.';
                    break;
                }
                foreach (['choice_a', 'choice_b', 'choice_c', 'choice_d'] as $choiceField) {
                    if (mb_strlen($question[$choiceField]) > 255) {
                        $parseError = 'CSV row ' . $rowNumber . ' ' . $choiceField . ' exceeds 255 characters.';
                        break;
                    }
                }
                if ($parseError !== null) {
                    break;
                }

                $question['answer'] = strtoupper($question['answer']);
                if (!in_array($question['answer'], ['A', 'B', 'C', 'D'], true)) {
                    $parseError = 'CSV row ' . $rowNumber . ' answer must be A, B, C, or D.';
                    break;
                }

                $questions[] = $question;
            }
        } finally {
            fclose($handle);
        }

        if ($parseError !== null) {
            return response()->json(['success' => false, 'message' => $parseError], 422);
        }
        if ($questions === []) {
            return response()->json(['success' => false, 'message' => 'The CSV contains no question rows.'], 422);
        }

        $now = now();
        $rows = array_map(static fn ($question) => [
            'class_id' => $validated['class_id'],
            'subject' => $validated['subject'],
            'type' => $validated['type'],
            'quarter' => $validated['quarter'],
            'sequence_number' => $validated['sequence_number'],
            'question' => $question['question'],
            'choice_a' => $question['choice_a'],
            'choice_b' => $question['choice_b'],
            'choice_c' => $question['choice_c'],
            'choice_d' => $question['choice_d'],
            'answer' => $question['answer'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $questions);

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 100) as $batch) {
                DB::table('questions')->insert($batch);
            }
        });

        return response()->json([
            'success' => true,
            'inserted' => count($rows),
            'message' => count($rows) . ' question(s) uploaded successfully and appended to the selected set.',
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

        $classId = $request->query('class_id');
        if (empty($classId) && session('user_id')) {
            $classStudent = DB::table('class_students')
                ->where('student_id', session('user_id'))
                ->first();
            if ($classStudent) {
                $classId = $classStudent->class_id;
            }
        }

        $subject = strtolower(trim($request->subject));
        $isMixed = ($subject === 'mixed' || $subject === 'all');

        $buildQuery = function ($withClass) use ($request, $classId, $subject, $isMixed) {
            $q = DB::table('questions');
            if ($withClass && $classId) {
                $q->where('class_id', $classId);
            }
            if ($isMixed) {
                $q->whereIn('subject', ['english', 'math', 'science']);
            } else {
                $q->where('subject', $subject);
            }
            $q->whereIn('type', ['quiz', 'exam', 'assessment']);
            if ($request->filled('type')) {
                $q->where('type', $request->type);
            }
            if ($request->filled('quarter')) {
                $q->where('quarter', $request->quarter);
            }
            if ($request->filled('sequence_number')) {
                $q->where('sequence_number', $request->sequence_number);
            }
            return $q;
        };

        $query = $buildQuery(true);
        if ($classId && (clone $query)->count() === 0) {
            $query = $buildQuery(false);
        }

        // Count total matching questions before filtering answered
        $totalQuestionsInPool = (clone $query)->count();

        // Avoid repeating recently-seen questions for this student session
        $answeredIds = [];
        if ($request->filled('answered')) {
            $answeredIds = array_filter(
                array_map('intval', explode(',', $request->answered))
            );
            if (!empty($answeredIds)) {
                $query->whereNotIn('id', $answeredIds);
            }
        }

        $remainingCount = (clone $query)->count();
        $question = $query->inRandomOrder()->first();

        // If no matching question is found, try another regular question.
        if (!$question) {
            $fallbackQuery = DB::table('questions');
            $fallbackQuery->whereIn('type', ['quiz', 'exam', 'assessment']);
            if ($isMixed) {
                $fallbackQuery->whereIn('subject', ['english', 'math', 'science']);
            }
            if ($request->filled('answered') && !empty($answeredIds)) {
                $fallbackQuery->whereNotIn('id', $answeredIds);
            }
            $question = $fallbackQuery->inRandomOrder()->first();
        }

        if (!$question) {
            return response()->json([
                'completed'      => true,
                'cycle_finished' => true,
                'error'          => 'No questions found for this class/subject/quarter. Ask your teacher to add questions.',
            ], 404);
        }

        $remainingAfterThis = max(0, $remainingCount - 1);
        $isLast = ($remainingAfterThis === 0);

        return response()->json([
            'id'              => $question->id,
            'question'        => $question->question,
            'A'               => $question->choice_a,
            'B'               => $question->choice_b,
            'C'               => $question->choice_c,
            'D'               => $question->choice_d,
            'answer'          => $question->answer,
            'explanation'     => $question->explanation ?? '',
            'definition'      => $question->explanation ?? '',
            'is_last'         => $isLast,
            'remaining'       => $remainingAfterThis,
            'total_questions' => $totalQuestionsInPool,
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
            ->select('id', 'first_name', 'last_name', 'email', 'section')
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

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'subject' => 'nullable|in:all,english,math,science',
            'type' => 'nullable|in:all,quiz,exam,assessment,grade',
            'quarter' => 'nullable|in:all,1,2,3,4',
        ]);

        $teacherId = session('user_id');

        // Verify class belongs to teacher
        $class = DB::table('classes')
            ->where('id', $request->class_id)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found']);
        }

        Section::syncClassRoster((int) $class->id);

        $students = DB::table('class_students')
            ->join('users', 'class_students.student_id', '=', 'users.id')
            ->where('class_students.class_id', $request->class_id)
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.parent_email')
            ->orderBy('users.last_name')
            ->get();

        $bestSubjectScores = DB::table('student_scores')
            ->where('class_id', $request->class_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('subject', ['english', 'math', 'science'])
            ->select('student_id', 'subject', DB::raw('MAX(percent) as percent'))
            ->groupBy('student_id', 'subject')
            ->get()
            ->keyBy(fn ($score) => $score->student_id . ':' . $score->subject);

        $subjectFilter = $validated['subject'] ?? 'all';
        $typeFilter    = $validated['type'] ?? 'all';
        $quarterFilter = $validated['quarter'] ?? 'all';

        foreach ($students as $student) {
            $student->name = trim($student->first_name . ' ' . $student->last_name);
            $student->best_subjects = [];
            foreach (['english', 'math', 'science'] as $subject) {
                $bestScore = $bestSubjectScores->get($student->id . ':' . $subject);
                $student->best_subjects[$subject] = $bestScore ? round((float) $bestScore->percent, 2) : null;
            }

            $scoreQuery = DB::table('student_scores')
                ->where('student_id', $student->id)
                ->where('class_id', $request->class_id);

            if ($subjectFilter !== 'all') {
                $scoreQuery->where('subject', $subjectFilter);
            }
            if ($typeFilter !== 'all') {
                $scoreQuery->where('type', $typeFilter);
            }
            if ($quarterFilter !== 'all') {
                $scoreQuery->where('quarter', $quarterFilter);
            }

            $scores = $scoreQuery->get();
            $student->has_parent_email = !empty($student->parent_email);
            unset($student->parent_email);

            if ($scores->isEmpty()) {
                $student->score   = 0;
                $student->total   = 0;
                $student->correct = 0;
                $student->subject = '—';
                $student->type    = '—';
                $student->quarter = '—';
            } else {
                $totalCorrect = $scores->sum('correct');
                $totalItems   = $scores->sum('total');
                $student->correct = $totalCorrect;
                $student->total   = $totalItems;
                $student->score   = $totalItems > 0 ? round(($totalCorrect / $totalItems) * 100) : 0;

                $student->subject = $subjectFilter !== 'all' ? $subjectFilter : 'Multiple';
                $student->type    = $typeFilter !== 'all' ? $typeFilter : 'Multiple';
                $quarters = $scores->pluck('quarter')->unique()->sort()->values();
                $student->quarter = $quarterFilter !== 'all'
                    ? 'Q' . $quarterFilter
                    : ($quarters->count() === 1 ? 'Q' . $quarters->first() : 'Multiple');
            }
        }

        return response()->json(['success' => true, 'students' => $students]);
    }

    public function emailParentLowGrade(Request $request)
    {
        if (session('user_role') !== 'teacher' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a teacher to send parent alerts.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'student_id' => 'required|integer',
            'subject' => 'required|in:all,english,math,science',
            'type' => 'required|in:all,quiz,exam,assessment,grade',
            'quarter' => 'required|in:all,1,2,3,4',
        ]);

        $class = DB::table('classes')
            ->where('id', $validated['class_id'])
            ->where('teacher_id', session('user_id'))
            ->first(['id']);
        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found or not yours.'], 404);
        }

        $student = DB::table('users')
            ->join('class_students', 'users.id', '=', 'class_students.student_id')
            ->where('class_students.class_id', $class->id)
            ->where('users.id', $validated['student_id'])
            ->where('users.role', 'student')
            ->first([
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.parent_email',
            ]);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student is not assigned to this class.'], 404);
        }
        if (empty($student->parent_email)) {
            return response()->json(['success' => false, 'message' => 'Add a parent/guardian email to this student account before sending an alert.'], 422);
        }

        $scoresQuery = DB::table('student_scores')
            ->where('class_id', $class->id)
            ->where('student_id', $student->id);
        if ($validated['subject'] !== 'all') {
            $scoresQuery->where('subject', $validated['subject']);
        }
        if ($validated['type'] !== 'all') {
            $scoresQuery->where('type', $validated['type']);
        }
        if ($validated['quarter'] !== 'all') {
            $scoresQuery->where('quarter', $validated['quarter']);
        }

        $scores = $scoresQuery->get(['subject', 'type', 'quarter', 'correct', 'total']);
        $totalCorrect = $scores->sum('correct');
        $total = $scores->sum('total');
        if ($total <= 0) {
            return response()->json(['success' => false, 'message' => 'There are no scores for this student under the selected filters.'], 422);
        }

        $percentage = round(($totalCorrect / $total) * 100, 2);
        if ($percentage >= 75) {
            return response()->json(['success' => false, 'message' => 'This score is not below the 75% passing threshold.'], 422);
        }

        $studentName = trim($student->first_name . ' ' . $student->last_name);
        $subjects = $scores->pluck('subject')->unique()->values();
        $types = $scores->pluck('type')->unique()->values();
        $quarters = $scores->pluck('quarter')->unique()->sort()->values();

        try {
            Mail::to($student->parent_email)->send(new ParentLowGradeAlert(
                $studentName,
                $subjects->count() === 1 ? $subjects->first() : 'Multiple Subjects',
                $types->count() === 1 ? $types->first() : 'Multiple Assessments',
                $percentage,
                $validated['quarter'] !== 'all'
                    ? (int) $validated['quarter']
                    : ($quarters->count() === 1 ? (int) $quarters->first() : null),
                (float) $totalCorrect,
                (float) $total,
            ));
        } catch (\Throwable $exception) {
            \Log::error('Parent low-grade alert email failed.', [
                'teacher_id' => session('user_id'),
                'student_id' => $student->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Email could not be sent. Check the mail configuration and try again.'], 502);
        }

        return response()->json(['success' => true, 'message' => 'Failing-score alert sent to the parent/guardian email.']);
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

    public function saveManualGrade(Request $request)
    {
        if (session('user_role') !== 'teacher' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a teacher to save grades.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'student_id' => 'required|integer|exists:users,id',
            'subject' => 'required|in:english,math,science',
            'quarter' => 'required|integer|between:1,4',
            'grade' => 'required|integer|min:0|max:100',
        ]);

        $class = DB::table('classes')
            ->where('id', $validated['class_id'])
            ->where('teacher_id', session('user_id'))
            ->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found or not yours.'], 404);
        }

        $student = DB::table('users')
            ->where('id', $validated['student_id'])
            ->where('role', 'student')
            ->exists();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        Section::syncClassRoster((int) $class->id);
        $isEnrolled = DB::table('class_students')
            ->where('class_id', $class->id)
            ->where('student_id', $validated['student_id'])
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['success' => false, 'message' => 'Student is not assigned to this class section.'], 422);
        }

        $sequenceNumber = DB::table('student_scores')
            ->where('class_id', $class->id)
            ->where('student_id', $validated['student_id'])
            ->where('subject', $validated['subject'])
            ->where('type', 'grade')
            ->where('quarter', $validated['quarter'])
            ->max('sequence_number') + 1;
        $grade = (int) $validated['grade'];

        DB::table('student_scores')->insert([
            'student_id' => $validated['student_id'],
            'class_id' => $class->id,
            'subject' => $validated['subject'],
            'type' => 'grade',
            'quarter' => $validated['quarter'],
            'sequence_number' => $sequenceNumber,
            'correct' => $grade,
            'total' => 100,
            'percent' => $grade,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grade saved successfully.',
        ]);
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

    public function issueQuizBeeCode(Request $request)
    {
        if (session('user_role') !== 'teacher' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a teacher to issue QuizBee codes.'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
        ]);

        $student = DB::table('users')
            ->where('id', $validated['student_id'])
            ->where('role', 'student')
            ->first(['id']);

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Select a valid student.'], 422);
        }

        $appKey = (string) config('app.key');
        if ($appKey === '') {
            \Log::error('QuizBee code issuance is unavailable because the application key is not configured.');
            return response()->json(['success' => false, 'message' => 'QuizBee access is temporarily unavailable. Please try again later.'], 500);
        }

        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $codeHash = hash_hmac('sha256', $code, $appKey);
        } while (DB::table('quizbee_access_codes')
            ->where('code_hash', $codeHash)
            ->whereNull('redeemed_at')
            ->where('expires_at', '>', now())
            ->exists());

        $expiresAt = now()->addHours(24);
        $teacherId = session('user_id');

        DB::transaction(function () use ($teacherId, $student, $codeHash, $expiresAt, $code) {
            DB::table('quizbee_access_codes')->insert([
                'teacher_id' => $teacherId,
                'student_id' => $student->id,
                'code_hash' => $codeHash,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('student_notes')->insert([
                'teacher_id' => $teacherId,
                'student_id' => $student->id,
                'note' => 'QuizBee access code: ' . $code . '. Valid until ' . $expiresAt->format('Y-m-d H:i T') . '. Single-use.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'code' => $code,
            'expires_at' => $expiresAt->toIso8601String(),
            'message' => 'QuizBee code issued and recorded in the student notes.',
        ]);
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