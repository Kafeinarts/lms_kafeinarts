<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SchoolProfileController extends Controller
{
    /**
     * Show the school information form (Super Admin only).
     */
    public function edit(): View
    {
        return view('pages.sekolah.edit', [
            'school' => SchoolProfile::current(),
        ]);
    }

    /**
     * Show the public school profile page (no login required).
     */
    public function publicProfile(): View
    {
        return view('pages.sekolah.public', [
            'school' => SchoolProfile::current(),
            'stats' => [
                'guru' => User::where('role', 'guru')->count(),
                'murid' => User::where('role', 'murid')->count(),
                'kelas' => Kelas::count(),
                'mapel' => Mapel::count(),
            ],
        ]);
    }

    /**
     * Update the school identity, address, and contact information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'npsn' => ['nullable', 'string', 'max:30'],
            'level' => ['nullable', Rule::in(['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK', 'MAK'])],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => 'Nama sekolah wajib diisi.',
            'level.in' => 'Jenjang sekolah tidak valid.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Format logo yang diperbolehkan: jpeg, png, jpg, webp, svg.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'email.email' => 'Format email sekolah tidak valid.',
            'website.url' => 'Alamat website harus berupa URL lengkap, misalnya https://sekolah.sch.id.',
            'description.max' => 'Keterangan sekolah maksimal 2000 karakter.',
        ]);

        $school = SchoolProfile::current();

        if ($request->hasFile('logo')) {
            if ($school->logo && Storage::disk('public')->exists($school->logo)) {
                Storage::disk('public')->delete($school->logo);
            }

            $validated['logo'] = $request->file('logo')->store('school', 'public');
        }

        $school->update($validated);

        ActivityLog::record('school.updated', "Informasi sekolah '{$school->name}' diperbarui", $school);

        return redirect()->route('sekolah.edit')->with('success', 'Informasi sekolah berhasil diperbarui!');
    }

    /**
     * Remove the uploaded school logo.
     */
    public function removeLogo(): RedirectResponse
    {
        $school = SchoolProfile::current();

        if ($school->logo && Storage::disk('public')->exists($school->logo)) {
            Storage::disk('public')->delete($school->logo);
        }

        $school->update(['logo' => null]);

        ActivityLog::record('school.updated', 'Logo sekolah dihapus', $school);

        return redirect()->route('sekolah.edit')->with('success', 'Logo sekolah berhasil dihapus.');
    }
}
