<?php

namespace App\Models;

use Database\Factories\TeachingScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingSchedule extends Model
{
    /** @use HasFactory<TeachingScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'day',
        'start_time',
        'end_time',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'assignment_id');
    }

    /**
     * Get readable day label.
     */
    public function getDayNameAttribute(): string
    {
        return match ($this->day) {
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu',
            default => ucfirst((string) $this->day),
        };
    }
}
