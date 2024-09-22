<?php

namespace App\Filament\Admin\Resources\TenantResource\Pages;

use App\Filament\Admin\Resources\TenantResource;
use App\Filament\Admin\Resources\TenantResource\Widgets\SpaceElectricityChart;
use App\Filament\Admin\Resources\TenantResource\Widgets\SpaceOverview;
use App\Filament\Admin\Resources\TenantResource\Widgets\SpaceWaterChart;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
           SpaceOverview::class,
           SpaceElectricityChart::class,
           SpaceWaterChart::class,
        ];
    }
  
}
