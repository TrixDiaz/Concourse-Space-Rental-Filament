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
    public ?string $heading = "Payments Report";

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Payments Report')
                                    ->title(),
                                Text::make('This report shows payments in the system')
                                    ->subtitle(),
                            ]),
                        Header\Layout\HeaderColumn::make()
                            // ->schema([
                            //     Text::make(now()->format('F, d Y'))
                            //         ->subtitle(),
                            // ])->alignRight(),
                    ])
            ]);
    }


    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema([
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
        $query = Payment::query();

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
                'column1' => 'Date',
                'column2' => 'Tenant',
                'column3' => 'Amount',
                'column4' => 'Payment Method',
                'column5' => 'Status',
                'column6' => 'Bill Types',
            ]
        ])->concat($payments->map(function ($payment) use ($filters) {
            $billTypes = isset($filters['bill_types']) ? implode(', ', array_map('ucfirst', $filters['bill_types'])) : 'All';
            return [
                'column1' => $payment->created_at->format('F d, Y'),
                'column2' => $payment->tenant->name,
                'column3' => number_format($payment->amount, 2),
                'column4' => $payment->payment_method,
                'column5' => $payment->payment_status,
                'column6' => $billTypes,
            ];
        }));
    }

    public function paymentMethodSummary(?array $filters): Collection
    {
        $query = Payment::query();

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
                'column1' => 'Payment Method',
                'column2' => 'Total Transactions',
                'column3' => 'Total Amount',
            ]
        ])->concat($paymentMethods->map(function ($payments, $method) {
            return [
                'column1' => $method,
                'column2' => $payments->count(),
                'column3' => number_format($payments->sum('amount'), 2),
            ];
        }));
    }
}
