<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\LogActivityController;
use App\Http\Controllers\TrainingCenterController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth.token')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================================
    // V2 API (NEW RULE-BASED SCORING)
    // ==========================================
    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'store']);

    // Questionnaire
    Route::get('/questionnaire', [QuestionnaireController::class, 'show']);
    Route::post('/questionnaire', [QuestionnaireController::class, 'store']);

    // Training
    Route::get('/trainings', [\App\Http\Controllers\PelatihanController::class, 'index']);
    Route::post('/trainings', [\App\Http\Controllers\PelatihanController::class, 'store']);
    Route::get('/trainings/{pelatihan}', [\App\Http\Controllers\PelatihanController::class, 'show']);
    Route::put('/trainings/{pelatihan}', [\App\Http\Controllers\PelatihanController::class, 'update']);
    Route::delete('/trainings/{pelatihan}', [\App\Http\Controllers\PelatihanController::class, 'destroy']);

    // Training Center
    Route::apiResource('/training-centers', TrainingCenterController::class);

    
    // Trending Trainings (User Dashboard)
    Route::get('/trending-trainings', [\App\Http\Controllers\PelatihanController::class, 'trending']);

    // Recommendation (Hanya baca hasil dari tabel Recommendation)
    Route::get('/recommendations', [RecommendationController::class, 'index']);

    // Enrollments User V2
    Route::get('/enrollments', [\App\Http\Controllers\EnrollmentController::class, 'index']);
    Route::post('/enrollments', [\App\Http\Controllers\EnrollmentController::class, 'store']);

    // Log Activity
    Route::get('/admin/log-activities', [LogActivityController::class, 'index']);
    Route::delete('/admin/log-activities/{id}', [LogActivityController::class, 'destroy']);
    Route::post('/log-activity', [LogActivityController::class, 'store']);

    // Admin Enrollments
    Route::get('/admin/enrollments', [EnrollmentController::class, 'index']);
    Route::patch('/admin/enrollments/{id}/status', [EnrollmentController::class, 'updateStatus']);

    // Admin Users
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::patch('/admin/users/{id}/status', [UserController::class, 'updateStatus']);

    // Admin Stats
    Route::get('/admin/stats', [\App\Http\Controllers\Admin\StatsController::class, 'index']);
});