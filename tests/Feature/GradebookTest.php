<?php

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function gradebookScenario(): array
{
    $teacher = User::factory()->guru()->create();
    $kelas = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $student = User::factory()->murid()->create(['kelas_id' => $kelas->id]);
    $academicYear = AcademicYear::factory()->active()->create();

    TeachingAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'mapel_id' => $mapel->id,
        'kelas_id' => $kelas->id,
    ]);

    $exam = Exam::factory()->tugas()->create([
        'mapel_id' => $mapel->id,
        'user_id' => $teacher->id,
    ]);

    ExamSubmission::factory()->create([
        'exam_id' => $exam->id,
        'user_id' => $student->id,
        'score' => 80,
    ]);

    return compact('teacher', 'kelas', 'mapel', 'student', 'academicYear');
}

test('guru can configure weights and final grades are calculated automatically', function () {
    $scenario = gradebookScenario();

    $this->actingAs($scenario['teacher'])->post(route('nilai.weights.store'), [
        'mapel_id' => $scenario['mapel']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'assignment_weight' => 30,
        'uts_weight' => 30,
        'uas_weight' => 40,
    ])->assertRedirect(route('nilai.index'));

    $this->assertDatabaseHas('grade_weights', [
        'mapel_id' => $scenario['mapel']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'assignment_weight' => 30,
        'uts_weight' => 30,
        'uas_weight' => 40,
    ]);

    $this->actingAs($scenario['teacher'])->post(route('nilai.input.store'), [
        'mapel_id' => $scenario['mapel']->id,
        'kelas_id' => $scenario['kelas']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'scores' => [
            $scenario['student']->id => [
                'uts' => 90,
                'uas' => 100,
                'notes' => 'Konsisten',
            ],
        ],
    ])->assertRedirect();

    // Nilai tugas 80, UTS 90, UAS 100 dengan bobot 30/30/40 menghasilkan 91.
    $this->assertDatabaseHas('grade_components', [
        'student_id' => $scenario['student']->id,
        'component' => 'uts',
        'score' => 90,
    ]);

    $this->assertDatabaseHas('final_grades', [
        'student_id' => $scenario['student']->id,
        'mapel_id' => $scenario['mapel']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'final_score' => 91,
        'grade_letter' => 'A',
    ]);

    $this->actingAs($scenario['teacher'])->get(route('nilai.index'))->assertOk()->assertSee($scenario['mapel']->name);

    $this->actingAs($scenario['teacher'])
        ->get(route('nilai.weights', [
            'mapel' => $scenario['mapel']->id,
            'academic_year' => $scenario['academicYear']->id,
        ]))
        ->assertOk()
        ->assertSee('Konfigurasi Bobot');

    $this->actingAs($scenario['teacher'])
        ->get(route('nilai.input', [
            'mapel' => $scenario['mapel']->id,
            'kelas' => $scenario['kelas']->id,
            'academic_year' => $scenario['academicYear']->id,
        ]))
        ->assertOk()
        ->assertSee($scenario['student']->name);
});

test('superadmin can manage academic years', function () {
    $admin = User::factory()->superadmin()->create();

    $this->actingAs($admin)
        ->get(route('nilai.academic-years'))
        ->assertOk()
        ->assertSee('Tahun Ajaran');

    $this->actingAs($admin)->post(route('nilai.academic-years.store'), [
        'name' => '2027/2028',
        'semester' => 'genap',
        'is_active' => '1',
    ])->assertRedirect(route('nilai.academic-years'));

    $this->assertDatabaseHas('academic_years', [
        'name' => '2027/2028',
        'semester' => 'genap',
        'is_active' => true,
    ]);
});

test('weights must total exactly one hundred percent', function () {
    $scenario = gradebookScenario();

    $this->actingAs($scenario['teacher'])->post(route('nilai.weights.store'), [
        'mapel_id' => $scenario['mapel']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'assignment_weight' => 40,
        'uts_weight' => 30,
        'uas_weight' => 40,
    ])->assertSessionHasErrors('assignment_weight');

    $this->assertDatabaseCount('grade_weights', 0);
});

test('student can view own report card but not another student report', function () {
    $scenario = gradebookScenario();
    $otherStudent = User::factory()->murid()->create(['kelas_id' => $scenario['kelas']->id]);

    $this->actingAs($scenario['teacher'])->post(route('nilai.input.store'), [
        'mapel_id' => $scenario['mapel']->id,
        'kelas_id' => $scenario['kelas']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'scores' => [
            $scenario['student']->id => ['uts' => 90, 'uas' => 100],
        ],
    ])->assertRedirect();

    $this->actingAs($scenario['student'])
        ->get(route('nilai.rapor', ['academic_year' => $scenario['academicYear']->id]))
        ->assertOk()
        ->assertSee('91.00')
        ->assertSee($scenario['mapel']->name);

    $this->actingAs($scenario['student'])
        ->get(route('nilai.rapor', ['student' => $otherStudent->id]))
        ->assertForbidden();
});

test('report card can be downloaded as pdf', function () {
    $scenario = gradebookScenario();

    $this->actingAs($scenario['teacher'])->post(route('nilai.input.store'), [
        'mapel_id' => $scenario['mapel']->id,
        'kelas_id' => $scenario['kelas']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'scores' => [
            $scenario['student']->id => ['uts' => 90, 'uas' => 100],
        ],
    ])->assertRedirect();

    $response = $this->actingAs($scenario['teacher'])->get(route('nilai.rapor.pdf', [
        'student' => $scenario['student']->id,
        'academic_year' => $scenario['academicYear']->id,
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('guru cannot manage grades for a subject they do not teach', function () {
    $scenario = gradebookScenario();
    $otherTeacher = User::factory()->guru()->create();

    $this->actingAs($otherTeacher)
        ->get(route('nilai.weights', ['mapel' => $scenario['mapel']->id]))
        ->assertForbidden();

    $this->actingAs($otherTeacher)->post(route('nilai.input.store'), [
        'mapel_id' => $scenario['mapel']->id,
        'kelas_id' => $scenario['kelas']->id,
        'academic_year_id' => $scenario['academicYear']->id,
        'scores' => [
            $scenario['student']->id => ['uts' => 90, 'uas' => 100],
        ],
    ])->assertForbidden();
});
