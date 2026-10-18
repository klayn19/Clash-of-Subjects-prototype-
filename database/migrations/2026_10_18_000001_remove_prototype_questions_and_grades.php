<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('questions')
            ->where('type', 'prototype')
            ->orWhere('subject', 'prototype')
            ->delete();

        DB::table('student_scores')
            ->where('type', 'prototype')
            ->orWhere('subject', 'prototype')
            ->delete();

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
            DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type IN ('quiz', 'exam', 'assessment'))");
            DB::statement("ALTER TABLE student_scores ADD CONSTRAINT student_scores_type_check CHECK (type IN ('quiz', 'exam', 'assessment'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'prototype') NOT NULL");
            DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'prototype') NOT NULL");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check');
            DB::statement('ALTER TABLE student_scores DROP CONSTRAINT IF EXISTS student_scores_type_check');
        }
    }
};
