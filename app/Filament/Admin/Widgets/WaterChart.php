<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Space;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class WaterChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'waterChart';
 
    /**
     * Sort
     */
    protected static ?int $sort = 6;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 275;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Water Monthly Chart';

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
                    'name' => 'Space Water Bill',
                    'type' => 'bar',
                    'data' => $data['water'],
                ],
                [
                    'name' => 'Space Consumption (m³)',
                    'type' => 'line',
                    'data' => $data['consumption'],
                ],
                [
                    'name' => 'Paid Water Bill',
                    'type' => 'bar',
                    'data' => $data['paid_water'],
                ],
                [
                    'name' => 'Paid Consumption (m³)',
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
                        'text' => 'Water Bill',
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
                        'text' => 'Consumption (m³)',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
            ],
            'colors' => ['#3b82f6', '#22c55e', '#dc2626', '#10b981'],
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
            DB::raw('MONTH(updated_at) as month'),
            DB::raw('SUM(water_bills) as total_water'),
            DB::raw('SUM(water_consumption) as total_consumption')
        )
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $paymentData = Payment::select(
            DB::raw('MONTH(updated_at) as month'),
            DB::raw('SUM(water_bill) as total_water'),
            DB::raw('SUM(water_consumption) as total_consumption')
        )
            ->whereYear('created_at', $currentYear)
            ->where('payment_status', Payment::STATUS_PAID)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = array_fill(0, 12, 0);
        $water = array_fill(0, 12, 0);
        $consumption = array_fill(0, 12, 0);
        $paid_water = array_fill(0, 12, 0);
        $paid_consumption = array_fill(0, 12, 0);

        foreach ($spaceData as $data) {
            $monthIndex = $data->month - 1;
            $water[$monthIndex] = round($data->total_water, 2);
            $consumption[$monthIndex] = round($data->total_consumption, 2);
        }

        foreach ($paymentData as $data) {
            $monthIndex = $data->month - 1;
            $paid_water[$monthIndex] = round($data->total_water, 2);
            $paid_consumption[$monthIndex] = round($data->total_consumption, 2);
        }

        for ($i = 0; $i < 12; $i++) {
            $months[$i] = date('M', mktime(0, 0, 0, $i + 1, 1));
        }

        return [
            'months' => array_values($months),
            'water' => array_values($water),
            'consumption' => array_values($consumption),
            'paid_water' => array_values($paid_water),
            'paid_consumption' => array_values($paid_consumption),
        ];
    }

    public function exportData()
    {
        $data = $this->getBillData();
        
        $csvContent = "Month,Space Water,Space Consumption,Paid Water,Paid Consumption\n";
        foreach ($data['months'] as $index => $month) {
            $csvContent .= "{$month},{$data['water'][$index]},{$data['consumption'][$index]},{$data['paid_water'][$index]},{$data['paid_consumption'][$index]}\n";
        }

        $fileName = 'monthly_water_payments_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
