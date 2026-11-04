<?php

namespace App\Models;

use Database\Factories\FinalGradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalGrade extends Model
{
    /** @use HasFactory<FinalGradeFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'mapel_id',
        'academic_year_id',
        'final_score',
        'grade_letter',
        'teacher_notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'final_score' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the badge color matching the grade letter.
     */
    public function getGradeBadgeClassAttribute(): string
    {
        return match ($this->grade_letter) {
            'A' => 'badge-success',
            'B' => 'badge-info',
            'C' => 'badge-warning',
            'D' => 'badge-secondary',
            default => 'badge-danger',
        };
    }
}
