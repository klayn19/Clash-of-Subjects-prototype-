<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ScoreController;

// Unity: fetch a question
Route::get('/get_question', [TeacherController::class, 'getQuestion']);

// Unity: save a score
Route::post('/save_score', [ScoreController::class, 'store']);