<?php

namespace Raseldev99\FilamentMessages\Services;

use Raseldev99\FilamentMessages\Models\Inbox;
use Raseldev99\FilamentMessages\Models\Message;

class MessageService
{
    /**
     * Send a message to an inbox as a specific user.
     *
     * @param int $inboxId The inbox/conversation ID
     * @param int $userId The user ID sending the message
     * @param string $message The message content
     * @return Message
     */
    public function sendMessage(int $inboxId, int $userId, string $message): Message
    {
        $inbox = Inbox::findOrFail($inboxId);

        $newMessage = $inbox->messages()->create([
            'message' => $message,
            'user_id' => $userId,
            'read_by' => [$userId],
            'read_at' => [now()],
            'notified' => [$userId],
        ]);

        $inbox->updated_at = now();
        $inbox->save();

        return $newMessage;
    }

    /**
     * Send a message to an inbox, creating the inbox if it doesn't exist.
     *
     * @param array $userIds Array of user IDs in the conversation
     * @param int $senderId The user ID sending the message
     * @param string $message The message content
     * @param string|null $title Optional title for group conversations
     * @return Message
     */
    public function sendMessageToUsers(array $userIds, int $senderId, string $message, ?string $title = null): Message
    {
        // Ensure sender is in the user list
        if (!in_array($senderId, $userIds)) {
            $userIds[] = $senderId;
        }

        // Sort user IDs for consistent lookup
        sort($userIds);
        $totalUserIds = count($userIds);

        // Try to find existing inbox with these exact users
        $inbox = Inbox::whereRaw(
            "JSON_CONTAINS(user_ids, ?) AND JSON_LENGTH(user_ids) = ?",
            [json_encode($userIds), $totalUserIds]
        )->first();

        // Create inbox if it doesn't exist
        if (!$inbox) {
            $inbox = Inbox::create([
                'title' => $title,
                'user_ids' => $userIds,
            ]);
        }

        return $this->sendMessage($inbox->id, $senderId, $message);
    }

    /**
     * Get or create an inbox for the given users.
     *
     * @param array $userIds Array of user IDs
     * @param string|null $title Optional title for group conversations
     * @return Inbox
     */
    public function getOrCreateInbox(array $userIds, ?string $title = null): Inbox
    {
        sort($userIds);
        $totalUserIds = count($userIds);

        $inbox = Inbox::whereRaw(
            "JSON_CONTAINS(user_ids, ?) AND JSON_LENGTH(user_ids) = ?",
            [json_encode($userIds), $totalUserIds]
        )->first();

        if (!$inbox) {
            $inbox = Inbox::create([
                'title' => $title,
                'user_ids' => $userIds,
            ]);
        }

        return $inbox;
    }

    /**
     * Get recent messages from an inbox.
     *
     * @param int $inboxId The inbox ID
     * @param int $limit Number of messages to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMessages(int $inboxId, int $limit = 10)
    {
        return Message::where('inbox_id', $inboxId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the latest message in an inbox.
     *
     * @param int $inboxId The inbox ID
     * @return Message|null
     */
    public function getLatestMessage(int $inboxId): ?Message
    {
        return Message::where('inbox_id', $inboxId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get all inboxes for a user.
     *
     * @param int $userId The user ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserInboxes(int $userId)
    {
        return Inbox::whereJsonContains('user_ids', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get unread message count for a user in an inbox.
     *
     * @param int $inboxId The inbox ID
     * @param int $userId The user ID
     * @return int
     */
    public function getUnreadCount(int $inboxId, int $userId): int
    {
        return Message::where('inbox_id', $inboxId)
            ->whereJsonDoesntContain('read_by', $userId)
            ->count();
    }

    /**
     * Mark all messages in an inbox as read for a user.
     *
     * @param int $inboxId The inbox ID
     * @param int $userId The user ID
     * @return int Number of messages marked as read
     */
    public function markAsRead(int $inboxId, int $userId): int
    {
        $messages = Message::where('inbox_id', $inboxId)
            ->whereJsonDoesntContain('read_by', $userId)
            ->get();

        foreach ($messages as $message) {
            $readBy = $message->read_by ?? [];
            $readAt = $message->read_at ?? [];

            if (!in_array($userId, $readBy)) {
                $readBy[] = $userId;
                $readAt[] = now();

                $message->update([
                    'read_by' => $readBy,
                    'read_at' => $readAt,
                ]);
            }
        }

        return $messages->count();
    }
}
