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
        // 1. Update questions table
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'sequence_number')) {
                $table->integer('sequence_number')->default(1)->after('quarter');
            }
        });

        // Modifying column natively in MySQL without doctrine/dbal
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
        }

        // 2. Update student_scores table
        Schema::table('student_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('student_scores', 'quarter')) {
                $table->tinyInteger('quarter')->default(1)->after('type');
            }
            if (!Schema::hasColumn('student_scores', 'sequence_number')) {
                $table->integer('sequence_number')->default(1)->after('quarter');
            }
        });

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam', 'assessment') NOT NULL");
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Drop sequence_number column
            $table->dropColumn('sequence_number');
        });

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('quiz', 'exam') NOT NULL");
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
        }

        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropColumn(['quarter', 'sequence_number']);
        });

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores MODIFY COLUMN type ENUM('quiz', 'exam') NOT NULL");
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE student_scores ALTER COLUMN type TYPE VARCHAR(100), ALTER COLUMN type SET NOT NULL");
        }
    }
};
