<?php

namespace App\Models;

use Database\Factories\GradeComponentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeComponent extends Model
{
    /** @use HasFactory<GradeComponentFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'mapel_id',
        'academic_year_id',
        'component',
        'score',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
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
     * Get a readable component label.
     */
    public function getComponentLabelAttribute(): string
    {
        return match ($this->component) {
            'uts' => 'UTS',
            'uas' => 'UAS',
            default => strtoupper((string) $this->component),
        };
    }
}
