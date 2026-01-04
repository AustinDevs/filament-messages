<?php

namespace Raseldev99\FilamentMessages\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Raseldev99\FilamentMessages\Models\AutoReply;
use Raseldev99\FilamentMessages\Models\Inbox;

trait HasFilamentMessages
{
    /**
     * Retrieves all conversations for the current user.
     *
     * @return Builder
     */
    public function allConversations(): Builder
    {
        return Inbox::whereJsonContains('user_ids', $this->id)->orderBy('updated_at', 'desc');
    }

    /**
     * Get all auto-replies for this user.
     *
     * @return HasMany
     */
    public function autoReplies(): HasMany
    {
        return $this->hasMany(AutoReply::class);
    }

    /**
     * Get active auto-replies for this user.
     *
     * @return Builder
     */
    public function activeAutoReplies(): Builder
    {
        return AutoReply::activeForUser($this->id);
    }

    /**
     * Check if the user has any active auto-replies.
     *
     * @return bool
     */
    public function hasActiveAutoReply(): bool
    {
        return $this->activeAutoReplies()->exists();
    }

    /**
     * Create a new auto-reply for this user.
     *
     * @param string $message The auto-reply message
     * @param array $options Additional options for the auto-reply
     * @return AutoReply
     */
    public function createAutoReply(string $message, array $options = []): AutoReply
    {
        return AutoReply::create([
            'user_id' => $this->id,
            'message' => $message,
            'is_active' => $options['is_active'] ?? true,
            'trigger_type' => $options['trigger_type'] ?? config('filament-messages.auto_reply.default_trigger_type', 'all'),
            'keywords' => $options['keywords'] ?? null,
            'start_at' => $options['start_at'] ?? null,
            'end_at' => $options['end_at'] ?? null,
            'reply_delay_seconds' => $options['reply_delay_seconds'] ?? 0,
            'reply_once_per_conversation' => $options['reply_once_per_conversation'] ?? false,
        ]);
    }

    /**
     * Disable all active auto-replies for this user.
     *
     * @return int Number of auto-replies disabled
     */
    public function disableAllAutoReplies(): int
    {
        return $this->autoReplies()->where('is_active', true)->update(['is_active' => false]);
    }
}
