<?php

namespace App\Filament\App\Resources\TenantSpaceResource\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WaterMonthlyBills extends ApexChartWidget
{
    protected static ?string $chartId = 'waterMonthlyBills';
    protected static ?string $heading = 'Water Monthly Bills';

    protected function getOptions(): array
    {
        $waterBills = $this->getWaterBills();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Water Bills',
                    'data' => array_values($waterBills['data']),
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($waterBills['data']),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'title' => [
                'text' => $waterBills['debug'],
                'align' => 'center',
            ],
        ];
    }

    private function getWaterBills(): array
    {
        $userId = Auth::id();
        $currentYear = Carbon::now()->year;

        // Debugging information
        $debug = "Year: {$currentYear}";

        $payments = Payment::where('tenant_id', $userId)
            ->whereYear('created_at', $currentYear)
            ->get();

        $debug .= ", Payments found: " . $payments->count();

        $waterBills = $payments->map(function ($payment) {
            $details = $payment->payment_details;
            // Check if $details is already an array
            if (!is_array($details)) {
                $details = json_decode($details, true);
            }
            $waterBill = $details['water_bill'] ?? 0;
            return [
                'month' => Carbon::parse($payment->created_at)->format('M'),
                'amount' => floatval($waterBill),
            ];
        })
        ->groupBy('month')
        ->map(function ($group) {
            return $group->sum('amount');
        });

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $result = collect($months)->mapWithKeys(function ($month) use ($waterBills) {
            return [$month => $waterBills->get($month, 0)];
        })->toArray();

        // Log debugging information
        Log::info('WaterMonthlyBills Debug: ' . $debug);

        return [
            'data' => $result,
            'debug' => $debug,
        ];
    }
}