<x-filament::modal width="xl" id="auto-reply-settings">
    <x-slot name="heading">
        <div class="flex items-center justify-between">
            <span>{{__('Auto-Reply Settings')}}</span>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            {{ $this->createAutoReply }}
        </div>

        @if($autoReplies->count() > 0)
            <div class="divide-y dark:divide-white/10">
                @foreach($autoReplies as $autoReply)
                    <div wire:key="auto-reply-{{ $autoReply->id }}" class="py-4 first:pt-0 last:pb-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    @if($autoReply->is_active)
                                        <x-filament::badge color="success">
                                            {{__('Active')}}
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="gray">
                                            {{__('Inactive')}}
                                        </x-filament::badge>
                                    @endif

                                    <x-filament::badge color="info">
                                        @switch($autoReply->trigger_type)
                                            @case('all')
                                                {{__('All Messages')}}
                                                @break
                                            @case('first_message')
                                                {{__('First Message')}}
                                                @break
                                            @case('keywords')
                                                {{__('Keywords')}}
                                                @break
                                        @endswitch
                                    </x-filament::badge>

                                    @if($autoReply->start_at || $autoReply->end_at)
                                        <x-filament::badge color="warning">
                                            {{__('Scheduled')}}
                                        </x-filament::badge>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {{ $autoReply->message }}
                                </p>

                                @if($autoReply->trigger_type === 'keywords' && !empty($autoReply->keywords))
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($autoReply->keywords as $keyword)
                                            <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 rounded">
                                                {{ $keyword }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($autoReply->start_at || $autoReply->end_at)
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                        @if($autoReply->start_at)
                                            {{__('From')}}: {{ $autoReply->start_at->format('M j, Y g:i A') }}
                                        @endif
                                        @if($autoReply->end_at)
                                            {{__('Until')}}: {{ $autoReply->end_at->format('M j, Y g:i A') }}
                                        @endif
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1">
                                <x-filament::icon-button
                                    wire:click="toggleActive({{ $autoReply->id }})"
                                    :icon="$autoReply->is_active ? 'heroicon-o-pause' : 'heroicon-o-play'"
                                    :color="$autoReply->is_active ? 'warning' : 'success'"
                                    :tooltip="$autoReply->is_active ? __('Deactivate') : __('Activate')"
                                />

                                {{ ($this->editAutoReply)(['id' => $autoReply->id, 'message' => $autoReply->message, 'trigger_type' => $autoReply->trigger_type, 'keywords' => $autoReply->keywords ?? [], 'start_at' => $autoReply->start_at, 'end_at' => $autoReply->end_at, 'reply_delay_seconds' => $autoReply->reply_delay_seconds, 'reply_once_per_conversation' => $autoReply->reply_once_per_conversation, 'is_active' => $autoReply->is_active]) }}

                                {{ ($this->deleteAutoReply)(['id' => $autoReply->id]) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8">
                <div class="p-3 mb-4 bg-gray-100 rounded-full dark:bg-gray-500/20">
                    <x-filament::icon icon="heroicon-o-chat-bubble-bottom-center-text" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                </div>
                <p class="text-base text-center text-gray-600 dark:text-gray-400">
                    {{__('No auto-replies configured')}}
                </p>
                <p class="text-sm text-center text-gray-500 dark:text-gray-500">
                    {{__('Create an auto-reply to automatically respond to messages')}}
                </p>
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament::modal>
