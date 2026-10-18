<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentNoteReadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('student_id');
            $table->text('note');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('student_notes');

        parent::tearDown();
    }

    public function test_student_can_mark_own_note_read_and_cannot_mark_another_students_note(): void
    {
        $studentId = 21;
        $otherStudentId = 22;
        $ownNoteId = $this->createNote($studentId);
        $otherNoteId = $this->createNote($otherStudentId);

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->postJson(route('student.notes.read', ['noteId' => $ownNoteId]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull(DB::table('student_notes')->where('id', $ownNoteId)->value('read_at'));

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->postJson(route('student.notes.read', ['noteId' => $otherNoteId]))
            ->assertNotFound()
            ->assertJsonPath('success', false);

        $this->assertNull(DB::table('student_notes')->where('id', $otherNoteId)->value('read_at'));
    }

    public function test_student_can_mark_all_and_only_their_own_notes_read(): void
    {
        $studentId = 31;
        $otherStudentId = 32;
        $ownNoteId = $this->createNote($studentId);
        $otherNoteId = $this->createNote($otherStudentId);

        $this->withSession(['user_id' => $studentId, 'user_role' => 'student'])
            ->postJson(route('student.notes.read-all'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull(DB::table('student_notes')->where('id', $ownNoteId)->value('read_at'));
        $this->assertNull(DB::table('student_notes')->where('id', $otherNoteId)->value('read_at'));
    }

    public function test_non_students_cannot_change_note_read_status(): void
    {
        $noteId = $this->createNote(41);

        $this->withSession(['user_id' => 42, 'user_role' => 'teacher'])
            ->postJson(route('student.notes.read', ['noteId' => $noteId]))
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertNull(DB::table('student_notes')->where('id', $noteId)->value('read_at'));
    }

    private function createNote(int $studentId): int
    {
        return DB::table('student_notes')->insertGetId([
            'teacher_id' => 1,
            'student_id' => $studentId,
            'note' => 'Test message',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
