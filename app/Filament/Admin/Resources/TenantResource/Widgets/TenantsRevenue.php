<?php

namespace App\Filament\Admin\Resources\TenantResource\Widgets;

use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantsRevenue extends BaseWidget
{
    protected function getStats(): array
    {
        // Retrieve all active tenants
        $tenants = Tenant::where('is_active', true)->get();

        // Total number of tenants
        $totalTenants = $tenants->count();

        // Count of tenants who have bills to pay (assuming bills array is not empty)
        $tenantsWithBills = $tenants->filter(function ($tenant) {
            return !empty($tenant->monthly_payment); // Checking if bills array is not empty
        })->count();

        // Calculate total sum of all bills from all tenants
        $totalBillsAmount = $tenants->sum(function ($tenant) {
            return collect($tenant->monthly_payment)->sum(); // Summing all values inside the bills array
        });

        return [
            Stat::make('Total Tenants', $totalTenants),
            Stat::make('Tenants with Bills to Pay', $tenantsWithBills),
            Stat::make('Total Bills Amount', '₱' . number_format($totalBillsAmount, 2)),
        ];
    }
}
