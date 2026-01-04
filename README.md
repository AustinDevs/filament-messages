# Filament Messages

**Filament Messages** is a powerful messaging plugin for [FilamentPHP](https://filamentphp.com/). It provides an easy-to-use interface for real-time messaging within Filament admin panels.

![screen-1](resources/images/screen-1.png)
<p align="center">
  <img src="resources/images/screen-2.png" width="49.7%" />
  <img src="resources/images/screen-3.png" width="49.7%" />
</p>

![GitHub stars](https://img.shields.io/github/stars/jeddsaliba/filament-messages?style=flat-square)
![GitHub issues](https://img.shields.io/github/issues/jeddsaliba/filament-messages?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)
![PHP Version](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-11|12-red?style=flat-square&logo=laravel)
![Filament Version](https://img.shields.io/badge/Filament-4.0-purple?style=flat-square)

**Key Features:**
- **Seamless Integration:** Designed specifically for FilamentPHP, making it easy to integrate into your admin panel.
- **User-to-User & Group Chats:** Enables both private conversations and group discussions.
- **Unread Message Badges:** Displays unread message counts in the sidebar for better visibility.
- **File Attachments:** Allows sending images, documents, and other media.
- **Programmatic Message API:** Send messages from bots, AI systems, or any backend service.
- **Database-Driven:** Uses Eloquent models for structured and scalable messaging.
- **Configurable Refresh Interval:** Lets you set the chat update frequency for optimized performance.
- **Timezone Support:** Allows setting a preferred timezone to maintain consistent timestamps across messages.

## Table of Contents
[Getting Started](#getting-started)<br/>
[Prerequisite](#prerequisite)<br/>
[User Model](#user-model)<br/>
[Admin Panel Provider](#admin-panel-provider)<br/>
[Programmatic Message API](#programmatic-messages)<br/>
[Configuration](#configuration)<br/>
[Plugins Used](#plugins-used)<br/>
[Acknowledgments](#acknowledgments)<br/>
[Support](#support)

<a name="getting-started"></a>
## Getting Started
You can install the package via Composer:

```bash
composer require raseldev99/filament-messages
```

Run this command to install all migrations and configurations.

```bash
php artisan filament-messages:install
```

<a name="prerequisite"></a>
## Prerequisite
This plugin requires **Filament v4.0+** and utilizes Filament Spatie Media Library. Please follow the steps below.

Install the plugin with Composer:

```bash
composer require filament/spatie-laravel-media-library-plugin:"^4.0" -W
```

If you haven't already done so, you need to publish the migration to create the media table:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
```

Run the migrations:

```bash
php artisan migrate
```

Or follow the documentation [here](https://github.com/filamentphp/spatie-laravel-media-library-plugin).

<a name="user-model"></a>
## User Model
Add the trait to your User model:

```php
<?php

use Raseldev99\FilamentMessages\Models\Traits\HasFilamentMessages;

class User extends Authenticatable
{
    use HasFilamentMessages;
}
```

<a name="admin-panel-provider"></a>
## Admin Panel Provider
Add this plugin to your FilamentPHP panel provider:

```php
<?php

use Raseldev99\FilamentMessages\FilamentMessagesPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugins([
                FilamentMessagesPlugin::make()
            ]);
    }
}
```

<a name="programmatic-messages"></a>
## Programmatic Message API

The `MessageService` provides a simple API for sending messages programmatically. This is useful for integrating with AI systems, bots, webhooks, or any automated messaging.

### Basic Usage

```php
use Raseldev99\FilamentMessages\Services\MessageService;

$messageService = app(MessageService::class);

// Send a message to an existing conversation
$message = $messageService->sendMessage(
    inboxId: 1,
    userId: $botUserId,
    message: 'Hello! How can I help you today?'
);
```

### Create Conversations and Send Messages

```php
// Send a message to users, creating the conversation if needed
$message = $messageService->sendMessageToUsers(
    userIds: [$userId1, $userId2, $botUserId],
    senderId: $botUserId,
    message: 'Welcome to the conversation!',
    title: 'Support Chat' // Optional, for group chats
);
```

### Get or Create an Inbox

```php
// Get an existing inbox or create a new one
$inbox = $messageService->getOrCreateInbox(
    userIds: [$userId, $botUserId],
    title: null // Optional
);
```

### Retrieve Messages

```php
// Get recent messages from a conversation
$messages = $messageService->getMessages($inboxId, limit: 20);

// Get the latest message
$latestMessage = $messageService->getLatestMessage($inboxId);

// Get all inboxes for a user
$inboxes = $messageService->getUserInboxes($userId);
```

### Read Status

```php
// Get unread count for a user
$unreadCount = $messageService->getUnreadCount($inboxId, $userId);

// Mark all messages as read
$messageService->markAsRead($inboxId, $userId);
```

### Example: AI Bot Integration

```php
use Raseldev99\FilamentMessages\Services\MessageService;

class AiBotController extends Controller
{
    public function handleMessage(Request $request, MessageService $messageService)
    {
        $inboxId = $request->input('inbox_id');
        $botUserId = config('app.bot_user_id');

        // Get conversation context
        $recentMessages = $messageService->getMessages($inboxId, 10);

        // Generate AI response with your AI system
        $aiResponse = $this->generateAiResponse($recentMessages);

        // Send the AI response
        $message = $messageService->sendMessage($inboxId, $botUserId, $aiResponse);

        return response()->json(['message_id' => $message->id]);
    }
}
```

### Available Methods

| Method | Description |
|--------|-------------|
| `sendMessage($inboxId, $userId, $message)` | Send a message to an inbox |
| `sendMessageToUsers($userIds, $senderId, $message, $title)` | Send message, creating inbox if needed |
| `getOrCreateInbox($userIds, $title)` | Get or create an inbox for users |
| `getMessages($inboxId, $limit)` | Get recent messages from an inbox |
| `getLatestMessage($inboxId)` | Get the most recent message |
| `getUserInboxes($userId)` | Get all inboxes for a user |
| `getUnreadCount($inboxId, $userId)` | Get unread message count |
| `markAsRead($inboxId, $userId)` | Mark messages as read |

<a name="configuration"></a>
## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-messages-config"
```

This will create `config/filament-messages.php` where you can customize:

- Navigation settings (icon, label, position)
- Attachment limits (file size, count)
- Route slug
- Page width
- Timezone
- Poll interval

<a name="plugins-used"></a>
## Plugins Used
These are [Filament Plugins](https://filamentphp.com/plugins) use for this project.

| **Plugin**                                                                                          | **Author**                                              |
| :-------------------------------------------------------------------------------------------------- | :------------------------------------------------------ |
| [Filament Spatie Media Library](https://github.com/filamentphp/spatie-laravel-media-library-plugin) | [Filament Official](https://github.com/filamentphp)     |

<a name="acknowledgments"></a>
## Acknowledgments
- [FilamentPHP](https://filamentphp.com)
- [Laravel](https://laravel.com)
- [FilaChat](https://github.com/199ocero/filachat)

<a name="support"></a>
## Support
- [Report a bug](https://github.com/jeddsaliba/filament-messages/issues)
- [Request a feature](https://github.com/jeddsaliba/filament-messages/issues)
- [Email support](mailto:jeddsaliba@gmail.com)

## Show Your Support

Give a ⭐️ if this project helped you!
