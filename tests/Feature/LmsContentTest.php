<?php

use App\Models\Exam;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Material;
use App\Models\Question;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guru can view materi index page', function () {
    $guru = User::factory()->guru()->create();

    $response = $this->actingAs($guru)->get(route('materis.index'));

    $response->assertStatus(200);
    $response->assertSee('Materi Pembelajaran');
    $response->assertSee('Upload Materi Baru');
});

test('murid can view materi index but cannot create', function () {
    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->get(route('materis.index'));
    $response->assertStatus(200);
    $response->assertDontSee('Upload Materi Baru');

    $create = $this->actingAs($murid)->get(route('materis.create'));
    $create->assertStatus(403);
});

test('guru can create material with body', function () {
    $guru = User::factory()->guru()->create();

    $response = $this->actingAs($guru)->post(route('materis.store'), [
        'title' => 'Pengenalan Bilangan',
        'body' => 'Materi tentang bilangan bulat.',
        'is_published' => 1,
    ]);

    $response->assertRedirect(route('materis.index'));
    $this->assertDatabaseHas('materials', [
        'title' => 'Pengenalan Bilangan',
        'user_id' => $guru->id,
        'is_published' => true,
    ]);
});

test('guru can upload material with file attachment', function () {
    Storage::fake('public');
    $guru = User::factory()->guru()->create();

    $file = UploadedFile::fake()->create('modul.pdf', 500, 'application/pdf');

    $response = $this->actingAs($guru)->post(route('materis.store'), [
        'title' => 'Modul PDF',
        'body' => 'Isi modul.',
        'file' => $file,
        'is_published' => 1,
    ]);

    $response->assertRedirect(route('materis.index'));
    $material = Material::where('title', 'Modul PDF')->first();
    expect($material)->not->toBeNull();
    expect($material->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($material->file_path);
});

test('murid cannot create material', function () {
    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->post(route('materis.store'), [
        'title' => 'Hacked',
        'body' => 'Should not work',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('materials', ['title' => 'Hacked']);
});

test('superadmin can create material', function () {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->post(route('materis.store'), [
        'title' => 'Materi Superadmin',
        'body' => 'Dari superadmin.',
        'is_published' => 1,
    ]);

    $response->assertRedirect(route('materis.index'));
    $this->assertDatabaseHas('materials', ['title' => 'Materi Superadmin']);
});

test('guru can create exam with multiple choice questions', function () {
    $guru = User::factory()->guru()->create();

    $response = $this->actingAs($guru)->post(route('exams.store'), [
        'title' => 'Ujian Bab 1',
        'description' => 'Kerjakan dengan jujur',
        'type' => 'ujian',
        'duration_minutes' => 45,
        'questions' => [
            [
                'question' => '1 + 1 = ?',
                'option_a' => '1',
                'option_b' => '2',
                'option_c' => '3',
                'option_d' => '4',
                'correct_answer' => 'B',
            ],
            [
                'question' => '2 + 2 = ?',
                'option_a' => '2',
                'option_b' => '3',
                'option_c' => '4',
                'option_d' => '5',
                'correct_answer' => 'C',
            ],
        ],
    ]);

    $response->assertRedirect(route('exams.index'));

    $exam = Exam::where('title', 'Ujian Bab 1')->first();
    expect($exam)->not->toBeNull();
    expect($exam->questions)->toHaveCount(2);
    expect($exam->type)->toBe('ujian');
    $this->assertDatabaseHas('questions', [
        'exam_id' => $exam->id,
        'correct_answer' => 'B',
        'order' => 1,
    ]);
});

test('guru can create tugas with questions', function () {
    $guru = User::factory()->guru()->create();

    $response = $this->actingAs($guru)->post(route('exams.store'), [
        'title' => 'Tugas Minggu 1',
        'type' => 'tugas',
        'questions' => [
            [
                'question' => 'Warna langit?',
                'option_a' => 'Merah',
                'option_b' => 'Biru',
                'option_c' => 'Hijau',
                'option_d' => 'Kuning',
                'correct_answer' => 'B',
            ],
        ],
    ]);

    $response->assertRedirect(route('exams.index'));
    $this->assertDatabaseHas('exams', [
        'title' => 'Tugas Minggu 1',
        'type' => 'tugas',
    ]);
});

test('murid cannot create exam', function () {
    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->post(route('exams.store'), [
        'title' => 'Fake Exam',
        'type' => 'tugas',
        'questions' => [
            [
                'question' => 'Q?',
                'option_a' => 'A',
                'option_b' => 'B',
                'option_c' => 'C',
                'option_d' => 'D',
                'correct_answer' => 'A',
            ],
        ],
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('exams', ['title' => 'Fake Exam']);
});

test('exam requires at least one question', function () {
    $guru = User::factory()->guru()->create();

    $response = $this->from(route('exams.create'))->actingAs($guru)->post(route('exams.store'), [
        'title' => 'Empty Exam',
        'type' => 'tugas',
        'questions' => [],
    ]);

    $response->assertSessionHasErrors('questions');
});

test('any authenticated user can view material detail', function () {
    $material = Material::factory()->create();
    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->get(route('materis.show', $material));

    $response->assertStatus(200);
    $response->assertSee($material->title);
});

test('any authenticated user can view exam detail', function () {
    $exam = Exam::factory()->create();
    Question::factory()->create(['exam_id' => $exam->id]);
    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->get(route('exams.show', $exam));

    $response->assertStatus(200);
    $response->assertSee($exam->title);
});

test('guru can update exam questions', function () {
    $guru = User::factory()->guru()->create();
    $exam = Exam::factory()->tugas()->create(['user_id' => $guru->id]);
    Question::factory()->create(['exam_id' => $exam->id]);

    $response = $this->actingAs($guru)->put(route('exams.update', $exam), [
        'title' => 'Tugas Updated',
        'type' => 'tugas',
        'questions' => [
            [
                'question' => 'Soal baru?',
                'option_a' => 'A1',
                'option_b' => 'B1',
                'option_c' => 'C1',
                'option_d' => 'D1',
                'correct_answer' => 'D',
            ],
        ],
    ]);

    $response->assertRedirect(route('exams.index'));
    $exam->refresh();
    expect($exam->title)->toBe('Tugas Updated');
    expect($exam->questions)->toHaveCount(1);
    expect($exam->questions->first()->question)->toBe('Soal baru?');
});

test('murid can answer multiple choice questions in the exam simulation', function () {
    $exam = Exam::factory()->tugas()->create();
    Question::factory()->create(['exam_id' => $exam->id]);
    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->get(route('exams.show', $exam));

    $response->assertStatus(200);
    $response->assertSee('quiz-option', false);
    $response->assertSee('Selesai &amp; Lihat Hasil', false);
});

test('materials can be filtered by mapel and sorted by teacher', function () {
    $guruFrontend = User::factory()->guru()->create(['name' => 'Alfarobby']);
    $guruBackend = User::factory()->guru()->create(['name' => 'Hanif']);
    $frontend = Mapel::factory()->create(['name' => 'Front-end']);
    $backend = Mapel::factory()->create(['name' => 'Back-end']);

    Material::factory()->create(['title' => 'Materi Front-end', 'user_id' => $guruFrontend->id, 'mapel_id' => $frontend->id]);
    Material::factory()->create(['title' => 'Materi Back-end', 'user_id' => $guruBackend->id, 'mapel_id' => $backend->id]);

    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get(route('materis.index', [
        'mapel' => $frontend->id,
        'sort' => 'teacher',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Materi Front-end');
    $response->assertDontSee('Materi Back-end');
    $response->assertSee('Semua Mapel (Show All)');
});

test('materi index shows mata pelajaran cards containing their materials', function () {
    $guru = User::factory()->guru()->create();
    $mapel = Mapel::factory()->create(['name' => 'Matematika']);
    Material::factory()->create([
        'title' => 'Aljabar Dasar',
        'user_id' => $guru->id,
        'mapel_id' => $mapel->id,
    ]);

    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get(route('materis.index'));

    $response->assertStatus(200);
    $response->assertSee('1 mata pelajaran');
    $response->assertSee('Matematika');
    $response->assertSee('Aljabar Dasar');
    $response->assertSee('Lihat semua (1)');
});

test('exams index provides tugas and ujian tabs with counts', function () {
    Exam::factory()->tugas()->create();
    Exam::factory()->ujian()->create();
    Exam::factory()->ujian()->create();

    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get(route('exams.index'));

    $response->assertStatus(200);
    $response->assertSee('exam-tabs', false);
    $response->assertSee('Tugas');
    $response->assertSee('Ujian');
    $response->assertSee('<span class="exam-tab-count">1</span>', false);
    $response->assertSee('<span class="exam-tab-count">2</span>', false);
});

test('jadwal page provides a weekly calendar with teacher, mapel, and time', function () {
    $assignment = TeachingAssignment::factory()->create();
    TeachingSchedule::factory()->create([
        'assignment_id' => $assignment->id,
        'day' => 'senin',
        'start_time' => '07:00',
        'end_time' => '08:30',
    ]);

    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get(route('jadwal.index'));

    $response->assertStatus(200);
    $response->assertSee('Kalender Mingguan');
    $response->assertSee($assignment->teacher->name);
    $response->assertSee($assignment->mapel->name);
});

test('penugasan page groups repeated teacher and mapel rows', function () {
    $teacher = User::factory()->guru()->create(['name' => 'Alfarobby']);
    $mapel = Mapel::factory()->create(['name' => 'Front-end']);
    $kelasSatu = Kelas::factory()->create(['name' => 'X RPL 1']);
    $kelasDua = Kelas::factory()->create(['name' => 'XI RPL 1']);

    TeachingAssignment::factory()->create(['teacher_id' => $teacher->id, 'mapel_id' => $mapel->id, 'kelas_id' => $kelasSatu->id]);
    TeachingAssignment::factory()->create(['teacher_id' => $teacher->id, 'mapel_id' => $mapel->id, 'kelas_id' => $kelasDua->id]);

    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get(route('penugasan.index'));

    $response->assertStatus(200);
    $response->assertSee('Alfarobby');
    $response->assertSee('rowspan="2"', false);
});

test('guru can delete material to the history bin', function () {
    Storage::fake('public');
    $guru = User::factory()->guru()->create();
    $material = Material::factory()->create(['user_id' => $guru->id]);

    $response = $this->actingAs($guru)->delete(route('materis.destroy', $material));

    $response->assertRedirect(route('materis.index'));

    expect(Material::find($material->id))->toBeNull();
    expect(Material::withTrashed()->find($material->id))->not->toBeNull();
    expect(Material::onlyTrashed()->count())->toBe(1);
});
