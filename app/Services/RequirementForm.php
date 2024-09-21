<?php

namespace App\Services;

use Filament\Forms;

final class RequirementForm
{
    public static function schema(): array

    {
        return [
            Forms\Components\Section::make('Business Information')
                ->schema([
                    Forms\Components\TextInput::make('business_name')
                        ->label('Business Name'),
                    Forms\Components\TextInput::make('owner_name')
                        ->label('Owner Name'),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email(),
                    Forms\Components\TextInput::make('phone_number')
                        ->label('Phone Number'),
                    Forms\Components\TextInput::make('permanent_address')
                        ->label('Permanent Address')
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
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\FileUpload::make('requirements')
                                ->label('Requirements')
                                ->columnSpanFull(),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'rejected' => 'Rejected',
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    're-upload' => 'Re-Upload',
                                ])
                                ->native(false),
                        ])->columnSpanFull(),


                ])
                ->columns(2),






        ];
    }
}
