<?php

namespace App\Services;

use Filament\Forms;
use Filament\Forms\Components\Wizard;
use App\Models\Requirement;

final class RequirementForm
{
    public static function schema(): array

    {
        return [
            Wizard::make([
                Wizard\Step::make('Tenant Information')
                    ->schema([
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('business_name')
                                ->label('Business Name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('owner_name')
                                ->label('Owner Name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('owner_email')
                                ->label('Owner Email')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('owner_contact_number')
                                ->label('Owner Contact Number')
                                ->maxLength(255)
                                ->required(),

                            Forms\Components\TextInput::make('permanent_address')
                                ->label('Permanent Address')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Forms\Components\Select::make('type')
                                ->label('Type')
                                ->options([
                                    'food' => 'Food',
                                    'non-food' => 'Non Food',
                                ])
                                ->native(false)
                                ->required(),
                        ])->columns(2),
                    ]),
                Wizard\Step::make('Attachments')
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(function () {
                                $requirements = Requirement::where('is_active', true)->get();

                                return $requirements->map(function ($requirement) {
                                    return Forms\Components\FileUpload::make("attachment_{$requirement->id}")
                                        ->label($requirement->name)
                                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                                        ->maxSize(5120) // 5MB
                                        ->disk('public')
                                        ->directory('attachments');
                                })->toArray();
                            })->columns(2),
                    ]),
                Wizard\Step::make('Agreement')
                    ->schema([
                        Forms\Components\Section::make('CONCESSION\'S APPLICATION FORM')
                            ->schema([
                                Forms\Components\Placeholder::make('form_type')
                                    ->content('RGO FORM 4 New Application')
                                    ->extraAttributes(['class' => 'text-center font-medium underline']),
                                Forms\Components\Radio::make('stall_type')


                                    ->label('Stall Type')
                                    ->options([
                                        'food' => 'Food Stall',
                                        'non-food' => 'Non-food Stall',
                                    ])
                                    ->inline()
                                    ->required(),

                                Forms\Components\Section::make('REQUIREMENTS')
                                    ->schema([
                                        Forms\Components\Checkbox::make('letter_of_intent')->label('Letter of Intent'),
                                        Forms\Components\Checkbox::make('dti_sec_registration')->label('DTI Certificate/SEC Registration'),
                                        Forms\Components\Checkbox::make('business_permit')->label('Business Permit'),
                                        Forms\Components\Checkbox::make('barangay_clearance')->label('Barangay Clearance'),
                                        Forms\Components\Checkbox::make('sanitary_permit')->label('Sanitary Permit (for food stall)'),
                                        Forms\Components\Checkbox::make('health_certification')->label('Health Certification of Personnel'),
                                        Forms\Components\Checkbox::make('proof_of_billing')->label('Proof of Billing (in the name of applicant)'),
                                        Forms\Components\Checkbox::make('government_id')->label('Photocopy of Government issued ID'),
                                        Forms\Components\Checkbox::make('organizational_chart')->label('Organizational Chart with Photo'),
                                        Forms\Components\Checkbox::make('menu_list')->label('List of Menu (for food stall)'),
                                        Forms\Components\Checkbox::make('supplies_list')->label('List of Office Supplies for sale (non-food)'),
                                        Forms\Components\Checkbox::make('services_offered')->label('Services Offered (non-food)'),
                                        Forms\Components\Checkbox::make('application_fee')->label('Business Application Fee (PHP 150.00)'),
                                    ])
                                    ->columns(1),

                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('assessed_by')
                                            ->label('Assessed by')
                                            ->placeholder('Enter your Full Name to sign the application form')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('endorsed_by')
                                            ->label('Endorsed by')
                                            ->placeholder('Director')
                                            ->disabled()
                                            ->default('Director Trix'),
                                    ])->columns(2),
                            ])

                    ]),
            ])
        ];
    }
}
