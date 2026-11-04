<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SchoolProfile extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'npsn',
        'level',
        'headmaster_name',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'website',
        'description',
    ];

    /**
     * Get the school profile row, creating the default one when empty.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'name' => 'MySchool',
        ]);
    }

    /**
     * Get the public URL of the school logo, if one is uploaded.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo || ! Storage::disk('public')->exists($this->logo)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }

    /**
     * Check whether the school information has been filled in.
     */
    public function hasDetails(): bool
    {
        return collect([
            'npsn',
            'level',
            'headmaster_name',
            'address',
            'city',
            'province',
            'postal_code',
            'phone',
            'email',
            'website',
            'description',
        ])->contains(fn (string $field) => filled($this->{$field}));
    }

    /**
     * Get the full address in one readable line.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
            $this->postal_code,
        ], fn (?string $part) => filled($part));

        return implode(', ', $parts);
    }
}
