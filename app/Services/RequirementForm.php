<?php

namespace App\Services;

use App\Models\Requirement;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

final class RequirementForm
{
    public static function schema($concourseId = null, $spaceId = null): array
    {
        $user = Auth::user();

        return [
            Forms\Components\Hidden::make('user_id')
                ->default(fn() => $user->id),
            Forms\Components\Hidden::make('space_id')
                ->default($spaceId),
            Forms\Components\Hidden::make('concourse_id')
                ->default($concourseId),

            Forms\Components\Section::make('Business Information')
                ->schema([
                    Forms\Components\TextInput::make('business_name')
                        ->label('Business Name')
                        ->required(),
                    Forms\Components\TextInput::make('owner_name')
                        ->label('Owner Name')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->default(fn() => $user->email)
                        ->readOnly(),
                    Forms\Components\TextInput::make('phone_number')
                        ->label('Phone Number')
                        ->default(fn() => $user->phone_number)
                        ->readOnly()
                        ->required(),
                    Forms\Components\TextInput::make('address')
                        ->label('Permanent Address')
                        ->default(fn() => $user->address)
                        ->columnSpanFull()
                        ->required(),
                    Forms\Components\Select::make('business_type')
                        ->label('Business Type')
                        ->options([
                            'food' => 'Food',
                            'non-food' => 'Non Food',
                            'other' => 'Other',
                        ])
                        ->native(false),
                    Forms\Components\DatePicker::make('expiration_date')
                        ->label('Lease Agreement Date')
                        ->native(false),
                ])
                ->columns(2),

            Forms\Components\Section::make('Requirements')
                ->schema(function () {
                    $requirements = Requirement::all();
                    return $requirements->map(function ($requirement) {
                        return Forms\Components\FileUpload::make("requirements.{$requirement->id}")
                            ->label($requirement->name)
                            ->disk('public')
                            ->directory('requirements');
                    })->toArray();
                })
                ->columns(2),
        ];
    }
}
