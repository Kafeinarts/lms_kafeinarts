<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\MasterImportController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\TeachingScheduleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViolationController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (if authenticated) or login (if guest)
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::get('reload-captcha', [
        AuthController::class,
        'reloadCaptcha',
    ])->name('captcha.reload');

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'login'])->name('login');
        Route::post('login', [AuthController::class, 'authenticate']);
        Route::get('register', [AuthController::class, 'register'])->name(
            'register'
        );
        Route::post('register', [AuthController::class, 'store']);
    });

    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout')
        ->middleware('auth');
});

// Protected routes (authenticated users)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name(
        'dashboard'
    );

    // Profile & Password & Avatar
    Route::get('/profile', [ProfileController::class, 'edit'])->name(
        'profile.edit'
    );
    Route::put('/profile', [ProfileController::class, 'update'])->name(
        'profile.update'
    );
    Route::put('/profile/password', [
        ProfileController::class,
        'updatePassword',
    ])->name('profile.password');
    Route::delete('/profile/avatar', [
        ProfileController::class,
        'removeAvatar',
    ])->name('profile.avatar.destroy');

    // Role simulation preview for Super Admin (read-only look & feel)
    Route::post('/profile/simulate', [
        ProfileController::class,
        'startSimulation',
    ])->name('profile.simulate.start');
    Route::delete('/profile/simulate', [
        ProfileController::class,
        'stopSimulation',
    ])->name('profile.simulate.stop');

    // Laporan bug & permintaan fitur (semua pengguna, dikirim via WhatsApp)
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');

    // Notifications (all authenticated users)
    Route::get('/notifications', [NotificationController::class, 'index'])->name(
        'notifications.index'
    );
    Route::patch('/notifications/read-all', [
        NotificationController::class,
        'markAllRead',
    ])->name('notifications.readAll');
    Route::patch('/notifications/{notification}/read', [
        NotificationController::class,
        'markRead',
    ])->name('notifications.read');

    // Jadwal pengajaran & penugasan: viewing for all, management for superadmin
    Route::get('/jadwal', [TeachingScheduleController::class, 'index'])->name(
        'jadwal.index'
    );
    Route::get('/penugasan', [
        TeachingAssignmentController::class,
        'index',
    ])->name('penugasan.index');

    // LMS: Materi (blog) & Tugas/Ujian — viewing for all, management for guru & superadmin
    Route::get('/materis', [MateriController::class, 'index'])->name(
        'materis.index'
    );
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');

    Route::middleware('teacher')->group(function () {
        Route::get('/materis/create', [
            MateriController::class,
            'create',
        ])->name('materis.create');
        Route::post('/materis/images/upload', [
            MateriController::class,
            'uploadImage',
        ])->name('materis.images.upload');
        Route::post('/materis', [MateriController::class, 'store'])->name(
            'materis.store',
        );
        Route::get('/materis/{materi}/edit', [
            MateriController::class,
            'edit',
        ])->name('materis.edit');
        Route::put('/materis/{materi}', [
            MateriController::class,
            'update',
        ])->name('materis.update');
        Route::delete('/materis/{materi}', [
            MateriController::class,
            'destroy',
        ])->name('materis.destroy');

        // History Bin: soft-deleted materials (restore or permanent delete)
        Route::get('/materis/bin', [
            MateriController::class,
            'bin',
        ])->name('materis.bin');
        Route::post('/materis/{materi}/restore', [
            MateriController::class,
            'restore',
        ])->withTrashed()->name('materis.restore');
        Route::delete('/materis/{materi}/force', [
            MateriController::class,
            'forceDelete',
        ])->withTrashed()->name('materis.force');

        Route::get('/exams/create', [ExamController::class, 'create'])->name(
            'exams.create'
        );
        Route::post('/exams', [ExamController::class, 'store'])->name(
            'exams.store'
        );
        Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name(
            'exams.edit'
        );
        Route::put('/exams/{exam}', [ExamController::class, 'update'])->name(
            'exams.update'
        );
        Route::delete('/exams/{exam}', [
            ExamController::class,
            'destroy',
        ])->name('exams.destroy');

        // Pelanggaran & pemanggilan siswa ke ruang BK/ruang guru
        Route::get('/pelanggaran', [ViolationController::class, 'index'])->name(
            'pelanggaran.index'
        );
        Route::get('/pelanggaran/create', [
            ViolationController::class,
            'create',
        ])->name('pelanggaran.create');
        Route::post('/pelanggaran', [
            ViolationController::class,
            'store',
        ])->name('pelanggaran.store');
    });

    Route::get('/materis/{materi}', [MateriController::class, 'show'])->name(
        'materis.show'
    );
    Route::get('/exams/{exam}', [ExamController::class, 'show'])->name(
        'exams.show'
    );
    Route::post('/exams/{exam}/submit', [
        ExamController::class,
        'submit',
    ])->name('exams.submit');

    // Super Admin Only: User Management, Master Data & Import
    Route::middleware('superadmin')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.index');

        Route::resource('users', UserController::class)->except(['show']);

        // Master data
        Route::resource('kelas', KelasController::class)->except(['show'])->parameters([
            'kelas' => 'kelas',
        ]);
        Route::resource('mapel', MapelController::class)->except(['show']);
        Route::resource('siswa', StudentController::class)->except(['show']);
        Route::resource('guru', TeacherController::class)->except(['show']);
        Route::resource('wali', GuardianController::class)->except(['show']);
        Route::resource('penugasan', TeachingAssignmentController::class)->except([
            'show',
            'index',
        ]);
        Route::resource('jadwal', TeachingScheduleController::class)->except([
            'show',
            'index',
        ]);

        // Import master data from Excel/CSV + template download
        Route::get('/master/{type}/template', [
            MasterImportController::class,
            'template',
        ])->name('master.template');
        Route::post('/master/{type}/import', [
            MasterImportController::class,
            'import',
        ])->name('master.import');
    });
});
