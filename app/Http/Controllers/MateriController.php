<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    /**
     * Display a listing of materials, grouped by mata pelajaran.
     */
    public function index(Request $request): View
    {
        $query = Material::with(['author', 'kelas', 'mapel'])
            ->leftJoin('users', 'users.id', '=', 'materials.user_id')
            ->leftJoin('mapels', 'mapels.id', '=', 'materials.mapel_id')
            ->select('materials.*');

        $user = $request->user();
        if ($user->isStudent()) {
            $query->where('materials.is_published', true)
                ->where(function ($q) use ($user) {
                    $q->whereNull('materials.kelas_id');

                    if ($user->kelas_id !== null && $user->kelas !== null) {
                        $q->orWhereHas('kelas', fn ($k) => $k->where('tingkat', $user->kelas->tingkat));
                    }
                });
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('materials.title', 'like', '%'.$request->string('q').'%')
                    ->orWhere('materials.body', 'like', '%'.$request->string('q').'%');
            });
        }

        if ($request->filled('mapel')) {
            $query->where('materials.mapel_id', $request->integer('mapel'));
        }

        match ($request->string('sort')->toString()) {
            'teacher' => $query->orderBy('users.name'),
            'mapel' => $query->orderBy('mapels.name'),
            'title' => $query->orderBy('materials.title'),
            'oldest' => $query->orderBy('materials.created_at'),
            default => $query->orderByDesc('materials.created_at'),
        };

        $query->orderByDesc('materials.created_at');

        $mapels = Mapel::orderBy('name')->get();
        $selectedMapel = $request->filled('mapel')
            ? $mapels->firstWhere('id', $request->integer('mapel'))
            : null;

        // Tanpa filter/pencarian: tampilkan kartu mata pelajaran yang memuat materinya.
        if (! $request->filled('mapel') && ! $request->filled('q')) {
            $allMaterials = $query->get();

            $mapelGroups = $mapels
                ->map(function (Mapel $mapel) use ($allMaterials) {
                    $owned = $allMaterials->where('mapel_id', $mapel->id)->values();

                    return [
                        'mapel' => $mapel,
                        'materials' => $owned->take(6),
                        'total' => $owned->count(),
                    ];
                })
                ->filter(fn (array $group) => $group['total'] > 0)
                ->values();

            $unassigned = $allMaterials->whereNull('mapel_id')->values();

            return view('pages.materis.index', [
                'mode' => 'mapels',
                'mapels' => $mapels,
                'mapelGroups' => $mapelGroups,
                'unassignedMaterials' => $unassigned->take(6),
                'unassignedTotal' => $unassigned->count(),
                'selectedMapel' => null,
                'materials' => null,
            ]);
        }

        return view('pages.materis.index', [
            'mode' => 'list',
            'materials' => $query->paginate(9)->withQueryString(),
            'mapels' => $mapels,
            'selectedMapel' => $selectedMapel,
        ]);
    }

    /**
     * Show the form for creating a new material.
     */
    public function create(): View
    {
        return view('pages.materis.create', [
            'kelasList' => Kelas::orderBy('tingkat')->orderBy('name')->get(),
            'mapels' => Mapel::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created material in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        [$filePath, $fileName] = $this->storedAttachment($request);

        $material = Material::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'type' => $validated['type'],
            'kelas_id' => $validated['kelas_id'] ?? null,
            'mapel_id' => $validated['mapel_id'] ?? null,
            'body' => $validated['body'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'is_published' => $request->boolean('is_published', true),
        ]);

        ActivityLog::record('material.created', "Materi '{$material->title}' ditambahkan", $material);

        return redirect()->route('materis.index')->with('success', 'Materi berhasil dipublikasikan!');
    }

    /**
     * Handle image uploads coming from the CKEditor WYSIWYG editor.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ], [
            'upload.required' => 'Pilih gambar yang akan diunggah.',
            'upload.file' => 'File harus berupa gambar.',
            'upload.image' => 'File harus berupa gambar.',
            'upload.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif, webp.',
            'upload.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        $path = $request->file('upload')->store('materials/images', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * List soft-deleted materials (History Bin).
     */
    public function bin(Request $request): View
    {
        $query = Material::onlyTrashed()->with(['author', 'kelas'])->latest('deleted_at');

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }

        return view('pages.materis.bin', [
            'materials' => $query->paginate(10)->withQueryString(),
        ]);
    }

    /**
     * Restore a soft-deleted material.
     */
    public function restore(Material $materi): RedirectResponse
    {
        $materi->restore();

        ActivityLog::record('material.restored', "Materi '{$materi->title}' dipulihkan dari History Bin", $materi);

        return redirect()->route('materis.bin')->with('success', 'Materi berhasil dipulihkan dan kembali tampil di daftar materi.');
    }

    /**
     * Permanently delete a soft-deleted material and its files.
     */
    public function forceDelete(Material $materi): RedirectResponse
    {
        ActivityLog::record('material.force_deleted', "Materi '{$materi->title}' dihapus permanen", null);
        foreach ($materi->embeddedImagePaths() as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->forceDelete();

        return redirect()->route('materis.bin')->with('success', 'Materi dihapus permanen beserta file pendukungnya.');
    }

    /**
     * Display the specified material.
     */
    public function show(Request $request, Material $materi): View
    {
        if ($request->user()->isStudent() && ! $materi->isAccessibleBy($request->user())) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }

        $materi->load(['author', 'kelas']);

        return view('pages.materis.show', [
            'material' => $materi,
        ]);
    }

    /**
     * Show the form for editing the specified material.
     */
    public function edit(Material $materi): View
    {
        return view('pages.materis.edit', [
            'material' => $materi,
            'kelasList' => Kelas::orderBy('tingkat')->orderBy('name')->get(),
            'mapels' => Mapel::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified material in storage.
     */
    public function update(Request $request, Material $materi): RedirectResponse
    {
        $validated = $this->validated($request, $materi);

        $filePath = $materi->file_path;
        $fileName = $materi->file_name;

        if ($request->hasFile('gambar') || $request->hasFile('file')) {
            if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
                Storage::disk('public')->delete($materi->file_path);
            }

            [$filePath, $fileName] = $this->storedAttachment($request);
        }

        $slug = Str::slug($validated['title']);
        if (Material::withTrashed()->where('slug', $slug)->where('id', '!=', $materi->id)->exists()) {
            $slug = $materi->slug;
        }

        $materi->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'type' => $validated['type'],
            'kelas_id' => $validated['kelas_id'] ?? null,
            'mapel_id' => $validated['mapel_id'] ?? null,
            'body' => $validated['body'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'is_published' => $request->boolean('is_published', $materi->is_published),
        ]);

        ActivityLog::record('material.updated', "Materi '{$materi->title}' diperbarui", $materi);

        return redirect()->route('materis.index')->with('success', 'Materi berhasil diperbarui!');
    }

    /**
     * Soft delete the specified material (moves it to the History Bin).
     */
    public function destroy(Material $materi): RedirectResponse
    {
        ActivityLog::record('material.deleted', "Materi '{$materi->title}' dipindahkan ke History Bin", $materi);

        $materi->delete();

        return redirect()->route('materis.index')->with('success', 'Materi dipindahkan ke History Bin. Anda bisa memulihkannya kembali.');
    }

    /**
     * Persist the uploaded attachment (image or document) to the public disk.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function storedAttachment(Request $request): array
    {
        if ($request->hasFile('gambar')) {
            return [
                $request->file('gambar')->store('materials', 'public'),
                $request->file('gambar')->getClientOriginalName(),
            ];
        }

        if ($request->hasFile('file')) {
            return [
                $request->file('file')->store('materials', 'public'),
                $request->file('file')->getClientOriginalName(),
            ];
        }

        return [null, null];
    }

    /**
     * Validate material payload for all tab modes (artikel, gambar or file upload).
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Material $material = null): array
    {
        $request->merge(['type' => $request->input('type', 'artikel')]);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:artikel,gambar,file'],
            'body' => ['required_if:type,artikel', 'nullable', 'string', 'max:200000'],
            'gambar' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt', 'max:10240'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'is_published' => ['nullable', 'boolean'],
        ];

        // A gambar/file material may keep its existing attachment when editing.
        if ($material === null || $material->file_path === null) {
            $rules['gambar'][] = 'required_if:type,gambar';
            $rules['file'][] = 'required_if:type,file';
        }

        return $request->validate($rules, [
            'body.required_if' => 'Tulis isi materi pada tab artikel.',
            'gambar.required_if' => 'Unggah gambar pada tab input gambar.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif, webp.',
            'gambar.max' => 'Ukuran gambar maksimal 5MB.',
            'file.required_if' => 'Upload file PDF/PPT pada tab upload file.',
            'file.mimes' => 'Format file tidak didukung.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);

        if (Material::withTrashed()->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::random(5);
        }

        return $slug;
    }
}
