<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Record one activity entry for the current (or given) user.
     */
    public static function record(string $action, string $description, ?Model $subject = null, ?User $user = null): self
    {
        return self::create([
            'user_id' => $user?->id ?? Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 500),
        ]);
    }

    /**
     * Get a readable label for the logged action.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'login' => 'Masuk',
            'logout' => 'Keluar',
            'user.created' => 'Akun dibuat',
            'user.updated' => 'Akun diperbarui',
            'user.deleted' => 'Akun dihapus',
            'user.password_changed' => 'Kata sandi diubah',
            'material.created' => 'Materi ditambahkan',
            'material.updated' => 'Materi diperbarui',
            'material.deleted' => 'Materi dihapus',
            'material.restored' => 'Materi dipulihkan',
            'material.force_deleted' => 'Materi dihapus permanen',
            'exam.created' => 'Tugas/Ujian dibuat',
            'exam.updated' => 'Tugas/Ujian diperbarui',
            'exam.deleted' => 'Tugas/Ujian dihapus',
            'kelas.created' => 'Kelas ditambahkan',
            'kelas.updated' => 'Kelas diperbarui',
            'kelas.deleted' => 'Kelas dihapus',
            'mapel.created' => 'Mapel ditambahkan',
            'mapel.updated' => 'Mapel diperbarui',
            'mapel.deleted' => 'Mapel dihapus',
            'assignment.created' => 'Penugasan ditambahkan',
            'assignment.updated' => 'Penugasan diperbarui',
            'assignment.deleted' => 'Penugasan dihapus',
            'schedule.created' => 'Jadwal ditambahkan',
            'schedule.updated' => 'Jadwal diperbarui',
            'schedule.deleted' => 'Jadwal dihapus',
            'guardian.created' => 'Wali murid ditambahkan',
            'guardian.updated' => 'Wali murid diperbarui',
            'guardian.deleted' => 'Wali murid dihapus',
            'violation.created' => 'Pelanggaran dicatat',
            'school.updated' => 'Informasi sekolah diperbarui',
            default => Str::of($this->action)->replace(['.', '_'], ' ')->title(),
        };
    }

    /**
     * Get the badge color matching the action group.
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match (true) {
            str_starts_with($this->action, 'user.') => 'badge-primary',
            str_starts_with($this->action, 'material.') => 'badge-warning',
            str_starts_with($this->action, 'exam.') => 'badge-info',
            str_starts_with($this->action, 'login') => 'badge-success',
            str_starts_with($this->action, 'logout') => 'badge-secondary',
            str_starts_with($this->action, 'violation.') => 'badge-danger',
            default => 'badge-light border',
        };
    }
}
