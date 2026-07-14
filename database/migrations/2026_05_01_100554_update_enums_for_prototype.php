<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'prototype') NOT NULL");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment', 'prototype') NOT NULL");
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
        }
    }
};
