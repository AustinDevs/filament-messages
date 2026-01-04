<?php

namespace Raseldev99\FilamentMessages\Livewire\Messages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schema\Components\DateTimePicker;
use Filament\Schema\Components\Section;
use Filament\Schema\Components\Select;
use Filament\Schema\Components\TagsInput;
use Filament\Schema\Components\Textarea;
use Filament\Schema\Components\TextInput;
use Filament\Schema\Components\Toggle;
use Filament\Schema\Concerns\InteractsWithSchemas;
use Filament\Schema\Contracts\HasSchemas;
use Filament\Schema\Get;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Raseldev99\FilamentMessages\Models\AutoReply;

class AutoReplySettings extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;

    public Collection $autoReplies;

    /**
     * Initialize the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->loadAutoReplies();
    }

    /**
     * Load the auto-replies for the current user.
     *
     * @return void
     */
    #[On('refresh-auto-replies')]
    public function loadAutoReplies(): void
    {
        $this->autoReplies = AutoReply::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Define the action for creating a new auto-reply.
     *
     * @return Action
     */
    public function createAutoReplyAction(): Action
    {
        return Action::make('createAutoReply')
            ->icon('heroicon-o-plus')
            ->label(__('New Auto-Reply'))
            ->form([
                Textarea::make('message')
                    ->label(__('Auto-Reply Message'))
                    ->placeholder(__('Enter your automatic reply message...'))
                    ->helperText(__('Available placeholders: {sender_name}, {recipient_name}, {date}, {time}'))
                    ->required()
                    ->rows(4)
                    ->autosize(),
                Select::make('trigger_type')
                    ->label(__('Trigger Type'))
                    ->options([
                        'all' => __('All Messages'),
                        'first_message' => __('First Message Only'),
                        'keywords' => __('Keyword Match'),
                    ])
                    ->default('all')
                    ->required()
                    ->live(),
                TagsInput::make('keywords')
                    ->label(__('Keywords'))
                    ->placeholder(__('Add keywords...'))
                    ->helperText(__('Auto-reply will trigger when any of these keywords are found'))
                    ->visible(fn (Get $get) => $get('trigger_type') === 'keywords'),
                Section::make(__('Schedule'))
                    ->description(__('Optionally schedule when the auto-reply should be active'))
                    ->collapsed()
                    ->schema([
                        DateTimePicker::make('start_at')
                            ->label(__('Start Date/Time'))
                            ->placeholder(__('Leave empty for immediate start')),
                        DateTimePicker::make('end_at')
                            ->label(__('End Date/Time'))
                            ->placeholder(__('Leave empty for no end date')),
                    ]),
                Section::make(__('Advanced Options'))
                    ->collapsed()
                    ->schema([
                        TextInput::make('reply_delay_seconds')
                            ->label(__('Reply Delay (seconds)'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(3600)
                            ->helperText(__('Delay before sending the auto-reply')),
                        Toggle::make('reply_once_per_conversation')
                            ->label(__('Reply Once Per Conversation'))
                            ->helperText(__('Only send auto-reply once per conversation'))
                            ->default(false),
                    ]),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ])
            ->modalHeading(__('Create Auto-Reply'))
            ->modalSubmitActionLabel(__('Create'))
            ->modalWidth(MaxWidth::Large)
            ->action(function (array $data) {
                AutoReply::create([
                    'user_id' => Auth::id(),
                    'message' => $data['message'],
                    'trigger_type' => $data['trigger_type'],
                    'keywords' => $data['keywords'] ?? null,
                    'start_at' => $data['start_at'] ?? null,
                    'end_at' => $data['end_at'] ?? null,
                    'reply_delay_seconds' => $data['reply_delay_seconds'] ?? 0,
                    'reply_once_per_conversation' => $data['reply_once_per_conversation'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                ]);

                Notification::make()
                    ->title(__('Auto-reply created'))
                    ->success()
                    ->send();

                $this->dispatch('refresh-auto-replies');
            });
    }

    /**
     * Define the action for editing an auto-reply.
     *
     * @return Action
     */
    public function editAutoReplyAction(): Action
    {
        return Action::make('editAutoReply')
            ->icon('heroicon-o-pencil')
            ->iconButton()
            ->color('gray')
            ->form([
                Textarea::make('message')
                    ->label(__('Auto-Reply Message'))
                    ->placeholder(__('Enter your automatic reply message...'))
                    ->helperText(__('Available placeholders: {sender_name}, {recipient_name}, {date}, {time}'))
                    ->required()
                    ->rows(4)
                    ->autosize(),
                Select::make('trigger_type')
                    ->label(__('Trigger Type'))
                    ->options([
                        'all' => __('All Messages'),
                        'first_message' => __('First Message Only'),
                        'keywords' => __('Keyword Match'),
                    ])
                    ->required()
                    ->live(),
                TagsInput::make('keywords')
                    ->label(__('Keywords'))
                    ->placeholder(__('Add keywords...'))
                    ->helperText(__('Auto-reply will trigger when any of these keywords are found'))
                    ->visible(fn (Get $get) => $get('trigger_type') === 'keywords'),
                Section::make(__('Schedule'))
                    ->description(__('Optionally schedule when the auto-reply should be active'))
                    ->collapsed()
                    ->schema([
                        DateTimePicker::make('start_at')
                            ->label(__('Start Date/Time'))
                            ->placeholder(__('Leave empty for immediate start')),
                        DateTimePicker::make('end_at')
                            ->label(__('End Date/Time'))
                            ->placeholder(__('Leave empty for no end date')),
                    ]),
                Section::make(__('Advanced Options'))
                    ->collapsed()
                    ->schema([
                        TextInput::make('reply_delay_seconds')
                            ->label(__('Reply Delay (seconds)'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(3600)
                            ->helperText(__('Delay before sending the auto-reply')),
                        Toggle::make('reply_once_per_conversation')
                            ->label(__('Reply Once Per Conversation'))
                            ->helperText(__('Only send auto-reply once per conversation'))
                            ->default(false),
                    ]),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ])
            ->fillForm(fn (array $arguments): array => [
                'message' => $arguments['message'] ?? '',
                'trigger_type' => $arguments['trigger_type'] ?? 'all',
                'keywords' => $arguments['keywords'] ?? [],
                'start_at' => $arguments['start_at'] ?? null,
                'end_at' => $arguments['end_at'] ?? null,
                'reply_delay_seconds' => $arguments['reply_delay_seconds'] ?? 0,
                'reply_once_per_conversation' => $arguments['reply_once_per_conversation'] ?? false,
                'is_active' => $arguments['is_active'] ?? true,
            ])
            ->modalHeading(__('Edit Auto-Reply'))
            ->modalSubmitActionLabel(__('Save'))
            ->modalWidth(MaxWidth::Large)
            ->action(function (array $data, array $arguments) {
                $autoReply = AutoReply::find($arguments['id']);
                if ($autoReply && $autoReply->user_id === Auth::id()) {
                    $autoReply->update([
                        'message' => $data['message'],
                        'trigger_type' => $data['trigger_type'],
                        'keywords' => $data['keywords'] ?? null,
                        'start_at' => $data['start_at'] ?? null,
                        'end_at' => $data['end_at'] ?? null,
                        'reply_delay_seconds' => $data['reply_delay_seconds'] ?? 0,
                        'reply_once_per_conversation' => $data['reply_once_per_conversation'] ?? false,
                        'is_active' => $data['is_active'] ?? true,
                    ]);

                    Notification::make()
                        ->title(__('Auto-reply updated'))
                        ->success()
                        ->send();

                    $this->dispatch('refresh-auto-replies');
                }
            });
    }

    /**
     * Define the action for deleting an auto-reply.
     *
     * @return Action
     */
    public function deleteAutoReplyAction(): Action
    {
        return Action::make('deleteAutoReply')
            ->icon('heroicon-o-trash')
            ->iconButton()
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('Delete Auto-Reply'))
            ->modalDescription(__('Are you sure you want to delete this auto-reply?'))
            ->action(function (array $arguments) {
                $autoReply = AutoReply::find($arguments['id']);
                if ($autoReply && $autoReply->user_id === Auth::id()) {
                    $autoReply->delete();

                    Notification::make()
                        ->title(__('Auto-reply deleted'))
                        ->success()
                        ->send();

                    $this->dispatch('refresh-auto-replies');
                }
            });
    }

    /**
     * Toggle the active status of an auto-reply.
     *
     * @param int $id
     * @return void
     */
    public function toggleActive(int $id): void
    {
        $autoReply = AutoReply::find($id);
        if ($autoReply && $autoReply->user_id === Auth::id()) {
            $autoReply->update(['is_active' => !$autoReply->is_active]);

            Notification::make()
                ->title($autoReply->is_active ? __('Auto-reply activated') : __('Auto-reply deactivated'))
                ->success()
                ->send();

            $this->dispatch('refresh-auto-replies');
        }
    }

    /**
     * Render the auto-reply settings view.
     *
     * @return Application|Factory|View|\Illuminate\View\View
     */
    public function render(): Application|Factory|View|\Illuminate\View\View
    {
        return view('filament-messages::livewire.messages.auto-reply-settings');
    }
}
