<?php

namespace App\Http\Controllers;

use App\Support\QuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowGradeAlert;
use App\Mail\ParentLowGradeAlert;
use App\Models\Section;
use App\Models\User;
use App\Services\QuizbeeHistory;

class TeacherController extends Controller
{
    private function assignedTeacherSubject(): ?string
    {
        $subject = strtolower(trim((string) DB::table('users')
            ->where('id', session('user_id'))
            ->where('role', 'teacher')
            ->value('subject')));

        return in_array($subject, ['english', 'math', 'science'], true) ? $subject : null;
    }

    private function studentQuestionClassIds(?int $requestedClassId = null): array
    {
        $studentId = (int) session('user_id');
        $studentSection = DB::table('users')
            ->where('id', $studentId)
            ->where('role', 'student')
            ->value('section');

        if ($studentSection) {
            $classIds = DB::table('classes')
                ->where('section', $studentSection)
                ->pluck('id')
                ->all();
        } else {
            $classIds = DB::table('class_students')
                ->where('student_id', $studentId)
                ->pluck('class_id')
                ->all();
        }

        $classIds = array_values(array_unique(array_map('intval', $classIds)));
        if ($requestedClassId !== null && in_array($requestedClassId, $classIds, true)) {
            return [$requestedClassId];
        }

        if ($requestedClassId !== null) {
            Log::info('Ignoring a question request class ID outside the student section.', [
                'student_id' => $studentId,
                'requested_class_id' => $requestedClassId,
                'resolved_class_ids' => $classIds,
            ]);
        }

        return $classIds;
    }

    // ─── TEACHER DASHBOARD ────────────────────────────────────────
    public function index()
    {
        if (session('user_role') !== 'teacher') {
            return redirect('/');
        }

        $teacherId = session('user_id');
        $teacher = User::findOrFail($teacherId);
        $assignedSubject = $this->assignedTeacherSubject();

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

        $submittedQuestionSets = DB::table('questions')
            ->join('classes', 'questions.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $teacherId)
            ->when(
                $assignedSubject !== null,
                fn ($query) => $query->whereRaw('LOWER(questions.subject) = ?', [$assignedSubject]),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->orderByDesc('questions.created_at')
            ->orderBy('questions.type')
            ->orderBy('questions.subject')
            ->orderBy('questions.quarter')
            ->orderBy('questions.sequence_number')
            ->orderBy('questions.id')
            ->get([
                'questions.id',
                'questions.class_id',
                'questions.subject',
                'questions.type',
                'questions.quarter',
                'questions.sequence_number',
                'questions.question',
                'questions.choice_a',
                'questions.choice_b',
                'questions.choice_c',
                'questions.choice_d',
                'questions.answer',
                'questions.created_at',
                'classes.name as class_name',
                'classes.section',
            ])
            ->groupBy(fn ($question) => implode('|', [
                $question->class_id,
                strtolower($question->subject),
                in_array($question->type, ['assessment', 'quizbee'], true) ? 'quizbee' : $question->type,
                $question->quarter,
                $question->sequence_number,
            ]))
            ->map(fn ($questions) => (object) [
                'first' => $questions->first(),
                'questions' => $questions,
            ])
            ->values();

        return view('teacher.dashboard', compact('classes', 'teacher', 'submittedQuestionSets', 'assignedSubject'));
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

    public function deleteSubmittedQuestion(Request $request)
    {
        if (session('user_role') !== 'teacher' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $validated = $request->validate([
            'question_id' => 'required|integer',
        ]);
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject) {
            return response()->json(['success' => false, 'message' => 'No subject is assigned to this teacher account.'], 403);
        }

        $question = DB::table('questions')
            ->join('classes', 'questions.class_id', '=', 'classes.id')
            ->where('questions.id', $validated['question_id'])
            ->where('classes.teacher_id', session('user_id'))
            ->whereRaw('LOWER(questions.subject) = ?', [$assignedSubject])
            ->select('questions.id')
            ->first();

        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Question not found or not yours.'], 404);
        }

        DB::table('questions')->where('id', $question->id)->delete();

        return response()->json(['success' => true]);
    }

    public function deleteSubmittedQuestionSet(Request $request)
    {
        if (session('user_role') !== 'teacher' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'subject' => 'required|string|max:100',
            'type' => 'required|in:quiz,quizbee,exam',
            'quarter' => 'required|integer|between:1,4',
            'sequence_number' => 'required|integer|min:1',
        ]);
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject || strtolower(trim($validated['subject'])) !== $assignedSubject) {
            return response()->json(['success' => false, 'message' => 'You can only manage question sets for your assigned subject.'], 403);
        }

        $class = DB::table('classes')
            ->where('id', $validated['class_id'])
            ->where('teacher_id', session('user_id'))
            ->exists();
        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Question set not found or not yours.'], 404);
        }

        $query = DB::table('questions')
            ->where('class_id', $validated['class_id'])
            ->whereRaw('LOWER(subject) = ?', [$assignedSubject])
            ->where('quarter', $validated['quarter'])
            ->where('sequence_number', $validated['sequence_number']);

        if ($validated['type'] === 'quizbee') {
            $query->whereIn('type', ['quizbee', 'assessment']);
        } else {
            $query->where('type', $validated['type']);
        }

        $deleted = $query->delete();
        if ($deleted === 0) {
            return response()->json(['success' => false, 'message' => 'Question set was not found.'], 404);
        }

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    // ─── SAVE QUESTIONS ───────────────────────────────────────────
    // Accepts an array of questions to insert/replace for one or more classes.
    // Frontend should POST:
    //   { class_ids, subject, type, quarter, questions: [{question, choice_a, …, answer}, …] }
    public function saveQuestions(Request $request)
    {
        Log::info('saveQuestions hit. Session role: ' . session('user_role'));
        
        if (session('user_role') !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Session expired. Please refresh the page and log in.'], 403);
        }

        $validated = $request->validate([
            'class_id'             => 'required_without:class_ids|nullable|integer|exists:classes,id',
            'class_ids'            => 'required_without:class_id|array|min:1',
            'class_ids.*'          => 'required|integer|distinct|exists:classes,id',
            'subject'              => 'required|string|max:100',
            'type'                 => 'required|in:quiz,exam,assessment,quizbee',
            'quarter'              => 'required|integer|between:1,4',
            'sequence_number'      => 'required|integer|min:1',
            'append'               => 'sometimes|boolean',
            'questions'            => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.choice_a' => 'required|string',
            'questions.*.choice_b' => 'required|string',
            'questions.*.choice_c' => 'required|string',
            'questions.*.choice_d' => 'required|string',
            'questions.*.answer'   => 'required|in:A,B,C,D',
        ]);
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject || strtolower(trim($request->input('subject'))) !== $assignedSubject) {
            return response()->json(['success' => false, 'message' => 'You can only save questions for your assigned subject.'], 403);
        }

        $teacherId = session('user_id');
        $classIds = array_values(array_unique(array_map(
            'intval',
            $validated['class_ids'] ?? [$validated['class_id']]
        )));
        $ownedClassCount = DB::table('classes')
            ->whereIn('id', $classIds)
            ->where('teacher_id', $teacherId)
            ->count();

        if ($ownedClassCount !== count($classIds)) {
            return response()->json(['success' => false, 'message' => 'One or more classes were not found or are not yours.'], 404);
        }

        $subject = $assignedSubject;
        $type = $request->input('type') === 'assessment' ? 'quizbee' : $request->input('type');
        $append = $request->boolean('append');

        DB::transaction(function () use ($request, $classIds, $subject, $type, $append) {
            foreach ($classIds as $classId) {
                if (!$append) {
                    DB::table('questions')
                        ->where('class_id', $classId)
                        ->whereRaw('LOWER(subject) = ?', [$subject])
                        ->where('type', $type)
                        ->where('quarter', $request->quarter)
                        ->where('sequence_number', $request->sequence_number)
                        ->delete();
                }

                $rows = [];
                foreach ($request->questions as $question) {
                    $rows[] = [
                        'class_id'        => $classId,
                        'subject'         => $subject,
                        'type'            => $type,
                        'quarter'         => $request->quarter,
                        'sequence_number' => $request->sequence_number,
                        'question'        => $question['question'],
                        'choice_a'        => $question['choice_a'],
                        'choice_b'        => $question['choice_b'],
                        'choice_c'        => $question['choice_c'],
                        'choice_d'        => $question['choice_d'],
                        'answer'          => strtoupper($question['answer']),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                DB::table('questions')->insert($rows);
            }
        });

        $inserted = count($validated['questions']) * count($classIds);
        return response()->json([
            'success' => true,
            'inserted' => $inserted,
            'skipped' => 0,
            'message' => count($validated['questions']) . ' question(s) ' .
                ($append ? 'appended' : 'saved') . ' successfully to ' . count($classIds) .
                (count($classIds) === 1 ? ' class' : ' classes'),
        ]);
    }

    public function bulkUploadQuestions(Request $request)
    {
        if (session('user_role') !== 'teacher' || !session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Please log in as a teacher to upload questions.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required_without:class_ids|nullable|integer|exists:classes,id',
            'class_ids' => 'required_without:class_id|array|min:1',
            'class_ids.*' => 'required|integer|distinct|exists:classes,id',
            'subject' => 'required|in:english,math,science',
            'type' => 'required|in:quiz,exam,assessment,quizbee',
            'quarter' => 'required|integer|between:1,4',
            'sequence_number' => 'required|integer|min:1',
            'questions_file' => 'required|file|extensions:csv|max:5120',
        ]);
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject || $validated['subject'] !== $assignedSubject) {
            return response()->json(['success' => false, 'message' => 'You can only upload questions for your assigned subject.'], 403);
        }

        $classIds = array_values(array_unique(array_map(
            'intval',
            $validated['class_ids'] ?? [$validated['class_id']]
        )));
        $ownedClassCount = DB::table('classes')
            ->whereIn('id', $classIds)
            ->where('teacher_id', session('user_id'))
            ->count();

        if ($ownedClassCount !== count($classIds)) {
            return response()->json(['success' => false, 'message' => 'One or more classes were not found or are not yours.'], 404);
        }

        $validated['type'] = $validated['type'] === 'assessment' ? 'quizbee' : $validated['type'];

        $handle = fopen($validated['questions_file']->getRealPath(), 'rb');
        if ($handle === false) {
            Log::error('Could not open uploaded question CSV.');
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
        DB::transaction(function () use ($classIds, $validated, $questions, $now) {
            foreach ($classIds as $classId) {
                $rows = array_map(static fn ($question) => [
                    'class_id' => $classId,
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

                foreach (array_chunk($rows, 100) as $batch) {
                    DB::table('questions')->insert($batch);
                }
            }
        });

        $inserted = count($questions) * count($classIds);
        return response()->json([
            'success' => true,
            'inserted' => $inserted,
            'message' => count($questions) . ' question(s) uploaded successfully to ' .
                count($classIds) . (count($classIds) === 1 ? ' class' : ' classes') . ' and appended.',
        ]);
    }

    // ─── GET QUESTION FOR UNITY GAME ─────────────────────────────
    // Called by Unity via GET /api/get_question
    // Params: class_id, subject, type, quarter, student_id (optional, to avoid repeats)
    public function getQuizNumbers(Request $request)
    {
        $request->merge([
            'subject' => strtolower(trim((string) $request->input('subject', ''))),
        ]);
        $validated = $request->validate([
            'class_id' => 'required|integer|min:1',
            'subject' => 'required|in:english,math,science',
            'type' => 'required|in:quiz,exam,assessment,quizbee',
            'quarter' => 'required|integer|between:1,4',
        ]);

        $classId = (int) $validated['class_id'];
        $classIds = [$classId];
        if (session('user_role') === 'student' && session('user_id')) {
            $classIds = $this->studentQuestionClassIds($classId);
        }

        $type = $validated['type'] === 'assessment' ? 'quizbee' : $validated['type'];
        $isStudent = session('user_role') === 'student' && session('user_id');
        $query = DB::table('questions')
            ->whereRaw('LOWER(subject) = ?', [$validated['subject']])
            ->where('type', $type)
            ->where('quarter', $validated['quarter'])
            ->where('sequence_number', '>=', 1);

        if ($classIds !== []) {
            $query->where(function ($classQuery) use ($classIds, $isStudent, $type) {
                $classQuery->whereIn('class_id', $classIds);
                if (!$isStudent || !in_array($type, ['quiz', 'exam'], true)) {
                    $classQuery->orWhereNull('class_id');
                }
            });
        } elseif ($isStudent && !in_array($type, ['quiz', 'exam'], true)) {
            $query->whereNull('class_id');
        } elseif ($isStudent) {
            $query->whereRaw('1 = 0');
        }

        $quizNumbers = $query->distinct()
            ->orderBy('sequence_number')
            ->pluck('sequence_number')
            ->map(static fn ($number) => (int) $number)
            ->values();

        return response()->json(['quiz_numbers' => $quizNumbers]);
    }

    public function getQuestion(Request $request)
    {
        Log::info('getQuestion hit: ', $request->all());
        $request->merge([
            'subject' => strtolower(trim((string) $request->input('subject', ''))),
        ]);
        $request->validate([
            'class_id'        => 'nullable|integer',
            'subject'         => 'required|in:english,math,science,mixed,all',
            'type'            => 'nullable|in:quiz,exam,assessment,quizbee',
            'quarter'         => 'nullable|integer|between:1,4',
            'sequence_number' => 'nullable|integer|min:1',
            'quiz_number'     => 'nullable|integer|min:1',
        ]);

        $query = DB::table('questions');

        $classIds = [];
        $classId = $request->input('class_id');
        $isStudent = session('user_role') === 'student' && session('user_id');
        if ($isStudent) {
            $classIds = $this->studentQuestionClassIds($classId !== null ? (int) $classId : null);
        } elseif (!empty($classId)) {
            $classIds = [(int) $classId];
        } elseif (session('user_id')) {
            $classIds = DB::table('class_students')
                ->where('student_id', session('user_id'))
                ->pluck('class_id')
                ->all();
        }

        $classIds = array_values(array_unique(array_map('intval', $classIds)));

        $subject = strtolower(trim($request->subject));
        $type = $request->input('type', 'quiz');
        if ($type === 'assessment') {
            $type = 'quizbee';
        }
        $isMixed = ($subject === 'mixed' || $subject === 'all');
        if ($isStudent && in_array($type, ['quiz', 'exam'], true) && $isMixed) {
            return response()->json([
                'completed' => true,
                'cycle_finished' => true,
                'error' => 'Select a subject before loading quiz or exam questions.',
            ], 422);
        }
        $quizbeeHistory = app(QuizbeeHistory::class);
        $quizbeeAttemptId = $quizbeeHistory->activeAttemptId(
            $request->input('mode') === 'quizbee' ? 'quizbee' : $type
        );

        $sequenceNumber = $request->input('quiz_number', $request->input('sequence_number'));
        $buildQuery = function () use ($request, $classIds, $isStudent, $subject, $isMixed, $type, $sequenceNumber) {
            $q = DB::table('questions');
            if ($isStudent || $classIds !== []) {
                $q->where(function ($classQuery) use ($classIds, $isStudent, $type) {
                    if ($classIds !== []) {
                        $classQuery->whereIn('class_id', $classIds);
                        if (!$isStudent || !in_array($type, ['quiz', 'exam'], true)) {
                            $classQuery->orWhereNull('class_id');
                        }
                    } elseif (!$isStudent || !in_array($type, ['quiz', 'exam'], true)) {
                        $classQuery->whereNull('class_id');
                    } else {
                        $classQuery->whereRaw('1 = 0');
                    }
                });
            }
            if ($isMixed) {
                $q->whereIn(DB::raw('LOWER(subject)'), ['english', 'math', 'science']);
            } else {
                $q->whereRaw('LOWER(subject) = ?', [$subject]);
            }
            $q->where('type', $type);
            if ($request->filled('quarter')) {
                $q->where('quarter', $request->quarter);
            }
            if ($sequenceNumber !== null && $sequenceNumber !== '') {
                $q->where('sequence_number', $sequenceNumber);
            }
            return $q;
        };

        $query = $buildQuery();

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

        if ($quizbeeAttemptId && !empty($answeredIds)) {
            $quizbeeHistory->recordAnsweredQuestions($quizbeeAttemptId, $answeredIds);
        }

        $remainingCount = (clone $query)->count();
        $question = $query->inRandomOrder()->first();

        if (!$question) {
            if ($quizbeeAttemptId && $totalQuestionsInPool > 0 && $remainingCount === 0) {
                $quizbeeHistory->completeAttempt($quizbeeAttemptId, (int) session('user_id'));
            }

            return response()->json([
                'completed'      => true,
                'cycle_finished' => true,
                'error'          => 'No questions found for this class/subject/quarter. Ask your teacher to add questions.',
            ]);
        }

        if ($quizbeeAttemptId) {
            $quizbeeHistory->recordServedQuestion($quizbeeAttemptId, $question);
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
            'answer'          => QuestionAnswer::normalizeKey($question->answer, [
                'A' => $question->choice_a,
                'B' => $question->choice_b,
                'C' => $question->choice_c,
                'D' => $question->choice_d,
            ]),
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
            'type' => 'nullable|in:all,quiz,exam,assessment,quizbee,grade',
            'quarter' => 'nullable|in:all,1,2,3,4',
        ]);
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject) {
            return response()->json(['success' => false, 'message' => 'No subject is assigned to this teacher account.'], 403);
        }

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
            ->where('subject', $assignedSubject)
            ->select('student_id', 'subject', DB::raw('MAX(percent) as percent'))
            ->groupBy('student_id', 'subject')
            ->get()
            ->keyBy(fn ($score) => $score->student_id . ':' . $score->subject);

        $subjectFilter = $assignedSubject;
        $typeFilter    = $validated['type'] ?? 'all';
        if ($typeFilter === 'assessment') {
            $typeFilter = 'quizbee';
        }
        $quarterFilter = $validated['quarter'] ?? 'all';

        foreach ($students as $student) {
            $student->name = trim($student->first_name . ' ' . $student->last_name);
            $student->best_subjects = [];
            foreach ([$assignedSubject] as $subject) {
                $bestScore = $bestSubjectScores->get($student->id . ':' . $subject);
                $student->best_subjects[$subject] = $bestScore ? round((float) $bestScore->percent, 2) : null;
            }
            foreach (array_diff(['english', 'math', 'science'], [$assignedSubject]) as $subject) {
                $student->best_subjects[$subject] = null;
            }

            $scoreQuery = DB::table('student_scores')
                ->where('student_id', $student->id)
                ->where('class_id', $request->class_id)
                ->where('subject', $assignedSubject);
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
            'type' => 'required|in:all,quiz,exam,assessment,quizbee,grade',
            'quarter' => 'required|in:all,1,2,3,4',
        ]);
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject) {
            return response()->json(['success' => false, 'message' => 'No subject is assigned to this teacher account.'], 403);
        }

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
            ->where('student_id', $student->id)
            ->where('subject', $assignedSubject);
        $type = $validated['type'] === 'assessment' ? 'quizbee' : $validated['type'];
        if ($type !== 'all') {
            $scoresQuery->where('type', $type);
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
            Log::error('Parent low-grade alert email failed.', [
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
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject) {
            return response()->json(['success' => false, 'message' => 'No subject is assigned to this teacher account.'], 403);
        }

        $teacherId = session('user_id');

        $query = DB::table('student_scores')
            ->join('users', 'student_scores.student_id', '=', 'users.id')
            ->where('student_scores.subject', $assignedSubject)
            ->whereIn('student_scores.class_id', DB::table('classes')
                ->select('id')
                ->where('teacher_id', $teacherId))
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
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject) {
            return response()->json(['success' => false, 'message' => 'No subject is assigned to this teacher account.'], 403);
        }

        // Fetch scores for the class, with correct/wrong logic, plus created_at for sorting
        $scores = DB::table('student_scores')
            ->join('users', 'student_scores.student_id', '=', 'users.id')
            ->where(function ($query) use ($request) {
                $query->where('student_scores.class_id', $request->class_id)
                    ->orWhere(function ($legacyScores) use ($request) {
                        $legacyScores->whereNull('student_scores.class_id')
                            ->whereExists(function ($classMembership) use ($request) {
                                $classMembership->selectRaw('1')
                                    ->from('class_students')
                                    ->whereColumn('class_students.student_id', 'student_scores.student_id')
                                    ->where('class_students.class_id', $request->class_id);
                            });
                    });
            })
            ->where('student_scores.subject', $assignedSubject)
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
        $assignedSubject = $this->assignedTeacherSubject();
        if (!$assignedSubject || $validated['subject'] !== $assignedSubject) {
            return response()->json(['success' => false, 'message' => 'You can only manage grades for your assigned subject.'], 403);
        }

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
            ->first(['id', 'first_name', 'last_name', 'parent_email']);

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

        $notificationStatus = 'not_required';
        $message = 'Grade saved successfully.';

        if ($grade < 75) {
            if (empty($student->parent_email)) {
                $notificationStatus = 'missing_parent_email';
                $message = 'Grade saved, but no parent/guardian email is registered, so no alert was sent.';
            } else {
                try {
                    Mail::to($student->parent_email)->send(new ParentLowGradeAlert(
                        trim($student->first_name . ' ' . $student->last_name),
                        $validated['subject'],
                        'grade',
                        (float) $grade,
                        (int) $validated['quarter'],
                        (float) $grade,
                        100.0,
                    ));
                    $notificationStatus = 'sent';
                    $message = 'Grade saved and a low-grade alert was sent to the parent/guardian.';
                } catch (\Throwable $exception) {
                    Log::error('Parent low-grade alert email failed after manual grade save.', [
                        'teacher_id' => session('user_id'),
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'message' => $exception->getMessage(),
                    ]);
                    $notificationStatus = 'failed';
                    $message = 'Grade saved, but the parent/guardian email could not be sent. Check the mail configuration.';
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'notification_status' => $notificationStatus,
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
            Log::error('QuizBee code issuance is unavailable because the application key is not configured.');
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
            Log::warning('LowGradeAlert email failed for student ' . $studentId . ': ' . $e->getMessage());
        }
    }
}