<?php

namespace App\Filament\Admin\Resources\ConcourseResource\Pages;

use App\Filament\Admin\Resources\ConcourseResource;
use App\Filament\Admin\Resources\ConcourseResource\Widgets\SpaceOverview;
use App\Models\Concourse;
use App\Models\ConcourseRate;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditConcourse extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SpaceOverview::class,
        ];
    }

    protected static string $resource = ConcourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewSpaces')
                ->label('View Layout')
                ->url(fn() => $this->getResource()::getUrl('view-spaces', ['record' => $this->getRecord()]))
                ->color('success'),
            Actions\Action::make('notifySpaces')
                ->label('Notify Spaces')
                ->action(function () {
                    $this->notifySpacesAboutBills();
                })
                ->color('warning')
                ->icon('heroicon-o-bell')
                ->visible(fn () => $this->hasSpacesWithBills())
                ->requiresConfirmation(),
            // Actions\DeleteAction::make(),
        ];
    }

    protected function hasSpacesWithBills(): bool
    {
        $concourse = $this->getRecord();
        $spacesCount = $concourse->spaces()->count();
        $spacesWithValidBills = $concourse->spaces()
            ->where(function ($query) {
                $query->whereRaw("JSON_CONTAINS(bills, '{\"name\": \"Water\"}', '$')")
                    ->whereRaw("JSON_CONTAINS(bills, '{\"name\": \"Electricity\"}', '$')")
                    ->whereNotNull('bills')
                    ->where('bills', '!=', '[]');
            })
            ->count();

        return $spacesCount > 0 && $spacesCount === $spacesWithValidBills;
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $concourse = parent::handleRecordUpdate($record, $data);

        if ($concourse instanceof Concourse && $concourse->wasChanged('rate_id')) {
            $this->updateSpacePrices($concourse);
        }

        return $concourse;
    }

    protected function updateSpacePrices(Concourse $concourse): void
    {
        $rate = ConcourseRate::find($concourse->rate_id);

        if ($rate) {
            $concourse->spaces()->each(function ($space) use ($rate) {
                $spacePrice = $rate->price * $space->sqm;
                $space->update(['price' => $spacePrice]);
            });
        }
    }

    protected function notifySpacesAboutBills(): void
    {
        $concourse = $this->getRecord();
        $spaces = $concourse->spaces()->where('is_active', true)->where('deleted_at', '=', null)->where('user_id', '!=', null)->get();
        // dd($spaces);
        foreach ($spaces as $space) {
            $notification = Notification::make()
                ->warning()
                ->title('Monthly Bill Available')
                ->body("Your monthly bill for space {$space->name} in {$concourse->name} is now available for review.");

            // Send notification to the space owner or associated user
            $spaceUser = User::find($space->user_id);
            if ($spaceUser) {
                $notification->sendToDatabase($spaceUser);
            }
        }

        Notification::make()
            ->success()
            ->title('Notifications Sent')
            ->body('All spaces have been notified about their monthly bills.')
            ->send();
    }
}
