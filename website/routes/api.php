<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SalaryAdvanceController;
use App\Http\Controllers\Api\ArticleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Các API công khai (Public)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/account/delete', [AuthController::class, 'deleteAccount']);
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);

    // NHÓM API: HỒ SƠ CÁ NHÂN
    Route::put('/profile', [ProfileController::class, 'update']);

    // MÀN HÌNH CHÍNH 
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // NHÓM API: QUẢN LÝ CÔNG VIỆC
    // 1. Danh sách công việc
    Route::get('/jobs/new', [JobController::class, 'newJobs']);
    Route::get('/jobs/active', [JobController::class, 'activeJobs']);
    Route::get('/jobs/completed', [JobController::class, 'completedJobs']);
    Route::get('/jobs/{type}/{id}', [JobController::class, 'showJob']);
    // 2. Tương tác với công việc
    Route::post('/jobs/{type}/{id}/accept', [JobController::class, 'acceptJob']);
    Route::post('/jobs/{type}/{id}/reject', [JobController::class, 'rejectJob']);
    Route::post('/jobs/{type}/{id}/submit', [JobController::class, 'submitJob']);
    Route::post('/jobs/{type}/{id}/update-progress', [JobController::class, 'updateProgress']);

    // NHÓM API: ỨNG LƯƠNG & PHIẾU LƯƠNG (SalaryAdvanceController)
    Route::get('/salary-advance', [SalaryAdvanceController::class, 'index']);
    Route::get('/salary-advance/info', [SalaryAdvanceController::class, 'create']);
    Route::post('/salary-advance', [SalaryAdvanceController::class, 'store']);
    Route::get('/salary-advance/{id}', [SalaryAdvanceController::class, 'show']);

});
    // Nhóm api: bài viết
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
