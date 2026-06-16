<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Add role to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student')->after('email');
            }
        });

        // 2. Create classes table
        if (!Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('section')->nullable();
                $table->unsignedBigInteger('teacher_id');
                $table->timestamps();

                $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 3. Create class_students table
        if (!Schema::hasTable('class_students')) {
            Schema::create('class_students', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id');
                $table->unsignedBigInteger('student_id');
                $table->timestamps();

                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
                $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 4. Create questions table
        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->string('subject');
                $table->enum('type', ['quiz', 'exam']);
                $table->text('question');
                $table->string('choice_a');
                $table->string('choice_b');
                $table->string('choice_c');
                $table->string('choice_d');
                $table->string('answer');
                $table->timestamps();
            });
        }

        // 5. Create scores table
        if (!Schema::hasTable('student_scores')) {
            Schema::create('student_scores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->string('subject');
                $table->enum('type', ['quiz', 'exam']);
                $table->integer('correct');
                $table->integer('total');
                $table->decimal('percent', 5, 2);
                $table->timestamps();

                $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('student_scores');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('classes');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
