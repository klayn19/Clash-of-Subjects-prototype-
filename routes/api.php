<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\GeneralKnowledgeController;

// Unity: fetch a question
Route::get('/get_question', [TeacherController::class, 'getQuestion']);

// Unity: save a score
Route::post('/save_score', [ScoreController::class, 'store']);

// Get student high score
Route::get('/student_high_score', [ScoreController::class, 'getHighScore']);

// General Knowledge: fetch 15 questions
Route::get('/general-knowledge/questions', [GeneralKnowledgeController::class, 'getQuestions']);