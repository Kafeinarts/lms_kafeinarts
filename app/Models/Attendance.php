<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    /**
     * Attendance status labels.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'H' => 'Hadir',
        'I' => 'Izin',
        'S' => 'Sakit',
        'A' => 'Alfa',
    ];

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'date',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(TeachingSchedule::class, 'schedule_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(AttendanceDetail::class);
    }

    /**
     * Summary count for each attendance status.
     *
     * @return array<string, int>
     */
    public function getStatusSummaryAttribute(): array
    {
        $summary = array_fill_keys(array_keys(self::STATUSES), 0);

        foreach ($this->details as $detail) {
            if (array_key_exists($detail->status, $summary)) {
                $summary[$detail->status]++;
            }
        }

        return $summary;
    }
}
