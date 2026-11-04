<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AcademicYearController extends Controller
{
    /**
     * Display the academic year list.
     */
    public function index(): View
    {
        return view('pages.nilai.academic-years', [
            'academicYears' => AcademicYear::query()
                ->withCount(['gradeWeights', 'finalGrades'])
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    /**
     * Store a new academic year.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:ganjil,genap'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'semester.in' => 'Semester harus ganjil atau genap.',
        ]);

        $exists = AcademicYear::query()
            ->where('name', $validated['name'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'Tahun ajaran dan semester tersebut sudah terdaftar.',
            ]);
        }

        $academicYear = AcademicYear::create([
            'name' => $validated['name'],
            'semester' => $validated['semester'],
            'is_active' => false,
        ]);

        if ($request->boolean('is_active') || AcademicYear::query()->count() === 1) {
            $academicYear->activate();
        }

        ActivityLog::record(
            'academic_year.created',
            "Tahun ajaran {$academicYear->label} ditambahkan",
            $academicYear
        );

        return redirect()->route('nilai.academic-years')->with('success', "Tahun ajaran {$academicYear->label} berhasil ditambahkan.");
    }

    /**
     * Activate an academic year.
     */
    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate();

        ActivityLog::record(
            'academic_year.activated',
            "Tahun ajaran {$academicYear->label} diaktifkan",
            $academicYear
        );

        return redirect()->route('nilai.academic-years')->with('success', "Tahun ajaran {$academicYear->label} sekarang aktif.");
    }
}
