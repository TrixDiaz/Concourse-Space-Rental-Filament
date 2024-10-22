<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Actions\Action;

class PaymentsChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'paymentsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Payments Chart';

    /**
     * Sort
     */
    protected static ?int $sort = 4;

    /**
     * Filter Form
     */
    protected function getFormSchema(): array
    {
        return [
            Radio::make('chartType')
                ->default('bar')
                ->options([
                    'line' => 'Line',
                    'bar' => 'Bar',
                    'area' => 'Area',
                ])
                ->inline(true)
                ->label('Type'),

            Grid::make()
                ->schema([
                    Toggle::make('chartMarkers')
                        ->default(false)
                        ->label('Markers'),

                    Toggle::make('chartGrid')
                        ->default(false)
                        ->label('Grid'),
                ]),
        ];
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $filters = $this->filterFormData;
        $data = $this->getBillData();

        return [
            'chart' => [
                'type' => $filters['chartType'],
                'height' => 300,
                'stacked' => true,
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Rent',
                    'data' => $data['rent'],
                ],
                [
                    'name' => 'Water',
                    'data' => $data['water'],
                ],
                [
                    'name' => 'Electricity',
                    'data' => $data['electricity'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['months'],
                'labels' => [
                    'style' => [
                        'fontWeight' => 400,
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Amount (₱)',
                ],
                'labels' => [
                    'style' => [
                        'fontWeight' => 400,
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'borderRadius' => 2,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'grid' => [
                'show' => $filters['chartGrid'],
            ],
            'markers' => [
                'size' => $filters['chartMarkers'] ? 3 : 0,
            ],
            'colors' => ['#f59e0b', '#3b82f6', '#10b981'],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }

    protected function getBillData(): array
    {
        $billData = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(rent_bill) as total_rent'),
            DB::raw('SUM(water_bill) as total_water'),
            DB::raw('SUM(electricity_bill) as total_electricity')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $rent = array_fill(0, 12, 0);
        $water = array_fill(0, 12, 0);
        $electricity = array_fill(0, 12, 0);

        foreach ($billData as $data) {
            $monthIndex = $data->month - 1;
            $months[$monthIndex] = date('M', mktime(0, 0, 0, $data->month, 1));
            $rent[$monthIndex] = round($data->total_rent, 2);
            $water[$monthIndex] = round($data->total_water, 2);
            $electricity[$monthIndex] = round($data->total_electricity, 2);
        }

        // Fill in any missing months
        for ($i = 0; $i < 12; $i++) {
            if (!isset($months[$i])) {
                $months[$i] = date('M', mktime(0, 0, 0, $i + 1, 1));
            }
        }

        ksort($months);

        return [
            'months' => array_values($months),
            'rent' => $rent,
            'water' => $water,
            'electricity' => $electricity,
        ];
    }

    public function getActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('exportData'),
        ];
    }

    public function exportData()
    {
        $data = $this->getBillData();
        
        $csvContent = "Month,Rent,Water,Electricity,Total\n";
        foreach ($data['months'] as $index => $month) {
            $total = $data['rent'][$index] + $data['water'][$index] + $data['electricity'][$index];
            $csvContent .= "{$month},{$data['rent'][$index]},{$data['water'][$index]},{$data['electricity'][$index]},{$total}\n";
        }

        $fileName = 'monthly_payments_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '₱' + val.toFixed(2)
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return '₱' + val.toFixed(0)
                    }
                }
            }
        }
        JS);
    }
}
