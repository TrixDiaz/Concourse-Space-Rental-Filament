<?php

namespace App\Filament\Admin\Resources\TenantResource\Pages;

use App\Filament\Admin\Resources\TenantResource;
use App\Filament\Admin\Resources\TenantResource\Widgets\TenantsRevenue;
use App\Filament\Admin\Resources\TenantResource\Widgets\MonthlyChart;
use App\Filament\Admin\Resources\TenantResource\Widgets\YearlyChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TenantsRevenue::class,
            MonthlyChart::class,
            YearlyChart::class,
        ];
    }
}
