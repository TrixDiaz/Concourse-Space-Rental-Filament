<?php

namespace App\Filament\App\Pages;

use App\Models\Space;
use App\Models\Payment;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\ActionSize;

class BillReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.app.pages.bill-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(Space::query()->with(['user', 'payments']))
            ->columns([
                TextColumn::make('lease_start')
                    ->date()
                    ->sortable()
                    ->label('Date'),
                TextColumn::make('user.name')
                    ->searchable()
                    ->label('Tenant'),
                TextColumn::make('price')
                    ->money('PHP')
                    ->sortable()
                    ->label('Amount'),
                TextColumn::make('lease_status')
                    ->label('Status'),
                TextColumn::make('space_type')
                    ->label('Bill Types'),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('lease_start', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('lease_start', '<=', $date),
                            );
                    }),
                SelectFilter::make('lease_status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                    ])
                    ->label('Payment Status'),
                SelectFilter::make('payments.payment_method')
                    ->relationship('payments', 'payment_method')
                    ->label('Payment Method'),
            ]);
    }

    public function getPaymentSummary()
    {
        return Payment::query()
            ->selectRaw('payment_method, COUNT(*) as transactions, SUM(amount) as amount')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method,
                    'transactions' => $item->transactions,
                    'amount' => $item->amount,
                ];
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->action(fn () => $this->js('window.printReport()')),
            \Filament\Actions\Action::make('export')
                ->label('Export to Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->js('window.exportToExcel()')),
        ];
    }
}
