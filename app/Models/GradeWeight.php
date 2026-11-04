<?php

namespace App\Models;

use Database\Factories\GradeWeightFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeWeight extends Model
{
    /** @use HasFactory<GradeWeightFactory> */
    use HasFactory;

    protected $fillable = [
        'mapel_id',
        'academic_year_id',
        'assignment_weight',
        'uts_weight',
        'uas_weight',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assignment_weight' => 'integer',
            'uts_weight' => 'integer',
            'uas_weight' => 'integer',
        ];
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
     * Total percentage of all weights.
     */
    public function getTotalWeightAttribute(): int
    {
        return $this->assignment_weight + $this->uts_weight + $this->uas_weight;
    }
}
