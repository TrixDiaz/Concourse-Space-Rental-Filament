<?php

namespace App\Filament\Admin\Resources\ConcourseRateResource\Pages;

use App\Filament\Admin\Resources\ConcourseRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConcourseRate extends EditRecord
{
    protected static string $resource = ConcourseRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
