<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Space;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ElectricChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'electricChart';

    /**
     * Sort
     */
    protected static ?int $sort = 5;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 275;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Electric Monthly Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $data = $this->getBillData();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Space Electric Bill',
                    'type' => 'bar',
                    'data' => $data['electric'],
                ],
                [
                    'name' => 'Space Consumption (kWh)',
                    'type' => 'line',
                    'data' => $data['consumption'],
                ],
                [
                    'name' => 'Paid Electric Bill',
                    'type' => 'bar',
                    'data' => $data['paid_electric'],
                ],
                [
                    'name' => 'Paid Consumption (kWh)',
                    'type' => 'line',
                    'data' => $data['paid_consumption'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['months'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                [
                    'title' => [
                        'text' => 'Electric Bill',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
                [
                    'opposite' => true,
                    'title' => [
                        'text' => 'Consumption (kWh)',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
            ],
            'colors' => ['#f59e0b', '#3b82f6', '#dc2626', '#10b981'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }

    protected function getBillData(): array
    {
        $currentYear = date('Y');
        
        $spaceData = Space::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(electricity_bills) as total_electric'),
            DB::raw('SUM(electricity_consumption) as total_consumption')
        )   
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $paymentData = Payment::select(
            DB::raw('MONTH(updated_at) as month'),
            DB::raw('SUM(electricity_bill) as total_electric'),
            DB::raw('SUM(electricity_consumption) as total_consumption')
        )   
            ->whereYear('updated_at', $currentYear)
            ->where('payment_status', Payment::STATUS_PAID)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = array_fill(0, 12, 0);
        $electric = array_fill(0, 12, 0);
        $consumption = array_fill(0, 12, 0);
        $paid_electric = array_fill(0, 12, 0);
        $paid_consumption = array_fill(0, 12, 0);

        foreach ($spaceData as $data) {
            $monthIndex = $data->month - 1;
            $electric[$monthIndex] = round($data->total_electric, 2);
            $consumption[$monthIndex] = round($data->total_consumption, 2);
        }

        foreach ($paymentData as $data) {
            $monthIndex = $data->month - 1;
            $paid_electric[$monthIndex] = round($data->total_electric, 2);
            $paid_consumption[$monthIndex] = round($data->total_consumption, 2);
        }

        // Fill in all months
        for ($i = 0; $i < 12; $i++) {
            $months[$i] = date('M', mktime(0, 0, 0, $i + 1, 1));
        }

        return [
            'months' => array_values($months),
            'electric' => array_values($electric),
            'consumption' => array_values($consumption),
            'paid_electric' => array_values($paid_electric),
            'paid_consumption' => array_values($paid_consumption),
        ];
    }

    public function exportData()
    {
        $data = $this->getBillData();
        
        $csvContent = "Month,Space Electric,Space Consumption,Paid Electric,Paid Consumption\n";
        foreach ($data['months'] as $index => $month) {
            $csvContent .= "{$month},{$data['electric'][$index]},{$data['consumption'][$index]},{$data['paid_electric'][$index]},{$data['paid_consumption'][$index]}\n";
        }

        $fileName = 'monthly_electric_payments_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
