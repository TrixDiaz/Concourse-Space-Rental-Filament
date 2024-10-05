<?php

namespace App\Filament\Admin\Resources\ApplicationResource\Pages;

use App\Filament\Admin\Resources\ApplicationResource;
use App\Mail\LeaseContractMail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;
use App\Models\Space;
use App\Models\ApprovedApplication;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EditApplication extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approveApplication')
                ->label('Approve Application')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    $application = $this->getRecord();

                    DB::transaction(function () use ($application) {
                        // Store the application data in ApprovedApplication table
                        ApprovedApplication::create($application->toArray());

                        // Update space status
                        $space = Space::find($application->space_id);
                        if ($space) {
                            $space->update(['status' => 'occupied']);
                        }

                        // Delete the application from the Application table
                        $application->delete();

                        // Notify the authenticated user
                        $authUser = Auth::user();
                        Notification::make()
                            ->success()
                            ->title('Application Approved')
                            ->body("You successfully approved the application and associated space.")
                            ->actions([
                                Action::make('view')
                                    ->button()
                                    ->url(route('filament.admin.resources.applications.index')),
                            ])
                            ->sendToDatabase($authUser);

                        // Notify the application's user
                        $applicationUser = User::find($application->user_id);
                        Notification::make()
                            ->success()
                            ->title('Application Approved')
                            ->body("Your application and associated space have been approved.")
                            ->sendToDatabase($applicationUser);

                        // Create a new Tenant instance
                        $space = Space::find($application->space_id);
                        $space->update([
                            'user_id' => $application->user_id,
                            'concourse_id' => $application->concourse_id,
                            'lease_start' => $application->created_at,
                            'lease_due' => Carbon::parse($application->created_at)->addMonths(1),
                            'lease_end' => Carbon::parse($application->created_at)->addMonths($application->lease_term),
                            'lease_term' => $application->concourse_lease_term,
                            'lease_status' => 'active',
                            'bills' => $application->bills ? json_encode($application->bills) : null,
                            'monthly_payment' => $application->monthly_payment ? $application->monthly_payment : 0,
                            'payment_status' => 'Paid',
                            'is_active' => true,
                        ]);

                        // Send lease contract email
                        // $this->sendLeaseContractEmail($tenant, $application);

                        // Show a success message in the UI
                        Notification::make()
                            ->success()
                            ->title('Application and Space Approved')
                            ->body("The application and associated space have been successfully approved and notifications sent.")
                            ->send();

                        // Redirect to the list view after approval
                        return redirect()->route('filament.admin.resources.applications.index');
                    });

                    // ... existing code ...
                })
                ->color('success')
                ->requiresConfirmation(),
            Actions\DeleteAction::make(),
        ];
    }

    private function sendLeaseContractEmail(Tenant $tenant, $application)
    {
        $owner = Auth::user();
        $tenantUser = User::find($tenant->tenant_id);
        $space = Space::find($tenant->space_id);

        Mail::to($tenantUser->email)->send(new LeaseContractMail($owner, $tenantUser, $tenant, $space, $application));
    }

    protected function getSavedNotification(): ?Notification
    {
        $record = $this->getRecord();

        // Check if the application status is 'approved'
        if ($record->status === 'approved') {
            return null; // Don't send any notification
        }

        $authUser = auth()->user();

        // Notification for the authenticated user
        $authNotification = Notification::make()
            ->success()
            ->icon('heroicon-o-document-text')
            ->title('Application Updated')
            ->body("Application {$record->name} has been updated.")
            ->actions([
                Action::make('markAsRead')
                    ->label('Mark as read')
                    ->button()
                    ->markAsRead(),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->action(fn(Notification $notification) => $notification->delete()),
            ])
            ->sendToDatabase($authUser);

        // Notification for the application owner (if different from auth user)
        $selectedUser = User::find($record->user_id);
        if ($selectedUser && $selectedUser->id !== $authUser->id) {
            $url = route('filament.app.pages.edit-requirement', [
                'concourse_id' => $record->concourse_id,
                'space_id' => $record->space_id,
                'user_id' => $record->user_id,
            ]);

            Notification::make()
                ->success()
                ->icon('heroicon-o-user-circle')
                ->title('Application Updated')
                ->body("Application {$record->name} Updated. Please review it!")
                ->actions([
                    Action::make('view')
                        ->label('View Application')
                        ->button()
                        ->url($url),
                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->action(fn(Notification $notification) => $notification->delete()),
                ])
                ->sendToDatabase($selectedUser);
        }

        return $authNotification;
    }
}
