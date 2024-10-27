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
    protected static ?string $heading = 'Rent Monthly Payments Chart';

    /**
     * Sort
     */
    protected static ?int $sort = 4;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 275;

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
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Rent',
                    'data' => $data['rent'],
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
            'colors' => ['#f59e0b'],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }

    protected function getBillData(): array
    {
        $currentYear = date('Y');
        
        $billData = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(rent_bill) as total_rent')
        )
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = array_fill(0, 12, 0);
        $rent = array_fill(0, 12, 0);

        foreach ($billData as $data) {
            $monthIndex = $data->month - 1;
            $rent[$monthIndex] = round($data->total_rent, 2);
        }

        // Fill in all months
        for ($i = 0; $i < 12; $i++) {
            $months[$i] = date('M', mktime(0, 0, 0, $i + 1, 1));
        }

        return [
            'months' => array_values($months),
            'rent' => array_values($rent),
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
        
        $csvContent = "Month,Rent\n";
        foreach ($data['months'] as $index => $month) {
            $csvContent .= "{$month},{$data['rent'][$index]}\n";
        }

        $fileName = 'monthly_rent_payments_' . date('Y-m-d') . '.csv';

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
