<?php

namespace App\Models;

use Database\Factories\AttendanceDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDetail extends Model
{
    /** @use HasFactory<AttendanceDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'status',
        'reason',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get a readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return Attendance::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Get the badge color matching the attendance status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'H' => 'badge-success',
            'I' => 'badge-info',
            'S' => 'badge-warning',
            'A' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
