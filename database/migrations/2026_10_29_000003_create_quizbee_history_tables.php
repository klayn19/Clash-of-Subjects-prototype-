<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizbee_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('student_id')->index();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quizbee_attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->string('subject', 100);
            $table->text('question');
            $table->string('correct_answer_key', 20);
            $table->text('correct_answer_text');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizbee_attempt_questions');
        Schema::dropIfExists('quizbee_attempts');
    }
};
