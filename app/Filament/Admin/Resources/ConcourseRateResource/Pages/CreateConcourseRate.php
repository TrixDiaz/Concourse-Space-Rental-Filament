<?php

namespace App\Filament\Admin\Resources\ConcourseRateResource\Pages;

use App\Filament\Admin\Resources\ConcourseRateResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateConcourseRate extends CreateRecord
{
    protected static string $resource = ConcourseRateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        $notification = Notification::make()
            ->success()
            ->icon('heroicon-o-finger-print')
            ->title('Rate Created')
            ->body("New Rate Created!")
            ->sendToDatabase(User::user());

        return $notification;
    }
}
