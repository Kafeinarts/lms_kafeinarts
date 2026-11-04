<?php

namespace App\Models;

use Database\Factories\ViolationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    /** @use HasFactory<ViolationFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'reported_by',
        'description',
        'points',
        'room',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get readable room label.
     */
    public function getRoomNameAttribute(): string
    {
        return match ($this->room) {
            'bk' => 'Ruang BK',
            'guru' => 'Ruang Guru',
            default => ucfirst((string) $this->room),
        };
    }
}
