<?php

namespace App\Filament\Reports;

use App\Models\Payment;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Components\VerticalSpace;
use Filament\Forms\Form;
use Illuminate\Support\Collection;

class BillsReport extends Report
{
    public ?string $heading = "Bills Report";

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {

        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Bills Report')
                                    ->title()
                                    ->primary(),
                                Text::make('Utility Bills Report')
                                    ->subtitle(),
                            ]),
                    ])
            ]);
    }


    public function body(Body $body): Body
    {
        return $body
            ->schema([       
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Text::make('Concourse Summary')
                            ->color('secondary')
                            ->title(),
                        Text::make('Detailed Space Summary')
                            ->subtitle(),
                        Body\Table::make()
                            ->data(
                                fn(?array $filters) => $this->paymentsSummary($filters)
                            ),
                        VerticalSpace::make(),
                        Body\Table::make()
                            ->data(
                                fn(?array $filters) => $this->paymentMethodSummary($filters)
                            ),
                    ]),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                Footer\Layout\FooterRow::make()
                    ->schema([
                        Footer\Layout\FooterColumn::make()
                            ->schema([
                                Text::make("Coms")
                                    ->title()
                                    ->primary(),
                                Text::make("All Rights Reserved")
                                    ->subtitle(),
                            ]),
                        Footer\Layout\FooterColumn::make()
                            ->schema([
                                Text::make("Generated on: " . now()->format('F d, Y')),
                            ])
                            ->alignRight(),
                    ]),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('concourse_id')
                    ->label('Concourse')
                    ->multiple()
                    ->options(
                        \App\Models\Concourse::query()
                            ->pluck('name', 'id')
                    )
                    ->native(false)
                    ->required(),
              
                \Filament\Forms\Components\DatePicker::make('date_from')
                    ->label('Date From')
                    ->placeholder('Start Date')
                    ->timezone('Asia/Manila')
                    ->displayFormat('Y-m-d')
                    ->maxDate(now())
                    ->native(false),
                \Filament\Forms\Components\DatePicker::make('date_to')
                    ->label('Date To')
                    ->placeholder('End Date')
                    ->timezone('Asia/Manila')
                    ->displayFormat('Y-m-d')
                    ->maxDate(now())
                    ->native(false),
            ]);
    }

    public function paymentsSummary(?array $filters): Collection
    {
        $query = Payment::query()
            ->with(['tenant', 'space.concourse']);

        if (isset($filters['concourse_id'])) {
            $query->whereHas('space.concourse', function ($query) use ($filters) {
                $query->whereIn('id', $filters['concourse_id']);
            });
        }

        $filtersApplied = false;

        if (isset($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
            $filtersApplied = true;
        }

        if (isset($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $query->where('payment_method', $filters['payment_method']);
            $filtersApplied = true;
        }

        if (isset($filters['payment_type']) && $filters['payment_type'] !== 'all') {
            $query->where('payment_type', $filters['payment_type']);
            $filtersApplied = true;
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
            $filtersApplied = true;
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
            $filtersApplied = true;
        }

        if (isset($filters['bill_types']) && !empty($filters['bill_types'])) {
            $query->where(function ($query) use ($filters) {
                foreach ($filters['bill_types'] as $billType) {
                    $query->orWhere($billType . '_bill', '>', 0);
                }
            });
            $filtersApplied = true;
        }

        if (!$filtersApplied) {
            $payments = $query->latest('created_at')->take(5)->get();
        } else {
            $payments = $query->latest('created_at')->get();
        }

        return collect([
            [
                'column1' => 'Concourse',
                'column2' => 'Space',
                'column3' => 'Tenant',
                'column4' => 'Water Usage',
                'column5' => 'Water Bill',
                'column6' => 'Electric Usage',
                'column7' => 'Electric Bill',
                'column8' => 'Unpaid Water',
                'column9' => 'Unpaid Electric',
            ]
        ])->concat($payments->map(function ($payment) {
            return [
                'column1' => $payment->space->concourse->name ?? 'N/A',
                'column2' => $payment->space->name ?? 'N/A',
                'column3' => $payment->tenant->first_name . ' ' . $payment->tenant->last_name ?? 'N/A',
                'column4' => (float)($payment->water_consumption ?? 0),
                'column5' => number_format((float)($payment->water_bill ?? 0), 2),
                'column6' => (float)($payment->electricity_consumption ?? 0),
                'column7' => number_format((float)($payment->electricity_bill ?? 0), 2),
                'column8' => number_format((float)($payment->water_due ?? 0), 2),
                'column9' => number_format((float)($payment->electricity_due ?? 0), 2),
            ];
        }));
    }

    public function paymentMethodSummary(?array $filters): Collection
    {
        $query = Payment::query();
        
        if (isset($filters['concourse_id'])) {
            $query->whereHas('space.concourse', function ($query) use ($filters) {
                $query->whereIn('id', $filters['concourse_id']);
            });
        }

        if (isset($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $paymentMethods = $query->get()->groupBy('payment_method');

        return collect([
            [
                'column1' => 'Summary',
                'column2' => 'Total',
            ]
        ])->concat(collect([
            [
                'column1' => 'Total Water Consumption',
                'column2' => (float)$query->sum('water_consumption'),
            ],
            [
                'column1' => 'Total Water Bill',
                'column2' => number_format((float)$query->sum('water_bill'), 2),
            ],
            [
                'column1' => 'Total Electric Consumption',
                'column2' => (float)$query->sum('electricity_consumption'),
            ],
            [
                'column1' => 'Total Electric Bill',
                'column2' => number_format((float)$query->sum('electricity_bill'), 2),
            ],
            [
                'column1' => 'Total Unpaid Water Bill',
                'column2' => number_format((float)$query->sum('water_due'), 2),
            ],
            [
                'column1' => 'Total Unpaid Electric Bill',
                'column2' => number_format((float)$query->sum('electricity_due'), 2),
            ],
        ]));
    }
}
