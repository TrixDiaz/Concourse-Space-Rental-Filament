<?php

namespace App\Filament\Admin\Resources\TicketResource\Pages;

use App\Filament\Admin\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;
use App\Models\Space;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
            Actions\Action::make('in_progress')
                ->label('Status: In Progress')
                ->icon('heroicon-o-information-circle')
                ->action(function () {
                    $this->getRecord()->update(['status' => 'in_progress']);
                })
                ->color('warning')
                ->visible(fn($record) => $record->status == 'in_progress')
                ->disabled(),
            Actions\Action::make('resolve')
                ->label('Resolve')
                ->form([
                    \Filament\Forms\Components\Textarea::make('remarks')
                        ->label('Resolution Remarks')
                        ->required()
                ])
                ->action(function (array $data) {
                    $this->getRecord()->update([
                        'status' => 'resolved',
                        'remarks' => $data['remarks']
                    ]);
                    $this->sendResolvedNotification();
                })
                ->visible(fn($record) => $record->status == 'in_progress')
                ->color('success'),
        ];
    }

    protected function getCreatedNotification(): ?Notification
    {
        $space_id = $this->getRecord()->space_id;
        $user_id = Space::find($space_id)->user_id;
        $user = User::find($user_id);
        $authUser = auth()->user();

        Notification::make()
            ->success()
            ->title('Ticket Updated')
            ->body("Ticket {$this->getRecord()->title} Updated!")
            ->sendToDatabase($user);

        return Notification::make()
            ->success()
            ->title('Ticket Updated')
            ->body("Ticket {$this->getRecord()->title} Updated!")
            ->sendToDatabase($authUser);
    }

    protected function sendResolvedNotification()
    {
        $record = $this->getRecord();
        $space_id = $record->space_id;
        $user_id = Space::find($space_id)->user_id;
        $user = User::find($user_id);
        $authUser = auth()->user();

        Notification::make()
        ->success()
        ->title('Ticket Resolved')
        ->body("Ticket {$record->title} has been resolved!")
        ->send();

        Notification::make()
            ->success()
            ->title('Ticket Resolved')
            ->body("Ticket {$record->title} has been resolved!")
            ->sendToDatabase($user);

        Notification::make()
            ->success()
            ->title('Ticket Resolved')
            ->body("Ticket {$record->title} has been resolved!")
            ->sendToDatabase($authUser);
    }
}
