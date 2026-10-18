<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'grade') NOT NULL");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE student_scores DROP CONSTRAINT IF EXISTS student_scores_type_check');
            DB::statement("ALTER TABLE student_scores ADD CONSTRAINT student_scores_type_check CHECK (type IN ('quiz', 'exam', 'assessment', 'grade'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('student_scores')->where('type', 'grade')->update(['type' => 'assessment']);
            DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE student_scores DROP CONSTRAINT IF EXISTS student_scores_type_check');
            DB::table('student_scores')->where('type', 'grade')->update(['type' => 'assessment']);
            DB::statement("ALTER TABLE student_scores ADD CONSTRAINT student_scores_type_check CHECK (type IN ('quiz', 'exam', 'assessment'))");
        }
    }
};
