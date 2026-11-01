<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentRosterVerificationTest extends TestCase
{
    private ?string $csvPath = null;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('lrn')->nullable()->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('email')->unique();
            $table->string('parent_email')->nullable();
            $table->string('password');
            $table->string('role');
            $table->string('section')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('student_rosters', function (Blueprint $table) {
            $table->id();
            $table->string('lrn', 12)->unique();
            $table->unsignedBigInteger('section_id');
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
        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('subject');
            $table->string('type');
            $table->integer('correct');
            $table->integer('total');
            $table->decimal('percent', 5, 2);
            $table->timestamps();
        });

        $now = now();
        DB::table('sections')->insert([
            ['name' => 'Grade 7 - Rizal', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Grade 7 - Bonifacio', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Grade 7 - Jacinto', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->csvPath && is_file($this->csvPath)) {
            unlink($this->csvPath);
        }

        foreach (['student_scores', 'class_students', 'classes', 'student_rosters', 'sections', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_roster_import_verifies_existing_matches_and_skips_duplicate_rows(): void
    {
        $existingId = $this->createStudent('111111111111', 'Grade 7 - Bonifacio');
        $this->writeCsv([
            ['111111111111', 'Grade 7 - Rizal'],
            ['222222222222', 'Grade 7 - Jacinto'],
            ['222222222222', 'Grade 7 - Jacinto'],
            ['invalid', 'Grade 7 - Rizal'],
        ]);

        $this->assertSame(0, Artisan::call('roster:import', ['file' => $this->csvPath]));
        $this->assertStringContainsString('Imported 2 unique roster entries', Artisan::output());
        $this->assertDatabaseHas('student_rosters', [
            'lrn' => '111111111111',
            'section_id' => DB::table('sections')->where('name', 'Grade 7 - Rizal')->value('id'),
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $existingId,
            'section' => 'Grade 7 - Rizal',
            'is_verified' => true,
        ]);
        $this->assertDatabaseHas('student_rosters', ['lrn' => '222222222222']);
        $this->assertDatabaseCount('student_rosters', 2);
    }

    public function test_registration_verifies_matching_lrn_and_preserves_unmatched_accounts_as_unverified(): void
    {
        $rizalId = (int) DB::table('sections')->where('name', 'Grade 7 - Rizal')->value('id');
        DB::table('student_rosters')->insert([
            'lrn' => '333333333333',
            'section_id' => $rizalId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->registerStudent('333333333333', 'matched@example.test', 'Grade 7 - Jacinto')
            ->assertRedirect('/')
            ->assertSessionHas('success', 'Registration successful! Your student record has been verified.');
        $this->assertDatabaseHas('users', [
            'lrn' => '333333333333',
            'section' => 'Grade 7 - Rizal',
            'is_verified' => true,
        ]);

        $this->registerStudent('444444444444', 'unmatched@example.test', 'Grade 7 - Jacinto')
            ->assertRedirect('/')
            ->assertSessionHas('success', 'Registration successful! Your account is not verified against the student roster yet.');
        $this->assertDatabaseHas('users', [
            'lrn' => '444444444444',
            'section' => 'Grade 7 - Jacinto',
            'is_verified' => false,
        ]);
    }

    public function test_registration_rejects_password_without_required_character_types(): void
    {
        $this->registerStudent('999999999999', 'weak.password@example.test', 'Grade 7 - Rizal', 'secret123')
            ->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['lrn' => '999999999999']);
    }

    public function test_admin_dashboard_shows_student_verification_status(): void
    {
        $this->createStudent('555555555555');
        $verifiedId = $this->createStudent('666666666666');
        DB::table('users')->where('id', $verifiedId)->update(['is_verified' => true]);
        DB::table('users')->insert([
            'first_name' => 'Needs',
            'last_name' => 'Lrn',
            'age' => 13,
            'email' => 'needs.lrn@example.test',
            'password' => 'unused',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $teacherId = DB::table('users')->insertGetId([
            'first_name' => 'Teacher',
            'last_name' => 'Visible',
            'email' => 'teacher-visible@example.test',
            'password' => 'unused',
            'role' => 'teacher',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['user_id' => 1, 'user_role' => 'admin'])
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('VERIFIED')
            ->assertSee('NOT VERIFIED')
            ->assertSee('LRN VERIFICATION')
            ->assertSee('STUDENT LRN VERIFICATION')
            ->assertSee('searchLrnReview')
            ->assertSee('Name, LRN, or section...')
            ->assertSee('ADD LRN')
            ->assertSee('VERIFY THIS LRN')
            ->assertSee('REMOVE')
            ->assertSee('teacher-delete-btn')
            ->assertSee('removeTeacher(' . $teacherId, false);
    }

    public function test_admin_can_add_and_manually_verify_a_student_lrn(): void
    {
        $studentId = (int) DB::table('users')->insertGetId([
            'first_name' => 'New',
            'last_name' => 'Student',
            'age' => 13,
            'email' => 'new.student@example.test',
            'password' => 'unused',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['user_id' => 1, 'user_role' => 'admin'])
            ->postJson('/backend/admin/update_student', [
                'student_id' => $studentId,
                'lrn' => '888888888888',
                'is_verified' => true,
                'section' => 'Grade 7 - Rizal',
                'parent_email' => '',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('verified', true);

        $this->assertDatabaseHas('users', [
            'id' => $studentId,
            'lrn' => '888888888888',
            'section' => 'Grade 7 - Rizal',
            'is_verified' => true,
        ]);
    }

    public function test_admin_cannot_verify_a_student_without_an_lrn(): void
    {
        $studentId = (int) DB::table('users')->insertGetId([
            'first_name' => 'No',
            'last_name' => 'Lrn',
            'age' => 13,
            'email' => 'no.lrn@example.test',
            'password' => 'unused',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['user_id' => 1, 'user_role' => 'admin'])
            ->postJson('/backend/admin/update_student', [
                'student_id' => $studentId,
                'lrn' => null,
                'is_verified' => true,
                'section' => 'Grade 7 - Rizal',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Enter the student LRN before verifying it.');

        $this->assertDatabaseHas('users', [
            'id' => $studentId,
            'lrn' => null,
            'is_verified' => false,
        ]);
    }

    public function test_admin_cannot_assign_an_lrn_already_used_by_another_student(): void
    {
        $existingStudentId = $this->createStudent('999999999999');
        $newStudentId = (int) DB::table('users')->insertGetId([
            'first_name' => 'Another',
            'last_name' => 'Student',
            'age' => 13,
            'email' => 'another.student@example.test',
            'password' => 'unused',
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['user_id' => 1, 'user_role' => 'admin'])
            ->postJson('/backend/admin/update_student', [
                'student_id' => $newStudentId,
                'lrn' => '999999999999',
                'is_verified' => true,
                'section' => 'Grade 7 - Rizal',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('users', [
            'id' => $existingStudentId,
            'lrn' => '999999999999',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $newStudentId,
            'lrn' => null,
            'is_verified' => false,
        ]);
    }

    public function test_admin_can_remove_a_student_and_their_scores_and_class_membership(): void
    {
        $studentId = $this->createStudent('777777777777');
        $teacherId = DB::table('users')->insertGetId([
            'first_name' => 'Teacher',
            'last_name' => 'One',
            'email' => 'teacher@example.test',
            'password' => 'unused',
            'role' => 'teacher',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classId = DB::table('classes')->insertGetId([
            'name' => 'Grade 7 - Rizal',
            'section' => 'Grade 7 - Rizal',
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('class_students')->insert([
            'class_id' => $classId,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_scores')->insert([
            'student_id' => $studentId,
            'class_id' => $classId,
            'subject' => 'math',
            'type' => 'quiz',
            'correct' => 8,
            'total' => 10,
            'percent' => 80,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminId = DB::table('users')->insertGetId([
            'first_name' => 'Admin',
            'last_name' => 'One',
            'email' => 'admin@example.test',
            'password' => 'unused',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['user_id' => $adminId, 'user_role' => 'admin'])
            ->postJson('/backend/admin/delete_student', ['student_id' => $studentId])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $studentId]);
        $this->assertDatabaseMissing('student_scores', ['student_id' => $studentId]);
        $this->assertDatabaseMissing('class_students', ['student_id' => $studentId]);
    }

    public function test_non_admin_cannot_remove_a_student(): void
    {
        $studentId = $this->createStudent('888888888888');

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->postJson('/backend/admin/delete_student', ['student_id' => $studentId])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $studentId]);
    }

    public function test_admin_can_remove_teacher_and_the_teachers_class_data(): void
    {
        $teacherId = DB::table('users')->insertGetId([
            'first_name' => 'Teacher',
            'last_name' => 'To Remove',
            'email' => 'teacher-to-remove@example.test',
            'password' => 'unused',
            'role' => 'teacher',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = $this->createStudent('777777777777');
        $classId = DB::table('classes')->insertGetId([
            'name' => 'Grade 7',
            'section' => 'Grade 7 - Rizal',
            'teacher_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('class_students')->insert([
            'class_id' => $classId,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_scores')->insert([
            'student_id' => $studentId,
            'class_id' => $classId,
            'subject' => 'english',
            'type' => 'quiz',
            'correct' => 8,
            'total' => 10,
            'percent' => 80,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $adminId = DB::table('users')->insertGetId([
            'first_name' => 'Admin',
            'last_name' => 'One',
            'email' => 'teacher-delete-admin@example.test',
            'password' => 'unused',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['user_id' => $adminId, 'user_role' => 'admin'])
            ->postJson('/backend/admin/delete_teacher', ['teacher_id' => $teacherId])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $teacherId]);
        $this->assertDatabaseMissing('classes', ['id' => $classId]);
        $this->assertDatabaseMissing('class_students', ['class_id' => $classId]);
        $this->assertDatabaseMissing('student_scores', ['class_id' => $classId]);
        $this->assertDatabaseHas('users', ['id' => $studentId, 'role' => 'student']);
    }

    public function test_non_admin_cannot_remove_a_teacher(): void
    {
        $teacherId = DB::table('users')->insertGetId([
            'first_name' => 'Teacher',
            'last_name' => 'Protected',
            'email' => 'teacher-protected@example.test',
            'password' => 'unused',
            'role' => 'teacher',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = $this->createStudent('666666666666');

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->postJson('/backend/admin/delete_teacher', ['teacher_id' => $teacherId])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $teacherId, 'role' => 'teacher']);
    }

    private function registerStudent(string $lrn, string $email, string $section, string $password = 'CosPass1!')
    {
        return $this->post('/register', [
            'lrn' => $lrn,
            'section' => $section,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'age' => 13,
            'email' => $email,
            'password' => $password,
            'confirm_password' => $password,
        ]);
    }

    private function createStudent(string $lrn, ?string $section = null): int
    {
        return (int) DB::table('users')->insertGetId([
            'lrn' => $lrn,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'age' => 13,
            'email' => $lrn . '@example.test',
            'password' => 'unused',
            'role' => 'student',
            'section' => $section,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function writeCsv(array $rows): void
    {
        $this->csvPath = tempnam(sys_get_temp_dir(), 'student-roster-');
        $handle = fopen($this->csvPath, 'wb');
        fputcsv($handle, ['lrn', 'section']);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}
