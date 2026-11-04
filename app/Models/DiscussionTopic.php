<?php

namespace App\Models;

use Database\Factories\DiscussionTopicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionTopic extends Model
{
    /** @use HasFactory<DiscussionTopicFactory> */
    use HasFactory;

    protected $fillable = [
        'mapel_id',
        'user_id',
        'title',
        'body',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'topic_id')->oldest();
    }
}
