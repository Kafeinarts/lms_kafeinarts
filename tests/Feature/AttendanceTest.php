<?php

use App\Models\Attendance;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function attendanceScenario(): array
{
    $teacher = User::factory()->guru()->create();
    $kelas = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $assignment = TeachingAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'mapel_id' => $mapel->id,
        'kelas_id' => $kelas->id,
    ]);
    $schedule = TeachingSchedule::factory()->create([
        'assignment_id' => $assignment->id,
        'day' => 'senin',
        'start_time' => '07:00',
        'end_time' => '09:00',
    ]);

    $students = User::factory()->count(2)->murid()->create([
        'kelas_id' => $kelas->id,
    ]);

    return compact('teacher', 'kelas', 'mapel', 'assignment', 'schedule', 'students');
}

test('guru can store attendance for their class schedule', function () {
    $scenario = attendanceScenario();

    $details = $scenario['students']->mapWithKeys(fn (User $student) => [
        $student->id => ['status' => 'H', 'reason' => null],
    ])->all();

    $details[$scenario['students']->first()->id] = [
        'status' => 'I',
        'reason' => 'Acara keluarga',
    ];

    $response = $this->actingAs($scenario['teacher'])->post(route('presensi.store'), [
        'schedule_id' => $scenario['schedule']->id,
        'date' => now()->toDateString(),
        'notes' => 'Pertemuan pertama',
        'details' => $details,
    ]);

    $attendance = Attendance::first();

    $response->assertRedirect(route('presensi.show', $attendance));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('attendances', [
        'schedule_id' => $scenario['schedule']->id,
        'teacher_id' => $scenario['teacher']->id,
    ]);

    expect($attendance->date->toDateString())->toBe(now()->toDateString());

    $this->assertDatabaseHas('attendance_details', [
        'attendance_id' => $attendance->id,
        'student_id' => $scenario['students']->first()->id,
        'status' => 'I',
        'reason' => 'Acara keluarga',
    ]);

    $this->assertDatabaseCount('attendance_details', 2);

    $this->actingAs($scenario['teacher'])
        ->get(route('presensi.show', $attendance))
        ->assertOk()
        ->assertSee('Detail Presensi');

    $this->actingAs($scenario['teacher'])
        ->get(route('presensi.create'))
        ->assertOk()
        ->assertSee($scenario['mapel']->name);

    $this->actingAs($scenario['teacher'])
        ->get(route('presensi.recap'))
        ->assertOk()
        ->assertSee('Rekap Presensi');
});

test('attendance cannot be stored twice for the same schedule and date', function () {
    $scenario = attendanceScenario();

    Attendance::create([
        'schedule_id' => $scenario['schedule']->id,
        'teacher_id' => $scenario['teacher']->id,
        'date' => now()->toDateString(),
        'notes' => null,
    ]);

    $details = $scenario['students']->mapWithKeys(fn (User $student) => [
        $student->id => ['status' => 'H', 'reason' => null],
    ])->all();

    $response = $this->actingAs($scenario['teacher'])->post(route('presensi.store'), [
        'schedule_id' => $scenario['schedule']->id,
        'date' => now()->toDateString(),
        'details' => $details,
    ]);

    $response->assertSessionHasErrors('date');
    $this->assertDatabaseCount('attendances', 1);
});

test('student sees own attendance history but cannot manage attendance', function () {
    $scenario = attendanceScenario();
    $student = $scenario['students']->first();

    $attendance = Attendance::create([
        'schedule_id' => $scenario['schedule']->id,
        'teacher_id' => $scenario['teacher']->id,
        'date' => now()->toDateString(),
        'notes' => null,
    ]);

    $attendance->details()->create([
        'student_id' => $student->id,
        'status' => 'S',
        'reason' => 'Demam',
    ]);

    $this->actingAs($student)
        ->get(route('presensi.index'))
        ->assertOk()
        ->assertSee('Presensi Saya')
        ->assertSee($scenario['mapel']->name);

    $this->actingAs($student)->get(route('presensi.recap'))->assertForbidden();
    $this->actingAs($student)->get(route('presensi.create'))->assertForbidden();
    $this->actingAs($student)->post(route('presensi.store'), [])->assertForbidden();
});

test('teacher cannot store attendance for a schedule they do not teach', function () {
    $scenario = attendanceScenario();
    $otherTeacher = User::factory()->guru()->create();

    $details = $scenario['students']->mapWithKeys(fn (User $student) => [
        $student->id => ['status' => 'H', 'reason' => null],
    ])->all();

    $this->actingAs($otherTeacher)->post(route('presensi.store'), [
        'schedule_id' => $scenario['schedule']->id,
        'date' => now()->toDateString(),
        'details' => $details,
    ])->assertForbidden();

    $this->assertDatabaseCount('attendances', 0);
});
