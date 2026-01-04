<?php

namespace AustinDevs\FilamentMessages\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use AustinDevs\FilamentMessages\Models\Inbox;

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
}
