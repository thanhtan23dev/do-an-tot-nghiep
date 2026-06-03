<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\NhanVienLoginController;
use App\Http\Controllers\NhanVien\DashboardController;
use App\Http\Controllers\NhanVien\JobController;
use App\Http\Controllers\NhanVien\ProfileController;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Voyager\VoyagerController;
use App\Http\Controllers\NhanVien\SalaryAdvanceController;
use App\Http\Controllers\Admin\ExportController;
//clear dữ liệu

// use Illuminate\Support\Facades\Artisan;
// use Database\Seeders\ClearTestDataSeeder;

// Route::get('/clear-test-data', function () {
//     try {
//         $seeder = new ClearTestDataSeeder();
//         $seeder->run();

//         return response()->json(['message' => 'Test data cleared successfully!']);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine()], 500);
//     }
// });

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('nhanvien.dashboard');
});
Route::get('/home', function () {
    return redirect()->route('nhanvien.dashboard');
});

Route::group(['prefix' => 'admin'], function () {
    Route::get('/api/ai-insights', [VoyagerController::class, 'fetchAiInsights'])->name('voyager.ai_insights');

    Voyager::routes();

    Route::get('/', [VoyagerController::class, 'index'])->name('voyager.dashboard')->middleware('admin.user');

    Route::get('/nhan-viens/{id}/export-payroll', [ExportController::class, 'exportPayroll'])->name('admin.nhanviens.export_payroll');
    Route::get('/monthly-payrolls/{id}/export-detail', [ExportController::class, 'exportDetailPayroll'])->name('admin.monthly_payrolls.export_detail');
    Route::put('/orders/{id}/complete', [VoyagerController::class, 'markAsCompleted'])->name('admin.orders.complete');
});

// 1. Nhóm Route chưa đăng nhập (Guest)
Route::middleware('guest:nhan_vien')->prefix('nhanvien')->group(function () {
    Route::get('/login', [NhanVienLoginController::class, 'showLoginForm'])->name('nhanvien.login');
    Route::post('/login', [NhanVienLoginController::class, 'login'])->name('nhanvien.login.post');
});

// 2. Nhóm Route bắt buộc phải đăng nhập (Auth Guard)
Route::middleware('auth:nhan_vien')->prefix('nhanvien')->group(function () {

    Route::post('/logout', [NhanVienLoginController::class, 'logout'])->name('nhanvien.logout');
    Route::post('/ho-so', [ProfileController::class, 'update'])->name('nhanvien.profile.update');
    Route::get('/ho-so', [ProfileController::class, 'edit'])->name('nhanvien.profile.edit');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('nhanvien.dashboard');

    Route::get('/cong-viec/{type}/{id}', [JobController::class, 'showJob'])->name('nhanvien.job.show');
    Route::get('/cong-viec-moi', [JobController::class, 'newJobs'])->name('nhanvien.jobs.new');
    Route::post('/cong-viec/{type}/{id}/accept', [JobController::class, 'acceptJob'])->name('nhanvien.job.accept');
    Route::post('/cong-viec/{type}/{id}/reject', [JobController::class, 'rejectJob'])->name('nhanvien.job.reject');
    Route::get('/cong-viec-dang-lam', [JobController::class, 'activeJobs'])->name('nhanvien.jobs.active');
    Route::post('/cong-viec/{type}/{id}/submit', [JobController::class, 'submitJob'])->name('nhanvien.job.submit');
    Route::get('/cong-viec-da-hoan-thanh', [JobController::class, 'completedJobs'])->name('nhanvien.jobs.completed');
    Route::post('/jobs/{type}/{id}/update-progress', [JobController::class, 'updateProgress'])->name('nhanvien.job.update_progress');
    //ứng lương
    Route::get('/ung-luong', [SalaryAdvanceController::class, 'create'])->name('nhanvien.salary_advance.create');
    Route::post('/ung-luong', [SalaryAdvanceController::class, 'store'])->name('nhanvien.salary_advance.store');
    Route::get('/phieu-luong', [SalaryAdvanceController::class, 'index'])->name('nhanvien.salary_advance.index');
    Route::get('/phieu-luong/{id}', [SalaryAdvanceController::class, 'show'])->name('nhanvien.salary_advance.show');
});
