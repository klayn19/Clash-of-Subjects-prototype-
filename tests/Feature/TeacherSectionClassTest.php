<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Mail\TeacherWelcome;
use Tests\TestCase;

class TeacherSectionClassTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('lrn')->nullable()->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('age')->nullable();
            $table->string('email')->unique();
            $table->string('parent_email')->nullable();
            $table->string('password');
            $table->string('role');
            $table->string('section')->nullable();
            $table->string('subject')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('section')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->timestamps();
        });

        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamps();
        });

        Schema::create('student_rosters', function (Blueprint $table) {
            $table->id();
            $table->string('lrn', 12)->unique();
            $table->unsignedBigInteger('section_id');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('subject');
            $table->string('type');
            $table->integer('quarter')->default(1);
            $table->integer('sequence_number')->default(1);
            $table->text('question');
            $table->string('choice_a');
            $table->string('choice_b');
            $table->string('choice_c');
            $table->string('choice_d');
            $table->string('answer');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('student_rosters');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_admin_assigns_multiple_sections_and_matching_students_are_added_automatically(): void
    {
        $sectionIds = [];
        foreach (['Grade 7 - Rizal', 'Grade 8 - Bonifacio'] as $sectionName) {
            $sectionIds[] = DB::table('sections')->insertGetId([
                'name' => $sectionName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $teacherId = $this->createUser('teacher', null, 'teacher@example.test');
        $studentId = $this->createUser('student', 'Grade 7 - Rizal', 'student@example.test');
        $secondStudentId = $this->createUser('student', 'Grade 8 - Bonifacio', 'second@example.test');
        $otherStudentId = $this->createUser('student', 'Grade 9 - Jacinto', 'other@example.test');

        $response = $this->withSession([
            'user_id' => 999,
            'user_role' => 'admin',
        ])->postJson('/backend/admin/assign_teacher_section', [
            'teacher_id' => $teacherId,
            'section_ids' => $sectionIds,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Selected sections assigned to teacher.');

        $classIds = $response->json('class_ids');
        $this->assertCount(2, $classIds);
        $firstClassId = DB::table('classes')->where('teacher_id', $teacherId)->where('section', 'Grade 7 - Rizal')->value('id');
        $secondClassId = DB::table('classes')->where('teacher_id', $teacherId)->where('section', 'Grade 8 - Bonifacio')->value('id');
        $this->assertDatabaseHas('classes', [
            'id' => $firstClassId,
            'name' => 'Grade 7 - Rizal',
            'section' => 'Grade 7 - Rizal',
            'teacher_id' => $teacherId,
        ]);
        $this->assertDatabaseHas('classes', [
            'id' => $secondClassId,
            'name' => 'Grade 8 - Bonifacio',
            'section' => 'Grade 8 - Bonifacio',
            'teacher_id' => $teacherId,
        ]);
        $this->assertDatabaseHas('class_students', ['class_id' => $firstClassId, 'student_id' => $studentId]);
        $this->assertDatabaseHas('class_students', ['class_id' => $secondClassId, 'student_id' => $secondStudentId]);
        $this->assertDatabaseMissing('class_students', [
            'class_id' => $firstClassId,
            'student_id' => $otherStudentId,
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->get('/teacher/dashboard')
            ->assertOk()
            ->assertSee('Grade 7 - Rizal')
            ->assertSee('Grade 8 - Bonifacio')
            ->assertSee('1 student(s)')
            ->assertSee('id="examQuarter"', false)
            ->assertSee('id="examNumber"', false)
            ->assertSee('id="quizQuarter"', false)
            ->assertSee('id="quizNumber"', false)
            ->assertSee('id="submissionsTypeFilter"', false)
            ->assertSee('<option value="quiz">Quiz</option>', false)
            ->assertSee('<option value="exam">Exam</option>', false)
            ->assertSee('id="appDialogOverlay"', false)
            ->assertDontSee('confirm(', false)
            ->assertDontSee('alert(', false);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/admin/assign_teacher_section', [
            'teacher_id' => $teacherId,
            'section_ids' => $sectionIds,
        ])->assertForbidden();

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_class.php', ['section_id' => $sectionIds[0]])
            ->assertNotFound();
    }

    public function test_new_student_registration_automatically_adds_student_to_existing_section_class(): void
    {
        $sectionName = 'Grade 8 - Bonifacio';
        DB::table('sections')->insertGetId([
            'name' => $sectionName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $teacherId = $this->createUser('teacher', null, 'teacher2@example.test');
        $classId = DB::table('classes')->insertGetId([
            'name' => $sectionName,
            'section' => $sectionName,
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post('/register', [
            'lrn' => '123456789012',
            'section' => $sectionName,
            'first_name' => 'New',
            'last_name' => 'Student',
            'age' => 13,
            'email' => 'new.student@example.test',
            'parent_email' => 'guardian@example.test',
            'password' => 'CosPass1!',
            'confirm_password' => 'CosPass1!',
        ])->assertRedirect('/');

        $studentId = DB::table('users')->where('email', 'new.student@example.test')->value('id');
        $this->assertDatabaseHas('users', [
            'id' => $studentId,
            'parent_email' => 'guardian@example.test',
        ]);
        $this->assertDatabaseHas('class_students', [
            'class_id' => $classId,
            'student_id' => $studentId,
        ]);
    }

    public function test_admin_section_assignment_moves_student_to_their_section_class(): void
    {
        $oldSection = 'Grade 7 - Rizal';
        $newSection = 'Grade 8 - Bonifacio';
        foreach ([$oldSection, $newSection] as $sectionName) {
            DB::table('sections')->insert([
                'name' => $sectionName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminId = $this->createUser('admin', null, 'admin@example.test');
        $teacherId = $this->createUser('teacher', null, 'teacher4@example.test');
        $studentId = $this->createUser('student', $oldSection, 'section.change@example.test');
        $oldClassId = $this->createClass($teacherId, $oldSection);
        $newClassId = $this->createClass($teacherId, $newSection);

        DB::table('class_students')->insert([
            'class_id' => $oldClassId,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $adminId,
            'user_role' => 'admin',
        ])->postJson('/backend/admin/update_student', [
            'student_id' => $studentId,
            'lrn' => null,
            'section' => $newSection,
            'parent_email' => 'guardian.updated@example.test',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'id' => $studentId,
            'parent_email' => 'guardian.updated@example.test',
        ]);
        $this->assertDatabaseMissing('class_students', [
            'class_id' => $oldClassId,
            'student_id' => $studentId,
        ]);
        $this->assertDatabaseHas('class_students', [
            'class_id' => $newClassId,
            'student_id' => $studentId,
        ]);
    }

    public function test_admin_registers_teacher_with_subject_and_teacher_dashboard_displays_it(): void
    {
        Mail::fake();
        $sectionId = DB::table('sections')->insertGetId([
            'name' => 'Grade 7 - Rizal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['user_role' => 'admin'])
            ->post('/backend/admin/register_teacher', [
                'first_name' => 'Sam',
                'last_name' => 'Teacher',
                'email' => 'sam.teacher@example.test',
                'subject' => 'science',
                'section_ids' => [$sectionId],
                'password' => 'CosPass1!',
                'confirm_password' => 'CosPass1!',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Teacher account created successfully!');

        $teacherId = (int) DB::table('users')
            ->where('email', 'sam.teacher@example.test')
            ->value('id');
        $this->assertDatabaseHas('users', [
            'id' => $teacherId,
            'subject' => 'science',
        ]);
        $this->assertDatabaseHas('classes', [
            'teacher_id' => $teacherId,
            'section' => 'Grade 7 - Rizal',
        ]);
        Mail::assertSent(TeacherWelcome::class, function (TeacherWelcome $mail): bool {
            return $mail->hasTo('sam.teacher@example.test')
                && $mail->teacherName === 'Sam Teacher'
                && $mail->subjectName === 'Science';
        });

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
            'user_name' => 'Sam',
        ])->get('/teacher/dashboard')
            ->assertOk()
            ->assertSee('SUBJECT: SCIENCE')
            ->assertSee('id="assessmentSubject"', false)
            ->assertSee('<option value="science">Science</option>', false)
            ->assertDontSee('<option value="english">English</option>', false)
            ->assertSee('data-teacher-subject="science"', false)
            ->assertSee('id="submissionsPagination"', false)
            ->assertSee('id="noteSection"', false)
            ->assertSee('id="noteStudentSearch"', false)
            ->assertSee('id="classStudentSearch"', false);
    }

    public function test_teacher_can_review_their_quiz_exam_and_quizbee_questions_only(): void
    {
        $teacherId = $this->createUser('teacher', null, 'reviewer@example.test');
        $otherTeacherId = $this->createUser('teacher', null, 'other.reviewer@example.test');
        DB::table('users')->where('id', $teacherId)->update(['subject' => 'math']);
        DB::table('users')->where('id', $otherTeacherId)->update(['subject' => 'science']);
        $classId = $this->createClass($teacherId, 'Grade 7 - Rizal');
        $otherClassId = $this->createClass($otherTeacherId, 'Grade 8 - Bonifacio');

        foreach (['quiz', 'exam', 'quizbee', 'assessment'] as $type) {
            DB::table('questions')->insert([
                'class_id' => $classId,
                'subject' => 'math',
                'type' => $type,
                'quarter' => 2,
                'sequence_number' => 3,
                'question' => 'My submitted ' . $type . ' question',
                'choice_a' => 'Wrong option',
                'choice_b' => 'Correct option',
                'choice_c' => 'Another option',
                'choice_d' => 'Last option',
                'answer' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('questions')->insert([
            'class_id' => $classId,
            'subject' => 'science',
            'type' => 'quiz',
            'quarter' => 1,
            'sequence_number' => 1,
            'question' => 'My other-subject question',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('questions')->insert([
            'class_id' => $otherClassId,
            'subject' => 'science',
            'type' => 'quiz',
            'quarter' => 1,
            'sequence_number' => 1,
            'question' => 'Other teacher private question',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->get('/teacher/dashboard')
            ->assertOk()
            ->assertSee('MY SUBMITTED QUESTIONS')
            ->assertSee('QUESTION TYPE')
            ->assertSee('id="reviewQuestionSection"', false)
            ->assertSee('id="reviewQuestionQuarter"', false)
            ->assertSee('<option value="Grade 7 - Rizal">Grade 7 - Rizal</option>', false)
            ->assertSee('<option value="1">Quarter 1</option>', false)
            ->assertSee('id="submittedQuestionSets"', false)
            ->assertSee('data-question-type="quizbee"', false)
            ->assertSee('My submitted quiz question')
            ->assertSee('My submitted exam question')
            ->assertSee('My submitted quizbee question')
            ->assertSee('My submitted assessment question')
            ->assertSee('Correct answer')
            ->assertDontSee('My other-subject question')
            ->assertDontSee('Other teacher private question');
    }

    public function test_teacher_can_delete_a_question_from_their_submitted_questions(): void
    {
        $teacherId = $this->createUser('teacher', null, 'question.deleter@example.test');
        DB::table('users')->where('id', $teacherId)->update(['subject' => 'math']);
        $classId = $this->createClass($teacherId, 'Grade 7 - Rizal');
        $questionId = DB::table('questions')->insertGetId([
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'quiz',
            'quarter' => 1,
            'sequence_number' => 1,
            'question' => 'Question to delete?',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/delete_question.php', ['question_id' => $questionId])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
    }

    public function test_teacher_cannot_delete_another_teachers_question(): void
    {
        $teacherId = $this->createUser('teacher', null, 'question.owner@example.test');
        $otherTeacherId = $this->createUser('teacher', null, 'question.nonowner@example.test');
        DB::table('users')->where('id', $teacherId)->update(['subject' => 'math']);
        DB::table('users')->where('id', $otherTeacherId)->update(['subject' => 'math']);
        $classId = $this->createClass($otherTeacherId, 'Grade 8 - Bonifacio');
        $questionId = DB::table('questions')->insertGetId([
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'quiz',
            'quarter' => 1,
            'sequence_number' => 1,
            'question' => 'Another teacher question?',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/delete_question.php', ['question_id' => $questionId])
            ->assertNotFound()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('questions', ['id' => $questionId]);
    }

    public function test_teacher_can_delete_an_entire_submitted_question_set(): void
    {
        $teacherId = $this->createUser('teacher', null, 'question.set.deleter@example.test');
        DB::table('users')->where('id', $teacherId)->update(['subject' => 'math']);
        $classId = $this->createClass($teacherId, 'Grade 7 - Rizal');
        foreach (['quizbee', 'assessment'] as $type) {
            DB::table('questions')->insert([
                'class_id' => $classId,
                'subject' => 'math',
                'type' => $type,
                'quarter' => 2,
                'sequence_number' => 3,
                'question' => 'Question from ' . $type,
                'choice_a' => 'A',
                'choice_b' => 'B',
                'choice_c' => 'C',
                'choice_d' => 'D',
                'answer' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('questions')->insert([
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'quizbee',
            'quarter' => 2,
            'sequence_number' => 4,
            'question' => 'Question from another set',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/delete_question_set.php', [
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'quizbee',
            'quarter' => 2,
            'sequence_number' => 3,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('deleted', 2);

        $this->assertDatabaseMissing('questions', ['sequence_number' => 3]);
        $this->assertDatabaseHas('questions', ['sequence_number' => 4]);
    }

    public function test_teacher_cannot_delete_another_teachers_question_set(): void
    {
        $teacherId = $this->createUser('teacher', null, 'question.set.nonowner@example.test');
        $otherTeacherId = $this->createUser('teacher', null, 'question.set.owner@example.test');
        DB::table('users')->where('id', $teacherId)->update(['subject' => 'science']);
        $classId = $this->createClass($otherTeacherId, 'Grade 8 - Bonifacio');
        DB::table('questions')->insert([
            'class_id' => $classId,
            'subject' => 'science',
            'type' => 'exam',
            'quarter' => 1,
            'sequence_number' => 1,
            'question' => 'Private set question?',
            'choice_a' => 'A',
            'choice_b' => 'B',
            'choice_c' => 'C',
            'choice_d' => 'D',
            'answer' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/delete_question_set.php', [
            'class_id' => $classId,
            'subject' => 'science',
            'type' => 'exam',
            'quarter' => 1,
            'sequence_number' => 1,
        ])->assertNotFound()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('questions', ['question' => 'Private set question?']);
    }

    public function test_registration_rejects_names_with_digits_and_weak_passwords(): void
    {
        DB::table('sections')->insert([
            'name' => 'Grade 7 - Rizal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post('/register', [
            'lrn' => '123456789012',
            'section' => 'Grade 7 - Rizal',
            'first_name' => 'Stu2dent',
            'last_name' => 'Learner',
            'age' => 13,
            'email' => 'digit.name@example.test',
            'password' => 'CosPass1!',
            'confirm_password' => 'CosPass1!',
        ])->assertSessionHasErrors('first_name');

        $this->post('/register', [
            'lrn' => '123456789013',
            'section' => 'Grade 7 - Rizal',
            'first_name' => 'Student',
            'last_name' => 'Learner',
            'age' => 13,
            'email' => 'weak.password@example.test',
            'password' => 'student1!',
            'confirm_password' => 'student1!',
        ])->assertSessionHasErrors('password');

        $this->withSession(['user_role' => 'admin'])
            ->post('/backend/admin/register_teacher', [
                'first_name' => 'Teach3r',
                'last_name' => 'Instructor',
                'email' => 'digit.teacher@example.test',
                'subject' => 'science',
                'password' => 'CosPass1!',
                'confirm_password' => 'CosPass1!',
            ])->assertSessionHasErrors('first_name');

        $this->withSession(['user_role' => 'admin'])
            ->post('/backend/admin/register_teacher', [
                'first_name' => 'Teacher',
                'last_name' => 'Instructor',
                'email' => 'weak.teacher@example.test',
                'subject' => 'science',
                'password' => 'student1!',
                'confirm_password' => 'student1!',
            ])->assertSessionHasErrors('password');
    }

    private function createUser(string $role, ?string $section, string $email): int
    {
        return DB::table('users')->insertGetId([
            'first_name' => ucfirst($role),
            'last_name' => 'Test',
            'email' => $email,
            'password' => 'unused',
            'role' => $role,
            'section' => $section,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createClass(int $teacherId, string $section): int
    {
        return DB::table('classes')->insertGetId([
            'name' => $section,
            'section' => $section,
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
