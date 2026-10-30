<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'quizbee') NOT NULL");
            DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'quizbee', 'grade') NOT NULL");
        }

        DB::table('questions')->where('type', 'assessment')->update(['type' => 'quizbee']);
        DB::table('student_scores')->where('type', 'assessment')->update(['type' => 'quizbee']);
    }

    public function down(): void
    {
        DB::table('questions')->where('type', 'quizbee')->update(['type' => 'assessment']);
        DB::table('student_scores')->where('type', 'quizbee')->update(['type' => 'assessment']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
            DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'grade') NOT NULL");
        }
    }
};
