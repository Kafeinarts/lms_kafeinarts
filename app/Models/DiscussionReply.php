<?php

namespace App\Models;

use Database\Factories\DiscussionReplyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionReply extends Model
{
    /** @use HasFactory<DiscussionReplyFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'user_id',
        'body',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(DiscussionTopic::class, 'topic_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
