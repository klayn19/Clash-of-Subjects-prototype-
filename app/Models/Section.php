<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Section extends Model
{
    protected $fillable = ['name'];

    public static function syncClassRoster(int $classId): void
    {
        $class = DB::table('classes')->where('id', $classId)->first();
        if (!$class || !$class->section) {
            return;
        }

        $studentIds = DB::table('users')
            ->where('role', 'student')
            ->where('section', $class->section)
            ->pluck('id');

        $existingStudentIds = DB::table('class_students')
            ->where('class_id', $classId)
            ->pluck('student_id');

        $missingStudentIds = $studentIds->diff($existingStudentIds);
        $now = now();
        foreach ($missingStudentIds as $studentId) {
            DB::table('class_students')->insert([
                'class_id' => $classId,
                'student_id' => $studentId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $classStudents = DB::table('class_students')->where('class_id', $classId);
        if ($studentIds->isEmpty()) {
            $classStudents->delete();
        } else {
            $classStudents->whereNotIn('student_id', $studentIds)->delete();
        }
    }

    public static function syncStudentRoster(int $studentId): void
    {
        $student = DB::table('users')
            ->where('id', $studentId)
            ->where('role', 'student')
            ->first(['id', 'section']);

        if (!$student || !$student->section) {
            DB::table('class_students')->where('student_id', $studentId)->delete();
            return;
        }

        $classIds = DB::table('classes')
            ->where('section', $student->section)
            ->pluck('id');

        $existingClassIds = DB::table('class_students')
            ->where('student_id', $studentId)
            ->pluck('class_id');

        $missingClassIds = $classIds->diff($existingClassIds);
        $now = now();
        foreach ($missingClassIds as $classId) {
            DB::table('class_students')->insert([
                'class_id' => $classId,
                'student_id' => $studentId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $studentClasses = DB::table('class_students')->where('student_id', $studentId);
        if ($classIds->isEmpty()) {
            $studentClasses->delete();
        } else {
            $studentClasses->whereNotIn('class_id', $classIds)->delete();
        }
    }
}
