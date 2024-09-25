<?php

namespace App\Filament\Admin\Resources\ConcourseResource\Pages;

use App\Filament\Admin\Resources\ConcourseResource;
use App\Models\Concourse;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;

class EditConcourse extends EditRecord
{
    protected static string $resource = ConcourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewSpaces')
            ->label('View Layout')
            ->url(fn () => $this->getResource()::getUrl('view-spaces', ['record' => $this->getRecord()]))
            ->color('success'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        $record = $this->getRecord();

        $notification = Notification::make()
            ->success()
            ->icon('heroicon-o-currency-dollar')
            ->title('Concourse Updated')
            ->body("Concourse {$record->name} address in {$record->address} Updated!")
            ->actions([
                Action::make('view')
                    ->label('Mark as read')
                    ->link()
                    ->markAsRead(),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->action(fn(Notification $notification) => $notification->delete()),
            ]);

        // Get all users with the 'panel_user' or 'accountant' role
        $notifiedUsers = User::role(['panel_user'])->get();

        // Send notification to all panel users and accountants
        foreach ($notifiedUsers as $user) {
            $notification->sendToDatabase($user);
        }

        // Send notification to the authenticated user
        $notification->sendToDatabase(auth()->user());

        return $notification;
    }
}
