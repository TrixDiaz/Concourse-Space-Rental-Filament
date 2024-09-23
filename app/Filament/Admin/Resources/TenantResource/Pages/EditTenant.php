<?php

namespace App\Filament\Admin\Resources\TenantResource\Pages;

use App\Filament\Admin\Resources\TenantResource;
use App\Filament\Admin\Resources\TenantResource\Widgets\SpaceElectricityChart;
use App\Filament\Admin\Resources\TenantResource\Widgets\SpaceOverview;
use App\Filament\Admin\Resources\TenantResource\Widgets\SpaceWaterChart;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SpaceOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SpaceElectricityChart::class,
            SpaceWaterChart::class,
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        $record = $this->getRecord();

        $notification = Notification::make()
            ->success()
            ->icon('heroicon-o-user-circle')
            ->title('Tenant Updated')
            ->body("Tenant {$record->name} Updated!")
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

        // Assuming we have a method to get the selected user's ID
        $selectedUserId = $this->getSelectedUserId();

        // Find the selected user
        $selectedUser = User::find($selectedUserId);

        if ($selectedUser) {
            // Send notification to the selected user
        }

        // Send notification to the authenticated user
        $notification->sendToDatabase(auth()->user());

        return $notification;
    }

    // Add this method to get the selected user's ID
    protected function getSelectedUserId(): ?int
    {
        // Implement the logic to get the selected user's ID
        // This could be from a form field, a request parameter, or any other source
        // For now, we'll return null as a
    }

}
