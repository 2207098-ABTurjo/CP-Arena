<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\CodeforcesController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout']);

// Profile
Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
Route::post('/profile', [AuthController::class, 'updateProfile']);

// Home (Dashboard)
Route::get('/home', [AuthController::class, 'home'])->name('home');
Route::get('/', function () {
    return redirect('/login');
});

// Problems
Route::get('/problems', [ProblemController::class, 'index'])->name('problems');
Route::get('/problems/{id}', [ProblemController::class, 'show'])->name('problems.show');

// Submissions
Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions');
Route::get('/submit/{problem_id}', [SubmissionController::class, 'create'])->name('submit.create');
Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');

// Recommendations
Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations');

// Codeforces Sync
Route::get('/sync-codeforces', [CodeforcesController::class, 'sync'])->name('cf.sync');