<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation Properties
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        /*
        |--------------------------------------------------------------------------
        | Show Menu Item
        |--------------------------------------------------------------------------
        |
        | This setting determines whether the plugin adds a menu item to the sidebar.
        | If disabled, you can manually add a navigation item elsewhere in the panel.
        |
        */
        'show_in_menu' => true,

        /*
        |--------------------------------------------------------------------------
        | Navigation Group
        |--------------------------------------------------------------------------
        |
        | This setting defines the navigation group displayed in the sidebar.
        */
        'navigation_group' => null,

        /*
        |--------------------------------------------------------------------------
        | Navigation Label
        |--------------------------------------------------------------------------
        |
        | This setting defines the navigation label shown in the sidebar.
        */
        'navigation_label' => 'Messages',

        /*
        |--------------------------------------------------------------------------
        | Navigation Badge
        |--------------------------------------------------------------------------
        |
        | This setting determines the unread message count badge for the user in the sidebar.
        */
        'navigation_display_unread_messages_count' => true,

        /*
        |--------------------------------------------------------------------------
        | Navigation Icon
        |--------------------------------------------------------------------------
        |
        | This setting defines the navigation icon for the chat section.
        | You can customize it if your application uses a different icon.
        |
        */
        'navigation_icon' => 'heroicon-o-chat-bubble-left-right',

        /*
        |--------------------------------------------------------------------------
        | Navigation Sort
        |--------------------------------------------------------------------------
        |
        | This setting defines the sort order for the chat navigation.
        | You can customize it to match your application's preferred order.
        |
        */

        'navigation_sort' => 1,
    ],
    /*
    |--------------------------------------------------------------------------
    | Attachment Properties
    |--------------------------------------------------------------------------
    */
    'attachments' => [
        /*
        | Set the maximum/minimum file size and maximum/minimum number of files that
        | can be attached to each message.
        */
        'max_file_size' => 5120, /** Default max file size: 5mb */
        'min_file_size' => 1, /** Default min file size: 0mb */
        'max_files' => 5, /** Default max files: 10 */
        'min_files' => 0, /** Default min files: 0 */
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Slug
    |--------------------------------------------------------------------------
    |
    | This option specifies the route slug for the chat system,
    | allowing customization if your application uses a different one.
    |
    */
    'slug' => 'messages',

    /*
    |--------------------------------------------------------------------------
    | Max Content Width
    |--------------------------------------------------------------------------
    |
    | This setting defines the maximum width of the chat page,
    | which can be customized to match your application's layout.
    | You can use any enum value from \Filament\Support\Enums\MaxWidth.
    |
    */
    'max_content_width' => \Filament\Support\Enums\MaxWidth::Full,

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | This setting defines the timezone for the chat system,
    | which can be customized to match your application's timezone.
    | Refer to the supported timezones here: https://www.php.net/manual/en/timezones.php
    |
    */
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Poll Interval
    |--------------------------------------------------------------------------
    |
    | This setting determines how often the chat refreshes.
    | You can customize the interval to fit your application's needs.
    | For more details on poll intervals, visit:: https://livewire.laravel.com/docs/wire-poll
    |
    */
    'poll_interval' => '5s',

    /*
    |--------------------------------------------------------------------------
    | AI Responder Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the AI-powered automatic responder feature.
    | When enabled, an AI assistant can automatically respond to messages
    | in conversations where the AI user is a participant.
    |
    */
    'ai_responder' => [
        /*
        |--------------------------------------------------------------------------
        | Enable AI Responder
        |--------------------------------------------------------------------------
        |
        | This setting enables or disables the AI responder feature entirely.
        |
        */
        'enabled' => env('FILAMENT_MESSAGES_AI_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | AI User ID
        |--------------------------------------------------------------------------
        |
        | The user ID that represents the AI assistant in conversations.
        | Create a user in your database to represent the AI and set its ID here.
        | The AI will respond to messages in conversations where this user is included.
        |
        */
        'user_id' => env('FILAMENT_MESSAGES_AI_USER_ID'),

        /*
        |--------------------------------------------------------------------------
        | AI Provider
        |--------------------------------------------------------------------------
        |
        | The AI provider to use for generating responses.
        | Supported: 'openai', 'anthropic', 'custom'
        |
        */
        'provider' => env('FILAMENT_MESSAGES_AI_PROVIDER', 'openai'),

        /*
        |--------------------------------------------------------------------------
        | System Prompt
        |--------------------------------------------------------------------------
        |
        | The system prompt that defines the AI assistant's behavior and personality.
        | This is sent to the AI with every request to guide its responses.
        |
        */
        'system_prompt' => env('FILAMENT_MESSAGES_AI_SYSTEM_PROMPT', 'You are a helpful customer support assistant. Be friendly, professional, and concise in your responses. If you don\'t know something, say so honestly.'),

        /*
        |--------------------------------------------------------------------------
        | Max Tokens
        |--------------------------------------------------------------------------
        |
        | Maximum number of tokens for the AI response.
        |
        */
        'max_tokens' => env('FILAMENT_MESSAGES_AI_MAX_TOKENS', 500),

        /*
        |--------------------------------------------------------------------------
        | Temperature
        |--------------------------------------------------------------------------
        |
        | Controls randomness in the AI response (0.0 to 1.0).
        | Lower values make responses more focused and deterministic.
        |
        */
        'temperature' => env('FILAMENT_MESSAGES_AI_TEMPERATURE', 0.7),

        /*
        |--------------------------------------------------------------------------
        | Rate Limit (Minutes)
        |--------------------------------------------------------------------------
        |
        | Minimum time between AI responses in the same conversation.
        | Prevents the AI from responding too frequently.
        |
        */
        'rate_limit_minutes' => env('FILAMENT_MESSAGES_AI_RATE_LIMIT', 1),

        /*
        |--------------------------------------------------------------------------
        | Context Messages
        |--------------------------------------------------------------------------
        |
        | Number of previous messages to include as context for the AI.
        |
        */
        'context_messages' => env('FILAMENT_MESSAGES_AI_CONTEXT_MESSAGES', 10),

        /*
        |--------------------------------------------------------------------------
        | OpenAI Settings
        |--------------------------------------------------------------------------
        */
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('FILAMENT_MESSAGES_OPENAI_MODEL', 'gpt-4o'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Anthropic (Claude) Settings
        |--------------------------------------------------------------------------
        */
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('FILAMENT_MESSAGES_ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Custom Provider Callback
        |--------------------------------------------------------------------------
        |
        | When using 'custom' provider, specify a callback that receives
        | the message, inbox, and conversation context and returns a string response.
        |
        | Example:
        | 'custom_callback' => function ($message, $inbox, $context) {
        |     return MyAiService::generateResponse($context);
        | },
        |
        */
        'custom_callback' => null,
    ],
];
