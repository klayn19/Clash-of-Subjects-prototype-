<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\GeneralKnowledgeController;

// Unity: save a score
Route::post('/save_score', [ScoreController::class, 'store'])->middleware('web');
Route::post('/multiplayer/victory', [ScoreController::class, 'recordMultiplayerWin'])->middleware('web');

// Get student high score
Route::get('/student_high_score', [ScoreController::class, 'getHighScore']);

// General Knowledge: fetch 15 questions
Route::get('/general-knowledge/questions', [GeneralKnowledgeController::class, 'getQuestions']);