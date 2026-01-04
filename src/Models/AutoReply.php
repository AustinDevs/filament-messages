<?php

namespace Raseldev99\FilamentMessages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutoReply extends Model
{
    use SoftDeletes;

    protected $table = 'fm_auto_replies';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'message',
        'is_active',
        'trigger_type',
        'keywords',
        'start_at',
        'end_at',
        'reply_delay_seconds',
        'reply_once_per_conversation',
        'replied_conversations',
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
            'keywords' => 'array',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'reply_delay_seconds' => 'integer',
            'reply_once_per_conversation' => 'boolean',
            'replied_conversations' => 'array',
        ];
    }

    /**
     * Get the user that owns this auto-reply.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Check if the auto-reply is currently active based on schedule.
     *
     * @return bool
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        // Check if within scheduled time range
        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if this auto-reply should trigger for a given message and inbox.
     *
     * @param string|null $messageContent The content of the incoming message
     * @param int $inboxId The inbox ID
     * @return bool
     */
    public function shouldTrigger(?string $messageContent, int $inboxId): bool
    {
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        // Check if already replied to this conversation
        if ($this->reply_once_per_conversation) {
            $repliedConversations = $this->replied_conversations ?? [];
            if (in_array($inboxId, $repliedConversations)) {
                return false;
            }
        }

        // Check trigger type
        return match ($this->trigger_type) {
            'all' => true,
            'first_message' => $this->isFirstMessageInConversation($inboxId),
            'keywords' => $this->messageContainsKeywords($messageContent),
            default => true,
        };
    }

    /**
     * Check if this is the first message from the sender in the conversation.
     *
     * @param int $inboxId
     * @return bool
     */
    protected function isFirstMessageInConversation(int $inboxId): bool
    {
        $inbox = Inbox::find($inboxId);
        if (!$inbox) {
            return true;
        }

        // Check if there's any previous message in this conversation (excluding the current one)
        return $inbox->messages()->count() <= 1;
    }

    /**
     * Check if the message contains any of the configured keywords.
     *
     * @param string|null $messageContent
     * @return bool
     */
    protected function messageContainsKeywords(?string $messageContent): bool
    {
        if (!$messageContent || empty($this->keywords)) {
            return false;
        }

        $messageContent = strtolower($messageContent);

        foreach ($this->keywords as $keyword) {
            if (str_contains($messageContent, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark a conversation as replied to.
     *
     * @param int $inboxId
     * @return void
     */
    public function markConversationAsReplied(int $inboxId): void
    {
        $repliedConversations = $this->replied_conversations ?? [];
        if (!in_array($inboxId, $repliedConversations)) {
            $repliedConversations[] = $inboxId;
            $this->update(['replied_conversations' => $repliedConversations]);
        }
    }

    /**
     * Scope to get active auto-replies for a user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            });
    }
}
