<?php

namespace App\Models;

use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'type',
        'kelas_id',
        'mapel_id',
        'body',
        'file_path',
        'file_name',
        'is_published',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    /**
     * Check whether a student can access this material (same grade level or general material).
     */
    public function isAccessibleBy(User $user): bool
    {
        if ($this->kelas_id === null) {
            return true;
        }

        if ($user->kelas_id === null || $this->kelas === null || $user->kelas === null) {
            return false;
        }

        return $this->kelas->tingkat === $user->kelas->tingkat;
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags((string) $this->body), 120);
    }

    /**
     * Render the material body for the detail page.
     *
     * CKEditor stores HTML, while legacy/plain materials store plain text.
     */
    public function getRenderedBodyAttribute(): string
    {
        $body = (string) $this->body;

        if ($body === '') {
            return '';
        }

        $htmlTags = '/<(?:p|div|h[1-6]|ul|ol|li|pre|code|img|table|blockquote|a|b|strong|i|em|figure|figcaption)\b/i';

        if (preg_match($htmlTags, $body)) {
            return $body;
        }

        return nl2br(e($body));
    }

    /**
     * Get public storage paths of images embedded in the article body.
     *
     * @return array<int, string>
     */
    public function embeddedImagePaths(): array
    {
        $paths = [];

        if (! preg_match_all('/(?:src|href)="([^"]+)"/i', (string) $this->body, $matches)) {
            return $paths;
        }

        foreach ($matches[1] as $url) {
            $relative = Str::after((string) $url, asset('storage/').'/');

            if ($relative !== (string) $url && str_starts_with($relative, 'materials/')) {
                $paths[] = $relative;
            }
        }

        return array_values(array_unique($paths));
    }
}
