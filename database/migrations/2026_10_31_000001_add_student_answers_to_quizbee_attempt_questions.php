<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizbee_attempt_questions', function (Blueprint $table) {
            $table->string('student_answer_key', 20)->nullable()->after('correct_answer_text');
            $table->text('student_answer_text')->nullable()->after('student_answer_key');
            $table->boolean('is_correct')->nullable()->after('student_answer_text');
        });
    }

    public function down(): void
    {
        Schema::table('quizbee_attempt_questions', function (Blueprint $table) {
            $table->dropColumn(['student_answer_key', 'student_answer_text', 'is_correct']);
        });
    }
};
