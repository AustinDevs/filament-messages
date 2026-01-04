<?php

namespace Raseldev99\FilamentMessages\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Raseldev99\FilamentMessages\Models\Inbox;
use Raseldev99\FilamentMessages\Models\Message;

class AiResponderService
{
    /**
     * Process AI response for an incoming message.
     *
     * @param Message $message The incoming message
     * @param Inbox $inbox The inbox containing the message
     * @return void
     */
    public function processAiResponse(Message $message, Inbox $inbox): void
    {
        if (!config('filament-messages.ai_responder.enabled', false)) {
            return;
        }

        // Check if the message sender is not an AI responder user
        $aiResponderUserId = config('filament-messages.ai_responder.user_id');
        if ($message->user_id == $aiResponderUserId) {
            return; // Don't respond to AI's own messages
        }

        // Check if AI should respond to this conversation
        if (!$this->shouldRespond($inbox, $message)) {
            return;
        }

        try {
            $response = $this->generateAiResponse($message, $inbox);

            if ($response) {
                $this->sendAiMessage($response, $inbox, $aiResponderUserId);
            }
        } catch (\Exception $e) {
            Log::error('AI Responder failed', [
                'inbox_id' => $inbox->id,
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine if the AI should respond to this message.
     *
     * @param Inbox $inbox
     * @param Message $message
     * @return bool
     */
    protected function shouldRespond(Inbox $inbox, Message $message): bool
    {
        $aiResponderUserId = config('filament-messages.ai_responder.user_id');

        // Check if AI user is part of the conversation
        if (!in_array($aiResponderUserId, $inbox->user_ids)) {
            return false;
        }

        // Check rate limiting
        $rateLimitMinutes = config('filament-messages.ai_responder.rate_limit_minutes', 1);
        $recentAiMessages = $inbox->messages()
            ->where('user_id', $aiResponderUserId)
            ->where('created_at', '>=', now()->subMinutes($rateLimitMinutes))
            ->count();

        if ($recentAiMessages > 0) {
            return false;
        }

        return true;
    }

    /**
     * Generate an AI response using the configured provider.
     *
     * @param Message $message
     * @param Inbox $inbox
     * @return string|null
     */
    protected function generateAiResponse(Message $message, Inbox $inbox): ?string
    {
        $provider = config('filament-messages.ai_responder.provider', 'openai');

        return match ($provider) {
            'openai' => $this->generateOpenAiResponse($message, $inbox),
            'anthropic' => $this->generateAnthropicResponse($message, $inbox),
            'custom' => $this->generateCustomResponse($message, $inbox),
            default => null,
        };
    }

    /**
     * Get conversation context for the AI.
     *
     * @param Inbox $inbox
     * @param int $limit
     * @return array
     */
    protected function getConversationContext(Inbox $inbox, int $limit = 10): array
    {
        $aiResponderUserId = config('filament-messages.ai_responder.user_id');
        $messages = $inbox->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();

        $context = [];
        foreach ($messages as $msg) {
            $role = $msg->user_id == $aiResponderUserId ? 'assistant' : 'user';
            $senderName = $msg->sender?->name ?? 'Unknown';

            $context[] = [
                'role' => $role,
                'content' => $role === 'user'
                    ? "[{$senderName}]: " . ($msg->message ?? '[Attachment]')
                    : ($msg->message ?? ''),
            ];
        }

        return $context;
    }

    /**
     * Generate response using OpenAI API.
     *
     * @param Message $message
     * @param Inbox $inbox
     * @return string|null
     */
    protected function generateOpenAiResponse(Message $message, Inbox $inbox): ?string
    {
        $apiKey = config('filament-messages.ai_responder.openai.api_key');
        if (!$apiKey) {
            Log::warning('OpenAI API key not configured for AI Responder');
            return null;
        }

        $model = config('filament-messages.ai_responder.openai.model', 'gpt-4o');
        $systemPrompt = config('filament-messages.ai_responder.system_prompt', 'You are a helpful assistant.');
        $maxTokens = config('filament-messages.ai_responder.max_tokens', 500);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$this->getConversationContext($inbox),
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => config('filament-messages.ai_responder.temperature', 0.7),
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        Log::error('OpenAI API request failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Generate response using Anthropic (Claude) API.
     *
     * @param Message $message
     * @param Inbox $inbox
     * @return string|null
     */
    protected function generateAnthropicResponse(Message $message, Inbox $inbox): ?string
    {
        $apiKey = config('filament-messages.ai_responder.anthropic.api_key');
        if (!$apiKey) {
            Log::warning('Anthropic API key not configured for AI Responder');
            return null;
        }

        $model = config('filament-messages.ai_responder.anthropic.model', 'claude-sonnet-4-20250514');
        $systemPrompt = config('filament-messages.ai_responder.system_prompt', 'You are a helpful assistant.');
        $maxTokens = config('filament-messages.ai_responder.max_tokens', 500);

        $context = $this->getConversationContext($inbox);

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'system' => $systemPrompt,
            'messages' => $context,
        ]);

        if ($response->successful()) {
            return $response->json('content.0.text');
        }

        Log::error('Anthropic API request failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Generate response using a custom callback.
     *
     * @param Message $message
     * @param Inbox $inbox
     * @return string|null
     */
    protected function generateCustomResponse(Message $message, Inbox $inbox): ?string
    {
        $callback = config('filament-messages.ai_responder.custom_callback');

        if (is_callable($callback)) {
            return $callback($message, $inbox, $this->getConversationContext($inbox));
        }

        return null;
    }

    /**
     * Send the AI-generated message.
     *
     * @param string $content
     * @param Inbox $inbox
     * @param int $aiUserId
     * @return Message
     */
    protected function sendAiMessage(string $content, Inbox $inbox, int $aiUserId): Message
    {
        $message = $inbox->messages()->create([
            'message' => $content,
            'user_id' => $aiUserId,
            'read_by' => [$aiUserId],
            'read_at' => [now()],
            'notified' => [$aiUserId],
        ]);

        $inbox->updated_at = now();
        $inbox->save();

        return $message;
    }
}
