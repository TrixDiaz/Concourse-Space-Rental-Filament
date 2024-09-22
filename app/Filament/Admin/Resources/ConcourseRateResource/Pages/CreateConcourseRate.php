<?php

namespace App\Filament\Admin\Resources\ConcourseRateResource\Pages;

use App\Filament\Admin\Resources\ConcourseRateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateConcourseRate extends CreateRecord
{
    protected static string $resource = ConcourseRateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
