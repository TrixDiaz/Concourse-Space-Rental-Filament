<?php

namespace App\Filament\Admin\Resources\ApplicationResource\Pages;

use App\Filament\Admin\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;

class EditApplication extends EditRecord
{
   
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approveApplication')
                ->label('Approve Application')
                ->action(function () {
                    $application = $this->getRecord();
                    
                    // Update application status
                    $application->update(['status' => 'approved']);

                    // Update space status
                    $space = Space::find($application->space_id);
                    if ($space) {
                        $space->update(['status' => 'approved']);
                    }

                    // Notify the authenticated user
                    $authUser = Auth::user();
                    Notification::make()
                        ->success()
                        ->title('Application Approved')
                        ->body("You have successfully approved the application and associated space.")
                        ->sendToDatabase($authUser);

                    // Notify the application's user
                    $applicationUser = User::find($application->user_id);
                    Notification::make()
                        ->success()
                        ->title('Application Approved')
                        ->body("Your application and associated space have been approved.")
                        ->sendToDatabase($applicationUser);

                    // Show a success message in the UI
                    Notification::make()
                        ->success()
                        ->title('Application and Space Approved')
                        ->body("The application and associated space have been successfully approved and notifications sent.")
                        ->send();
                })
                ->color('success'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        $record = $this->getRecord();

        $notification = Notification::make()
            ->success()
            ->icon('heroicon-o-user-circle')
            ->title('Application Updated')
            ->body("Your Application {$record->name} Updated please review it!")
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

        // Get the selected user's ID
        $selectedUserId = $this->record->user_id;

        // Find the selected user
        $selectedUser = User::find($selectedUserId);

        if ($selectedUser) {
            // Send notification to the selected user
            $notification->sendToDatabase($selectedUser);
        }

        // Send notification to the authenticated user
        $notification->sendToDatabase(auth()->user());

        return $notification;
    }

  
}
