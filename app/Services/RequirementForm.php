<?php

namespace App\Services;

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
                        ->label('Business Name'),
                    Forms\Components\TextInput::make('owner_name')
                        ->label('Owner Name'),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->default(fn() => $user->email)
                        ->readOnly(),
                    Forms\Components\TextInput::make('phone_number')
                        ->label('Phone Number')

                        ->default(fn() => $user->phone_number)
                        ->readOnly(),
                    Forms\Components\TextInput::make('address')
                        ->label('Permanent Address')

                        ->default(fn() => $user->address)
                        ->columnSpanFull(),
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
                    Forms\Components\Repeater::make('requirements')
                        ->schema([
                            Forms\Components\TextInput::make('name'),
                            Forms\Components\FileUpload::make('attachment')
                                ->image()
                                ->label('Attachment')
                                ->maxSize(5120)
                                ->imageEditor()
                                ->openable()
                                ->downloadable()
                                ->preserveFilenames()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('status')
                                ->default('pending')
                                ->hidden(),
                        ])->columnSpanFull(),

                ])
                ->columns(2),

        ];
    }
}
