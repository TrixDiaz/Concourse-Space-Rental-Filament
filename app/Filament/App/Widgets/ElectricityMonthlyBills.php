<?php

namespace App\Filament\App\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ElectricityMonthlyBills extends ApexChartWidget
{
    protected static ?string $chartId = 'electricityMonthlyBills';
    protected static ?string $heading = 'Electricity Monthly Bills';

    protected function getOptions(): array
    {
        $electricityBills = $this->getElectricityBills();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Electricity Bills',
                    'data' => array_values($electricityBills['data']),
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($electricityBills['data']),
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
            'stroke' => [
                'curve' => 'smooth',
            ],
            'title' => [
                'text' => $electricityBills['debug'],
                'align' => 'center',
            ],
        ];
    }

    private function getElectricityBills(): array
    {
        $userId = Auth::id();
        $currentYear = Carbon::now()->year;

        // Debugging information
        $debug = "Year: {$currentYear}";

        $payments = Payment::where('tenant_id', $userId)
            ->whereYear('created_at', $currentYear)
            ->get();

        $debug .= ", Payments found: " . $payments->count();

        $electricityBills = $payments->map(function ($payment) {
            $details = $payment->payment_details;
            // Check if $details is already an array
            if (!is_array($details)) {
                $details = json_decode($details, true);
            }
            $electricityBill = $details['electricity_bill'] ?? 0;
            return [
                'month' => Carbon::parse($payment->created_at)->format('M'),
                'amount' => floatval($electricityBill),
            ];
        })
        ->groupBy('month')
        ->map(function ($group) {
            return $group->sum('amount');
        });

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $result = collect($months)->mapWithKeys(function ($month) use ($electricityBills) {
            return [$month => $electricityBills->get($month, 0)];
        })->toArray();

        // Log debugging information
        Log::info('ElectricityMonthlyBills Debug: ' . $debug);

        return [
            'data' => $result,
            'debug' => $debug,
        ];
    }
}