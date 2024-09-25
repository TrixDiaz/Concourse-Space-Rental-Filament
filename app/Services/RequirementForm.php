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
                ->schema([
                    Forms\Components\FileUpload::make('business_permit')
                        ->label('Business Permit')
                        ->required(),
                    Forms\Components\FileUpload::make('mayors_permit')
                        ->label("Mayor's Permit")
                        ->required(),
                    Forms\Components\FileUpload::make('bir_registration')
                        ->label('BIR Registration')
                        ->required(),
                    Forms\Components\FileUpload::make('dti_registration')
                        ->label('DTI Registration')
                        ->required(),
                    Forms\Components\FileUpload::make('sanitary_permit')
                        ->label('Sanitary Permit')
                        ->required(),
                    Forms\Components\FileUpload::make('fire_safety_inspection')
                        ->label('Fire Safety Inspection Certificate')
                        ->required(),
                    Forms\Components\FileUpload::make('valid_id')
                        ->label('Valid Government-issued ID')
                        ->required(),
                    Forms\Components\FileUpload::make('business_plan')
                        ->label('Business Plan')
                        ->required(),
                    Forms\Components\FileUpload::make('financial_statements')
                        ->label('Financial Statements')
                        ->required(),
                    Forms\Components\FileUpload::make('tax_clearance')
                        ->label('Tax Clearance')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Additional Information')
                ->schema([
                    Forms\Components\Textarea::make('business_description')
                        ->label('Business Description')
                        ->required(),
                    Forms\Components\TextInput::make('years_in_operation')
                        ->label('Years in Operation')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('number_of_employees')
                        ->label('Number of Employees')
                        ->numeric()
                        ->required(),
                    Forms\Components\Select::make('preferred_location')
                        ->label('Preferred Location')
                        ->options([
                            'ground_floor' => 'Ground Floor',
                            'second_floor' => 'Second Floor',
                            'food_court' => 'Food Court',
                            'kiosk' => 'Kiosk',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('space_size_required')
                        ->label('Space Size Required (in sq. meters)')
                        ->numeric()
                        ->required(),
                ])
                ->columns(2),

        ];
    }
}
