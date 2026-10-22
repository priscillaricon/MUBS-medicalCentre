<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DoctorManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Doctor\CheckInController;
use App\Http\Controllers\Doctor\ConsultationController;
use App\Http\Controllers\Doctor\DoctorPortalController;
use App\Http\Controllers\Doctor\SickSheetController;
use App\Http\Controllers\Student\AppointmentController;
use App\Http\Controllers\Student\StudentPortalController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MUBS Medical Centre - web routes (Stage 4: student, doctor and admin are ALL real)
|--------------------------------------------------------------------------
*/

$role = fn (string ...$roles) => RoleMiddleware::class . ':' . implode(',', $roles);

/* ---------------- Public: login + register ---------------- */
Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->dashboardRoute())
    : view('index'))->name('login');

Route::get('/register', fn () => auth()->check()
    ? redirect()->route(auth()->user()->dashboardRoute())
    : view('register'))->name('register');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.attempt');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/* ---------------- Student only ---------------- */
Route::middleware(['auth', $role('student')])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/medical-records', [StudentPortalController::class, 'records'])->name('student.records');
    Route::get('/id-card', [StudentPortalController::class, 'idCard'])->name('student.idcard');
    Route::get('/sick-sheets/{sickSheet}', [StudentPortalController::class, 'sickSheet'])->name('student.sicksheet');

    Route::get('/book-appointment', [AppointmentController::class, 'create'])->name('student.book');
    Route::get('/doctors/{doctor}/slots', [AppointmentController::class, 'slots'])->name('student.slots');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
});

/* ---------------- Doctor AND admin (front-desk check-in) ---------------- */
Route::middleware(['auth', $role('doctor', 'admin')])->group(function () {
    Route::get('/doctor/checkin', [CheckInController::class, 'index'])->name('doctor.checkin');
});

/* ---------------- Doctor only ---------------- */
Route::middleware(['auth', $role('doctor')])->prefix('doctor')->group(function () {
    Route::get('/dashboard', [DoctorPortalController::class, 'dashboard'])->name('doctor.dashboard');
    Route::post('/appointments/{appointment}/respond', [DoctorPortalController::class, 'respond'])->name('doctor.respond');

    Route::get('/appointments/{appointment}/consultation', [ConsultationController::class, 'show'])->name('doctor.consultation');
    Route::post('/appointments/{appointment}/consultation', [ConsultationController::class, 'store'])->name('consultations.store');

    Route::get('/appointments/{appointment}/sick-sheet', [SickSheetController::class, 'show'])->name('doctor.sicksheet');
    Route::post('/appointments/{appointment}/sick-sheet', [SickSheetController::class, 'store'])->name('sicksheets.store');
});

/* ---------------- Admin only ---------------- */
Route::middleware(['auth', $role('admin')])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/manage-doctors', [DoctorManagementController::class, 'index'])->name('admin.doctors');
    Route::post('/doctors', [DoctorManagementController::class, 'store'])->name('admin.doctors.store');
    Route::post('/doctors/{doctor}/toggle', [DoctorManagementController::class, 'toggle'])->name('admin.doctors.toggle');

    Route::get('/manage-schedules', [ScheduleController::class, 'index'])->name('admin.schedules');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/reports/export', [ReportController::class, 'csv'])->name('admin.reports.csv');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
});
