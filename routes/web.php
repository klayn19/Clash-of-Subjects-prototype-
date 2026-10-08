<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;

// ─── AUTH ──────────────────────────────────────────────────────────
Route::get('/',      [AuthController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'index'])->name('login.page');

Route::post('/login',           [AuthController::class, 'login'])->name('login');
Route::post('/register',        [AuthController::class, 'register'])->name('register');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgotPassword');
Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->name('resetPassword');
Route::get('/logout',           [AuthController::class, 'logout'])->name('logout');

// ─── GAMEPLAY ──────────────────────────────────────────────────────
Route::get('/gameplay', function () {
    if (!session('user_id')) return redirect('/');
    return view('gameplay');
})->name('gameplay');

// ─── STUDENT DASHBOARD ─────────────────────────────────────────────
Route::get('/student/dashboard', [\App\Http\Controllers\StudentController::class, 'index'])->name('student.dashboard');
Route::get('/student/general-knowledge', [\App\Http\Controllers\GeneralKnowledgeController::class, 'index'])->name('general.knowledge.play');

// ─── ADMIN DASHBOARD ───────────────────────────────────────────────
Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

// ─── ADMIN AJAX ENDPOINTS ──────────────────────────────────────────
Route::get('/backend/admin/get_student_grades/{id}', [AdminController::class, 'getStudentGrades']);
Route::post('/backend/admin/update_student',         [AdminController::class, 'updateStudent']);
Route::post('/backend/admin/register_teacher',       [AdminController::class, 'registerTeacher'])->name('admin.register_teacher');
Route::post('/backend/admin/add_section',            [AdminController::class, 'addSection'])->name('admin.add_section');
Route::post('/backend/admin/update_section',         [AdminController::class, 'updateSection'])->name('admin.update_section');
Route::post('/backend/admin/delete_section',         [AdminController::class, 'deleteSection'])->name('admin.delete_section');

// ─── TEACHER DASHBOARD ─────────────────────────────────────────────
Route::get('/teacher/dashboard', [TeacherController::class, 'index'])->name('teacher.dashboard');

// ─── TEACHER AJAX ENDPOINTS ────────────────────────────────────────
Route::post('/backend/save_class.php',         [TeacherController::class, 'saveClass']);
Route::post('/backend/delete_class.php',       [TeacherController::class, 'deleteClass']);
Route::post('/backend/save_questions.php',     [TeacherController::class, 'saveQuestions']);
Route::post('/backend/save_student_note.php',  [TeacherController::class, 'saveStudentNote']);
Route::get('/backend/get_students.php',        [TeacherController::class, 'getStudents']);
Route::get('/backend/get_student_scores.php',  [TeacherController::class, 'getStudentScores']);
Route::post('/backend/save_prototype_grade.php', [TeacherController::class, 'savePrototypeGrade']);
Route::get('/backend/get_student_analytics.php',  [TeacherController::class, 'getStudentAnalytics']);
Route::post('/backend/enroll_student.php',     [TeacherController::class, 'enrollStudent']);
Route::post('/backend/unenroll_student.php',   [TeacherController::class, 'unenrollStudent']);
Route::get('/backend/get_class_students.php',  [TeacherController::class, 'getClassStudents']);

// ─── UNITY GAME API ────────────────────────────────────────────────
// Called by Unity's QuizManager to fetch a question from the DB.
// GET /api/get_question?class_id=3&subject=english&type=quiz&quarter=1&answered=1,2,5
Route::any('/api/get_question', [TeacherController::class, 'getQuestion'])->name('api.getQuestion');
Route::any('/backend/get_question.php', [TeacherController::class, 'getQuestion']);

// Called by Unity's GK Arena (AI Mode) to fetch one AI-style question.
// GET /backend/generate_ai_question.php?subject=math&grade_level=Grade+8&difficulty=5&answered=1,2,3
Route::any('/backend/generate_ai_question.php', [\App\Http\Controllers\GeneralKnowledgeController::class, 'generateAiQuestion']);
Route::any('/api/generate_ai_question',         [\App\Http\Controllers\GeneralKnowledgeController::class, 'generateAiQuestion']);
