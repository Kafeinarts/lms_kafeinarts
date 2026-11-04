# Product Requirement Document (PRD)
## Fitur Tambahan Module LMS - MYSCHOOL

* **Status:** Draft / Proposed  
* **Tech Stack Target:** Laravel (Blade / Inertia / Vue / Livewire), MySQL/PostgreSQL  
* **Penulis:** Product Owner / Lead Developer  

---

## 1. Executive Summary & Context

Aplikasi **MYSCHOOL LMS** saat ini telah memiliki modul-modul utama seperti Materi, Tugas & Ujian, Jadwal Mengajar, Kelas Diajar, Pelanggaran Siswa, serta Administrasi Sistem (Master Data, Activity Log). 

Dokumen ini disusun untuk mendefinisikan kebutuhan pengembangan fitur-fitur lanjutan guna melengkapi ekosistem akademik dan kesiswaan berbasis **Laravel Framework**.

---

## 2. Rekapitulasi Module & Prioritas Fitur

| Module / Fitur | Prioritas | Target Pengguna | Deskripsi Ringkas |
| :--- | :--- | :--- | :--- |
| **Presensi & Absensi** | `P0` (Critical) | Guru, Siswa, Admin | Pencatatan kehadiran harian/per jam pelajaran + rekap otomatis |
| **Nilai & Rapor (Gradebook)** | `P0` (Critical) | Guru, Siswa, Wali | Penginputan nilai tugas/ujian dan kalkulasi nilai akhir/rapor |
| **Pengumuman (Announcements)** | `P1` (High) | Admin, Guru, Siswa | Broadcast berita/informasi sekolah dengan notifikasi |
| **Bimbingan Konseling (BK)** | `P1` (High) | Guru BK, Admin | Pencatatan poin pelanggaran, prestasi, dan sesi konseling |
| **Forum Diskusi** | `P2` (Medium) | Guru, Siswa | Ruang diskusi asynchronous per mata pelajaran |

---

## 3. Spesifikasi Detail Fitur (P0 - Prioritas Utama)

### Fitur A: Module Presensi (Absensi Siswa & Guru)

#### 1. User Stories
* **Sebagai Guru:** Saya ingin mencatat kehadiran siswa di kelas yang saya ajar agar data presensi terorganisir per pertemuan.
* **Sebagai Admin:** Saya ingin melihat rekap presensi bulanan/semesteran untuk evaluasi tingkat kedisiplinan.
* **Sebagai Siswa/Wali:** Saya ingin melihat riwayat kehadiran saya/anak saya secara *real-time*.

#### 2. Functional Requirements & Workflow
1. Guru memilih **Kelas Diajar** dan **Jadwal Hari Ini**.
2. Sistem menampilkan daftar siswa dengan opsi status: `Hadir (H)`, `Izin (I)`, `Sakit (S)`, `Tanpa Keterangan / Alfa (A)`.
3. Guru memasukkan catatan/keterangan (opsional) dan memilih tombol **Simpan Presensi**.
4. Sistem memblokir penginputan ganda pada slot jam pelajaran dan jadwal yang sama.

#### 3. Kebutuhan Skema Database (Laravel Migration Concept)

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Table: attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table: attendance_details
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['H', 'I', 'S', 'A'])->default('H');
            $table->string('reason')->nullable(); // Keterangan jika izin/sakit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
        Schema::dropIfExists('attendances');
    }
};
```

---

### Fitur B: Module Nilai & Rapor (Gradebook)

#### 1. User Stories
* **Sebagai Guru:** Saya ingin menginput bobot penilaian (Tugas 30%, UTS 30%, UAS 40%) dan memasukkan nilai harian siswa.
* **Sebagai Admin/Wali Kelas:** Saya ingin meng-generate Rapor Digital siswa secara otomatis berdasarkan akumulasi nilai akhir.

#### 2. Functional Requirements & Workflow
1. **Konfigurasi Bobot:** Guru/Admin menentukan bobot penilaian per mata pelajaran.
2. **Input Nilai:** Mengambil data nilai dari module `Tugas & Ujian` yang sudah ada, lalu mengizinkan input nilai komponen kognitif/psikomotorik tambahan.
3. **Kalkulasi Otomatis:** Sistem menghitung:  
   $$\text{Nilai Akhir} = (\text{Nilai Tugas} \times \text{Bobot Tugas}) + (\text{Nilai UTS} \times \text{Bobot UTS}) + (\text{Nilai UAS} \times \text{Bobot UAS})$$
4. **Export Rapor:** Pilihan eksport PDF Rapor Per Semester menggunakan library PDF Laravel.

#### 3. Kebutuhan Skema Database (Laravel Migration Concept)

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Table: grade_weights
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->integer('assignment_weight')->default(30);
            $table->integer('uts_weight')->default(30);
            $table->integer('uas_weight')->default(40);
            $table->timestamps();
        });

        // Table: final_grades
        Schema::create('final_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('final_score', 5, 2);
            $table->char('grade_letter', 2); // A, B, C, D, F
            $table->text('teacher_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_grades');
        Schema::dropIfExists('grade_weights');
    }
};
```

---

## 4. Arsitektur & Pertimbangan Teknikal Laravel

1. **Authorization & Policies (Laravel Gates/Spatie):**
   * Gunakan `Role/Permission` (misal: `super-admin`, `guru`, `siswa`, `wali-murid`).
   * Akses *read-only* dipastikan dengan policy, contoh: `Siswa` hanya bisa melihat data nilai dan presensi milik `user_id` mereka sendiri.
2. **Background Processing (Jobs & Queues):**
   * Pengiriman pengumuman massal atau kalkulasi massal Rapor akhir semester harus dialihkan ke **Laravel Queues** (`php artisan queue:work`) untuk menghindari request timeout.
3. **Export Laporan:**
   * `Maatwebsite/Laravel-Excel` untuk eksport rekap presensi dan rekap nilai dalam format `.xlsx`.
   * `barryvdh/laravel-dompdf` atau `Spatie/Browsershot` untuk cetak PDF Rapor dan Slip Pelanggaran Siswa.
4. **Navigasi Sidebar UI:**
   * Kelompokkan menu sidebar dengan struktur *collapsible/accordion*:
     * **Akademik & LMS:** Materi, Tugas & Ujian, Presensi, Nilai & Rapor, Jadwal Mengajar, Kelas Diajar
     * **Kesiswaan:** Pelanggaran Siswa, Bimbingan Konseling (BK)
     * **Administrasi:** Kelola Akun, Master Data, Activity Log

---

## 5. Rencana Implementasi & Sprint

* **Sprint 1 (Minggu 1):** Development Module Presensi (Migration, Controller, Service Layer, UI Blade/Vue, Testing).
* **Sprint 2 (Minggu 2):** Development Module Nilai & Rapor (Gradebook, Logic Kalkulasi, Export PDF).
* **Sprint 3 (Minggu 3):** Integration Module BK & Pengumuman, Refactoring Sidebar Navigation, & Authorization Testing.