<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminTeacherController extends Controller
{
    public function delete(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:users,id',
        ]);

        $teacher = User::where('id', $validated['teacher_id'])
            ->where('role', 'teacher')
            ->first();

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
        }

        DB::transaction(function () use ($teacher): void {
            $classIds = DB::table('classes')
                ->where('teacher_id', $teacher->id)
                ->pluck('id');

            if ($classIds->isNotEmpty()) {
                foreach (['questions', 'student_scores', 'class_students'] as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'class_id')) {
                        DB::table($table)->whereIn('class_id', $classIds)->delete();
                    }
                }

                DB::table('classes')->whereIn('id', $classIds)->delete();
            }

            foreach (['student_notes', 'quizbee_access_codes'] as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'teacher_id')) {
                    DB::table($table)->where('teacher_id', $teacher->id)->delete();
                }
            }

            if (Schema::hasTable('resetpassword')) {
                DB::table('resetpassword')->where('email', $teacher->email)->delete();
            }

            $teacher->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Teacher account, classes, questions, and class scores were removed.',
        ]);
    }
}
