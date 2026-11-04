<?php

namespace App\Models;

use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    /**
     * Announcement audience labels.
     *
     * @var array<string, string>
     */
    public const AUDIENCES = [
        'all' => 'Semua Pengguna',
        'guru' => 'Guru',
        'murid' => 'Siswa',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'audience',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Limit announcements to those visible for the given user.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $roles = $user->isStudent() ? ['all', 'murid'] : ['all', 'guru'];

        return $query->where(fn (Builder $query) => $query
            ->whereIn('audience', $roles)
            ->orWhere('user_id', $user->id));
    }

    /**
     * Check whether the given user may read this announcement.
     */
    public function isVisibleTo(User $user): bool
    {
        if ($user->isSuperAdmin() || $this->user_id === $user->id) {
            return true;
        }

        return $this->audience === 'all'
            || ($this->audience === 'guru' && $user->effectiveRole() === 'guru')
            || ($this->audience === 'murid' && $user->isStudent());
    }

    /**
     * Get a readable audience label.
     */
    public function getAudienceLabelAttribute(): string
    {
        return self::AUDIENCES[$this->audience] ?? ucfirst((string) $this->audience);
    }

    /**
     * Get the badge color matching the audience.
     */
    public function getAudienceBadgeClassAttribute(): string
    {
        return match ($this->audience) {
            'guru' => 'badge-primary',
            'murid' => 'badge-success',
            default => 'badge-info',
        };
    }
}
