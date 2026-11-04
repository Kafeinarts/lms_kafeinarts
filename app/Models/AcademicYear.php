<?php

namespace App\Models;

use Database\Factories\AcademicYearFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    /** @use HasFactory<AcademicYearFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'semester',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function gradeWeights(): HasMany
    {
        return $this->hasMany(GradeWeight::class);
    }

    public function gradeComponents(): HasMany
    {
        return $this->hasMany(GradeComponent::class);
    }

    public function finalGrades(): HasMany
    {
        return $this->hasMany(FinalGrade::class);
    }

    /**
     * Get the active academic year, creating a default one when none exists.
     */
    public static function current(): self
    {
        $active = static::query()->where('is_active', true)->latest('id')->first();

        if ($active !== null) {
            return $active;
        }

        $existing = static::query()->latest('id')->first();

        if ($existing !== null) {
            return $existing;
        }

        $now = now();
        $startYear = $now->month >= 7 ? $now->year : $now->year - 1;

        return static::query()->create([
            'name' => $startYear.'/'.($startYear + 1),
            'semester' => $now->month >= 7 ? 'ganjil' : 'genap',
            'is_active' => true,
        ]);
    }

    /**
     * Activate this academic year and deactivate the others.
     */
    public function activate(): void
    {
        static::query()->whereKeyNot($this->id)->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }

    /**
     * Get a readable label, for example "2026/2027 - Semester Ganjil".
     */
    public function getLabelAttribute(): string
    {
        return $this->name.' - Semester '.ucfirst((string) $this->semester);
    }
}
