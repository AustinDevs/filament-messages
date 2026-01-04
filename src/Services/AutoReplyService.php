<?php

namespace Raseldev99\FilamentMessages\Services;

use Illuminate\Support\Facades\Log;
use Raseldev99\FilamentMessages\Models\AutoReply;
use Raseldev99\FilamentMessages\Models\Inbox;
use Raseldev99\FilamentMessages\Models\Message;

class AutoReplyService
{
    /**
     * Process auto-replies for a newly sent message.
     *
     * @param Message $message The message that was sent
     * @param Inbox $inbox The inbox containing the message
     * @return void
     */
    public function processAutoReplies(Message $message, Inbox $inbox): void
    {
        if (!config('filament-messages.auto_reply.enabled', true)) {
            return;
        }

        // Get all other users in the conversation (excluding the sender)
        $recipientUserIds = collect($inbox->user_ids)
            ->filter(fn ($userId) => $userId != $message->user_id)
            ->values();

        foreach ($recipientUserIds as $recipientUserId) {
            $this->processAutoReplyForUser($recipientUserId, $message, $inbox);
        }
    }

    /**
     * Process auto-reply for a specific user.
     *
     * @param int $userId The user to check for auto-replies
     * @param Message $incomingMessage The incoming message
     * @param Inbox $inbox The inbox
     * @return void
     */
    protected function processAutoReplyForUser(int $userId, Message $incomingMessage, Inbox $inbox): void
    {
        // Get active auto-replies for this user
        $autoReplies = AutoReply::activeForUser($userId)->get();

        foreach ($autoReplies as $autoReply) {
            if ($autoReply->shouldTrigger($incomingMessage->message, $inbox->id)) {
                $this->sendAutoReply($autoReply, $inbox, $userId);
                break; // Only send one auto-reply per user per message
            }
        }
    }

    /**
     * Send an auto-reply message.
     *
     * @param AutoReply $autoReply The auto-reply configuration
     * @param Inbox $inbox The inbox to send to
     * @param int $userId The user sending the auto-reply
     * @return Message|null
     */
    protected function sendAutoReply(AutoReply $autoReply, Inbox $inbox, int $userId): ?Message
    {
        try {
            // Apply delay if configured
            if ($autoReply->reply_delay_seconds > 0) {
                // For now, we'll dispatch a job for delayed replies
                // In a simple implementation, we send immediately
                // You could implement a queued job for delayed sending
            }

            // Create the auto-reply message
            $message = $inbox->messages()->create([
                'message' => $this->processMessagePlaceholders($autoReply->message, $inbox, $userId),
                'user_id' => $userId,
                'read_by' => [$userId],
                'read_at' => [now()],
                'notified' => [$userId],
            ]);

            // Update the inbox timestamp
            $inbox->updated_at = now();
            $inbox->save();

            // Mark conversation as replied if configured
            if ($autoReply->reply_once_per_conversation) {
                $autoReply->markConversationAsReplied($inbox->id);
            }

            return $message;
        } catch (\Exception $e) {
            Log::error('Failed to send auto-reply', [
                'auto_reply_id' => $autoReply->id,
                'inbox_id' => $inbox->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Process placeholders in the auto-reply message.
     *
     * Available placeholders:
     * - {sender_name}: Name of the person who sent the message
     * - {recipient_name}: Name of the auto-reply owner
     * - {date}: Current date
     * - {time}: Current time
     *
     * @param string $message The message template
     * @param Inbox $inbox The inbox
     * @param int $userId The user ID of the auto-reply owner
     * @return string
     */
    protected function processMessagePlaceholders(string $message, Inbox $inbox, int $userId): string
    {
        $user = \App\Models\User::find($userId);
        $otherUsers = collect($inbox->user_ids)
            ->filter(fn ($id) => $id != $userId)
            ->map(fn ($id) => \App\Models\User::find($id)?->name)
            ->filter()
            ->implode(', ');

        $timezone = config('filament-messages.timezone', 'UTC');

        $replacements = [
            '{sender_name}' => $otherUsers,
            '{recipient_name}' => $user?->name ?? '',
            '{date}' => now()->setTimezone($timezone)->format('F j, Y'),
            '{time}' => now()->setTimezone($timezone)->format('g:i A'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Create a default auto-reply for a user.
     *
     * @param int $userId
     * @param string $message
     * @param array $options
     * @return AutoReply
     */
    public static function createAutoReply(int $userId, string $message, array $options = []): AutoReply
    {
        return AutoReply::create([
            'user_id' => $userId,
            'message' => $message,
            'is_active' => $options['is_active'] ?? true,
            'trigger_type' => $options['trigger_type'] ?? 'all',
            'keywords' => $options['keywords'] ?? null,
            'start_at' => $options['start_at'] ?? null,
            'end_at' => $options['end_at'] ?? null,
            'reply_delay_seconds' => $options['reply_delay_seconds'] ?? 0,
            'reply_once_per_conversation' => $options['reply_once_per_conversation'] ?? false,
        ]);
    }
}
