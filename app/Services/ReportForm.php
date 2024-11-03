<?php

namespace App\Services;

use App\Models\Requirement;
use App\Models\User;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

final class ReportForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Hidden::make('concourse_id')
                ->default(fn($record) => $record->concourse_id)
                ->required(),
            Forms\Components\Hidden::make('space_id')
                ->default(fn($record) => $record->id)
                ->required(),
            Forms\Components\Hidden::make('created_by')
                ->default(auth()->user()->id)
                ->required(),
            Forms\Components\TextInput::make('incident_ticket_number')
                ->default(fn() => 'INC' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT))
                ->required()
                ->readOnly(),
            Forms\Components\TextInput::make('title')
                ->required(),
            Forms\Components\Textarea::make('description')
                ->required()
                ->rows(3),
            Forms\Components\Select::make('concern_type')
                ->options([
                    'maintenance_and_repair' => 'Maintenance and Repair',
                    'safety_and_security' => 'Safety and Security',
                    'cleanliness_and_sanitation' => 'Cleanliness and Sanitation',
                    'lease_and_contractual' => 'Lease and Contractual Issues',
                    'utilities_concerns' => 'Utilities Concerns',
                    'aesthetic_and_comestics' => 'Aesthetic and Comestics Issues',
                    'general_support' => 'General Support',
                    'others' => 'Others',
                ])
                ->native(false)
                ->required(),
            Forms\Components\FileUpload::make('images')
                ->multiple(),
        ];
    }
}
