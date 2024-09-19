<?php

namespace App\Filament\Admin\Resources\ConcourseResource\Pages;

use App\Filament\Admin\Resources\ConcourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConcourse extends EditRecord
{
    protected static string $resource = ConcourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
