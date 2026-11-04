<?php

namespace App\Models;

use Database\Factories\MapelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
    /** @use HasFactory<MapelFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    public function teachingAssignments(): HasMany{
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Teachers who are assigned to teach this subject.
     *
     * @return BelongsToMany<User, TeachingAssignment>
     */
    public function assignedTeachers(): BelongsToMany{
        return $this->belongsToMany(User::class, 'teaching_assignments', 'mapel_id', 'teacher_id')
            ->orderBy('name')
            ->distinct();
    }
}
