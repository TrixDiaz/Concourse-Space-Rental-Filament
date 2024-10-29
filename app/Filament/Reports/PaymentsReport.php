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

class PaymentsReport extends Report
{
    public ?string $heading = "Bills Report";

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        $concourse = null;
        if (isset($this->filters['concourse_id'])) {
            $concourse = \App\Models\Concourse::find($this->filters['concourse_id']);
        }

        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make($concourse ? $concourse->name : 'All Concourses')
                                    ->title()
                                    ->primary(),
                                Text::make($concourse ? $concourse->address : '')
                                    ->subtitle(),
                                Text::make('Utility Bills Report')
                                    ->subtitle(),
                            ]),
                    ])
            ]);
    }


    public function body(Body $body): Body
    {
        $concourse = null;
        if (isset($this->filters['concourse_id'])) {
            $concourse = \App\Models\Concourse::find($this->filters['concourse_id']);
        }
        return $body
            ->schema([       
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Text::make($concourse ? 'Concourse ' . $concourse->name . ' Report' : 'All Concourses Report')
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
                    ->options(
                        \App\Models\Concourse::query()
                            ->pluck('name', 'id')
                    )
                    ->native(false)
                    ->required(),
                \Filament\Forms\Components\TextInput::make('search')
                    ->placeholder('Search')
                    ->autofocus(),
                \Filament\Forms\Components\Select::make('payment_status')
                    ->label('Payment Status')
                    ->native(false)
                    ->options([
                        'all' => 'All',
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                        'failed' => 'Failed',
                    ]),
                \Filament\Forms\Components\Select::make('payment_method')
                    ->label('Payment Method')
                    ->native(false)
                    ->options([
                        'all' => 'All',
                        'gcash' => 'Gcash',
                    ]),
                \Filament\Forms\Components\Select::make('payment_type')
                    ->label('Payment Type')
                    ->native(false)
                    ->options([
                        'all' => 'All',
                        'cash' => 'Cash',
                        'e-wallet' => 'E-Wallet',
                    ]),
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
                \Filament\Forms\Components\Select::make('bill_types')
                    ->label('Bill Types')
                    ->multiple()
                    ->native(false)
                    ->options([
                        'electricity' => 'Electricity',
                        'water' => 'Water',
                        'rent' => 'Rent',
                    ]),
                    \Filament\Forms\Components\Actions::make([
                        \Filament\Forms\Components\Actions\Action::make('reset')
                            ->label('Reset Filters')
                            ->color('danger')
                            ->action(function (Form $form) {
                                $form->fill([
                                    'search' => null,
                                    'payment_status' => null,
                                    'payment_method' => null,
                                    'payment_type' => null,
                                    'date_from' => null,
                                    'date_to' => null,
                                ]);
                            })
                    ]),
            ]);
    }

    public function paymentsSummary(?array $filters): Collection
    {
        $query = Payment::query()
            ->with(['tenant', 'space.concourse']);

        if (isset($filters['concourse_id'])) {
            $query->whereHas('space.concourse', function ($query) use ($filters) {
                $query->where('id', $filters['concourse_id']);
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
                'column1' => 'Space',
                'column2' => 'Tenant',
                'column3' => 'Water Usage',
                'column4' => 'Water Bill',
                'column5' => 'Electric Usage',
                'column6' => 'Electric Bill',
                'column7' => 'Unpaid Water',
                'column8' => 'Unpaid Electric',
            ]
        ])->concat($payments->map(function ($payment) {
            return [
                'column1' => $payment->space->name ?? 'N/A',
                'column2' => $payment->tenant->first_name . ' ' . $payment->tenant->last_name ?? 'N/A',
                'column3' => (float)($payment->water_consumption ?? 0),
                'column4' => number_format((float)($payment->water_bill ?? 0), 2),
                'column5' => (float)($payment->electricity_consumption ?? 0),
                'column6' => number_format((float)($payment->electricity_bill ?? 0), 2),
                'column7' => number_format((float)($payment->water_due ?? 0), 2),
                'column8' => number_format((float)($payment->electricity_due ?? 0), 2),
            ];
        }));
    }

    public function paymentMethodSummary(?array $filters): Collection
    {
        $query = Payment::query();
        
        if (isset($filters['concourse_id'])) {
            $query->whereHas('space.concourse', function ($query) use ($filters) {
                $query->where('id', $filters['concourse_id']);
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
