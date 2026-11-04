<?php

use App\Models\Achievement;
use App\Models\CounselingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guru can record counseling session and student achievement', function () {
    $guru = User::factory()->guru()->create();
    $student = User::factory()->murid()->create();

    $this->actingAs($guru)->post(route('bk.sessions.store'), [
        'student_id' => $student->id,
        'session_date' => now()->toDateString(),
        'topic' => 'Motivasi belajar',
        'notes' => 'Siswa kesulitan membagi waktu.',
        'follow_up' => 'Pemantauan mingguan.',
    ])->assertRedirect(route('bk.index'));

    $this->assertDatabaseHas('counseling_sessions', [
        'student_id' => $student->id,
        'counselor_id' => $guru->id,
        'topic' => 'Motivasi belajar',
    ]);

    $this->actingAs($guru)->post(route('bk.achievements.store'), [
        'student_id' => $student->id,
        'title' => 'Juara 1 Lomba Coding',
        'description' => 'Tingkat provinsi.',
        'points' => 25,
        'achieved_at' => now()->toDateString(),
    ])->assertRedirect(route('bk.index'));

    $this->assertDatabaseHas('achievements', [
        'student_id' => $student->id,
        'recorded_by' => $guru->id,
        'title' => 'Juara 1 Lomba Coding',
        'points' => 25,
    ]);

    expect(CounselingSession::count())->toBe(1)
        ->and(Achievement::count())->toBe(1);

    $this->actingAs($guru)
        ->get(route('bk.index'))
        ->assertOk()
        ->assertSee('Juara 1 Lomba Coding')
        ->assertSee('Motivasi belajar');

    $this->actingAs($guru)->get(route('bk.sessions.create'))->assertOk()->assertSee('Formulir Sesi Konseling');
    $this->actingAs($guru)->get(route('bk.achievements.create'))->assertOk()->assertSee('Formulir Prestasi');
});

test('student cannot access guidance counseling management', function () {
    $student = User::factory()->murid()->create();

    $this->actingAs($student)->get(route('bk.index'))->assertForbidden();
    $this->actingAs($student)->get(route('bk.sessions.create'))->assertForbidden();
    $this->actingAs($student)->post(route('bk.sessions.store'), [])->assertForbidden();
});

test('violation points are stored for the counseling recap', function () {
    $guru = User::factory()->guru()->create();
    $student = User::factory()->murid()->create();

    $this->actingAs($guru)->post(route('pelanggaran.store'), [
        'student_id' => $student->id,
        'description' => 'Terlambat tiga kali',
        'points' => 15,
        'room' => 'bk',
    ])->assertRedirect(route('pelanggaran.index'));

    $this->assertDatabaseHas('violations', [
        'student_id' => $student->id,
        'points' => 15,
    ]);
});
