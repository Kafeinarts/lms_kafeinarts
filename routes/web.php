<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\MasterImportController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolProfileController;
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

// Public school profile (shareable, no login required)
Route::get('/profil-sekolah', [SchoolProfileController::class, 'publicProfile'])->name('sekolah.public');

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

    // Presensi: history for students, management for guru & superadmin
    Route::middleware('teacher')->group(function () {
        Route::get('/presensi/input', [
            AttendanceController::class,
            'create',
        ])->name('presensi.create');
        Route::post('/presensi', [
            AttendanceController::class,
            'store',
        ])->name('presensi.store');
        Route::get('/presensi/rekap', [
            AttendanceController::class,
            'recap',
        ])->name('presensi.recap');
        Route::get('/presensi/rekap/export', [
            AttendanceController::class,
            'exportRecap',
        ])->name('presensi.recap.export');
    });
    Route::get('/presensi', [AttendanceController::class, 'index'])->name(
        'presensi.index'
    );
    Route::get('/presensi/{attendance}', [
        AttendanceController::class,
        'show',
    ])->name('presensi.show');

    // Nilai & Rapor (Gradebook)
    Route::middleware('teacher')->group(function () {
        Route::get('/nilai/bobot', [
            GradebookController::class,
            'weights',
        ])->name('nilai.weights');
        Route::post('/nilai/bobot', [
            GradebookController::class,
            'storeWeights',
        ])->name('nilai.weights.store');
        Route::get('/nilai/input', [
            GradebookController::class,
            'input',
        ])->name('nilai.input');
        Route::post('/nilai/input', [
            GradebookController::class,
            'storeGrades',
        ])->name('nilai.input.store');
    });
    Route::middleware('superadmin')->group(function () {
        Route::get('/nilai/tahun-ajaran', [
            AcademicYearController::class,
            'index',
        ])->name('nilai.academic-years');
        Route::post('/nilai/tahun-ajaran', [
            AcademicYearController::class,
            'store',
        ])->name('nilai.academic-years.store');
        Route::patch('/nilai/tahun-ajaran/{academicYear}/aktifkan', [
            AcademicYearController::class,
            'activate',
        ])->name('nilai.academic-years.activate');
    });
    Route::get('/nilai/rapor/{student}/pdf', [
        GradebookController::class,
        'raporPdf',
    ])->name('nilai.rapor.pdf');
    Route::get('/nilai/rapor/{student?}', [
        GradebookController::class,
        'rapor',
    ])->name('nilai.rapor');
    Route::get('/nilai', [GradebookController::class, 'index'])->name(
        'nilai.index'
    );

    // Pengumuman (Announcements)
    Route::middleware('teacher')->group(function () {
        Route::get('/pengumuman/create', [
            AnnouncementController::class,
            'create',
        ])->name('pengumuman.create');
        Route::post('/pengumuman', [
            AnnouncementController::class,
            'store',
        ])->name('pengumuman.store');
        Route::get('/pengumuman/{announcement}/edit', [
            AnnouncementController::class,
            'edit',
        ])->name('pengumuman.edit');
        Route::put('/pengumuman/{announcement}', [
            AnnouncementController::class,
            'update',
        ])->name('pengumuman.update');
        Route::delete('/pengumuman/{announcement}', [
            AnnouncementController::class,
            'destroy',
        ])->name('pengumuman.destroy');
    });
    Route::get('/pengumuman', [
        AnnouncementController::class,
        'index',
    ])->name('pengumuman.index');
    Route::get('/pengumuman/{announcement}', [
        AnnouncementController::class,
        'show',
    ])->name('pengumuman.show');

    // Bimbingan Konseling: poin pelanggaran, prestasi, dan sesi konseling
    Route::middleware('teacher')->group(function () {
        Route::get('/bk', [BkController::class, 'index'])->name('bk.index');
        Route::get('/bk/konseling/create', [
            BkController::class,
            'createSession',
        ])->name('bk.sessions.create');
        Route::post('/bk/konseling', [
            BkController::class,
            'storeSession',
        ])->name('bk.sessions.store');
        Route::get('/bk/prestasi/create', [
            BkController::class,
            'createAchievement',
        ])->name('bk.achievements.create');
        Route::post('/bk/prestasi', [
            BkController::class,
            'storeAchievement',
        ])->name('bk.achievements.store');
    });

    // Forum diskusi per mata pelajaran
    Route::get('/forum', [DiscussionController::class, 'index'])->name(
        'forum.index'
    );
    Route::get('/forum/create', [
        DiscussionController::class,
        'create',
    ])->name('forum.create');
    Route::post('/forum', [DiscussionController::class, 'store'])->name(
        'forum.store'
    );
    Route::delete('/forum/reply/{reply}', [
        DiscussionController::class,
        'destroyReply',
    ])->name('forum.reply.destroy');
    Route::get('/forum/{topic}', [
        DiscussionController::class,
        'show',
    ])->name('forum.show');
    Route::post('/forum/{topic}/reply', [
        DiscussionController::class,
        'reply',
    ])->name('forum.reply');
    Route::delete('/forum/{topic}', [
        DiscussionController::class,
        'destroyTopic',
    ])->name('forum.destroy');

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

        // Identitas, alamat, dan kontak sekolah
        Route::get('/sekolah', [SchoolProfileController::class, 'edit'])->name('sekolah.edit');
        Route::put('/sekolah', [SchoolProfileController::class, 'update'])->name('sekolah.update');
        Route::delete('/sekolah/logo', [SchoolProfileController::class, 'removeLogo'])->name('sekolah.logo.destroy');

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
