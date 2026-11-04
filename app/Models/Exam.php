<?php

namespace App\Models;

use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mapel_id',
        'title',
        'description',
        'type',
        'duration_minutes',
        'available_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'available_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ExamSubmission::class)->with('student')->latest('submitted_at');
    }

    /**
     * Get the current user's submission for this exam, if any.
     */
    public function submissionFor(User $user): ?ExamSubmission
    {
        return $this->submissions()->where('user_id', $user->id)->first();
    }

    public function getIsTugasAttribute(): bool
    {
        return $this->type === 'tugas';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'ujian' => 'Ujian',
            default => 'Tugas',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'ujian' => 'badge-danger',
            default => 'badge-primary',
        };
    }
}
