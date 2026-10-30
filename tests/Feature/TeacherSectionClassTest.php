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
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('student_rosters');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_teacher_adds_admin_section_and_matching_students_are_added_automatically(): void
    {
        $sectionId = DB::table('sections')->insertGetId([
            'name' => 'Grade 7 - Rizal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $teacherId = $this->createUser('teacher', null, 'teacher@example.test');
        $studentId = $this->createUser('student', 'Grade 7 - Rizal', 'student@example.test');
        $otherStudentId = $this->createUser('student', 'Grade 8 - Bonifacio', 'other@example.test');

        $response = $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_class.php', ['section_id' => $sectionId]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Section added successfully.');

        $classId = $response->json('class_id');
        $this->assertDatabaseHas('classes', [
            'id' => $classId,
            'name' => 'Grade 7 - Rizal',
            'section' => 'Grade 7 - Rizal',
            'teacher_id' => $teacherId,
        ]);
        $this->assertDatabaseHas('class_students', [
            'class_id' => $classId,
            'student_id' => $studentId,
        ]);
        $this->assertDatabaseMissing('class_students', [
            'class_id' => $classId,
            'student_id' => $otherStudentId,
        ]);

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->get('/teacher/dashboard')
            ->assertOk()
            ->assertSee('Grade 7 - Rizal')
            ->assertSee('1 student(s)');

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_class.php', ['section_id' => $sectionId])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
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

    public function test_teacher_cannot_add_a_section_that_admin_has_not_defined(): void
    {
        $teacherId = $this->createUser('teacher', null, 'teacher3@example.test');

        $this->withSession([
            'user_id' => $teacherId,
            'user_role' => 'teacher',
        ])->postJson('/backend/save_class.php', ['section_id' => 999])
            ->assertUnprocessable();

        $this->assertDatabaseCount('classes', 0);
    }

    public function test_admin_registers_teacher_with_subject_and_teacher_dashboard_displays_it(): void
    {
        Mail::fake();

        $this->withSession(['user_role' => 'admin'])
            ->post('/backend/admin/register_teacher', [
                'first_name' => 'Sam',
                'last_name' => 'Teacher',
                'email' => 'sam.teacher@example.test',
                'subject' => 'science',
                'password' => 'secret123',
                'confirm_password' => 'secret123',
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
            ->assertSee('SUBJECT: SCIENCE');
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
