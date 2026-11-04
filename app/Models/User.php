<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// [Fillable(['name', 'email', 'password', 'password_plain', 'role', 'avatar', 'nis', 'nip', 'phone', 'bidang', 'kelas_id'])]
// [Hidden(['password', 'password_plain', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_plain' => 'encrypted',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waliMurids(): HasMany
    {
        return $this->hasMany(WaliMurid::class, 'student_id');
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    public function reportedViolations(): HasMany
    {
        return $this->hasMany(Violation::class, 'reported_by');
    }

    /**
     * Get the real (database) role, ignoring any active role simulation.
     */
    public function realRole(): string
    {
        return (string) $this->getRawOriginal('role');
    }

    /**
     * Get the role currently simulated by a Super Admin, if any.
     */
    public function simulatedRole(): ?string
    {
        if (! $this->reallyIsSuperAdmin()) {
            return null;
        }

        $role = session('simulated_role');

        return in_array($role, ['guru', 'murid'], true) ? $role : null;
    }

    /**
     * Get the role used for rendering and authorization (simulated role wins).
     */
    public function effectiveRole(): string
    {
        return $this->simulatedRole() ?? $this->realRole();
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->effectiveRole() === 'superadmin';
    }

    /**
     * Check if user can manage LMS content (Super Admin or Guru).
     */
    public function isTeacher(): bool
    {
        return in_array($this->effectiveRole(), ['superadmin', 'guru'], true);
    }

    /**
     * Check if user is a student (Murid).
     */
    public function isStudent(): bool
    {
        return $this->effectiveRole() === 'murid';
    }

    /**
     * Check the real database role regardless of any simulation.
     */
    public function reallyIsSuperAdmin(): bool
    {
        return $this->realRole() === 'superadmin';
    }

    /**
     * Check if a role simulation is currently active.
     */
    public function isSimulating(): bool
    {
        return $this->simulatedRole() !== null;
    }

    /**
     * Get URL for user avatar or default illustration.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(storage_path('app/public/'.$this->avatar))) {
            return asset('storage/'.$this->avatar);
        }

        return asset('template/img/undraw_profile.svg');
    }

    /**
     * Get readable role label.
     */
    public function getRoleNameAttribute(): string
    {
        return match ($this->effectiveRole()) {
            'superadmin' => 'Super Admin',
            'guru' => 'Guru',
            'murid' => 'Murid',
            default => ucfirst($this->effectiveRole()),
        };
    }

    /**
     * Get badge HTML class for role.
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->effectiveRole()) {
            'superadmin' => 'badge-danger',
            'guru' => 'badge-primary',
            'murid' => 'badge-success',
            default => 'badge-secondary',
        };
    }
}
