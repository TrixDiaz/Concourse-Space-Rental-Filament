<?php

namespace App\Filament\App\Pages;

use App\Mail\PaymentConfirmation;
use App\Models\Payment;
use App\Models\Space;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;
use Ixudra\Curl\Facades\Curl;
use App\Models\Tenant;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class TenantSpace extends Page implements HasForms, HasTable
{
    public $tenantId;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static string $view = 'filament.app.pages.tenant-space';

    public function table(Table $table): Table
    {
        return $table
            ->query(Space::query()
                ->where('is_active', true)
                ->where('user_id', auth()->user()->id))
            ->columns([
                Tables\Columns\TextColumn::make('concourse.name')
                    ->label('Concourse')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('space.name')
                    ->label('Space Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease_start')
                ->label('Lease Start')
                    ->date('F j, Y')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease_end')
                    ->label('Lease End')
                    ->date('F j, Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('lease_status')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable()
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('monthly_payment')
                    ->searchable()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('payment_status')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable()
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->actions([
                Tables\Actions\Action::make('payBills')
                    ->label('Pay Bills')
                    ->button()
                    ->action(fn($record) => $this->payWithGCash($record))
                    ->visible(fn($record) => $record->payment_status !== 'Paid' && $record->monthly_payment > 0),
            ])
            ->poll('3s');
    }

    protected function payWithGCash($record)
    {
        $total = $record->monthly_payment;
        $billRecord = $record->bills;

        $lineItems = [];

        foreach ($billRecord as $bill) {
            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $bill['amount'] * 100,
                'description' => $bill['name'],
                'name' => $bill['name'],
                'quantity' => 1,
            ];
        }

        $data = [
            'data' => [
                'attributes' => [
                    'line_items' => $lineItems,
                    'amount_total' => $total * 100,
                    'payment_method_types' => ['gcash'],
                    'success_url' => route('filament.app.pages.tenant-space.payment-success', ['record' => $record->id]),
                    'cancel_url' => route('filament.app.pages.tenant-space.payment-cancel'),
                    'description' => 'Payment for monthly rent',
                ],
            ],
        ];

        $response = Curl::to('https://api.paymongo.com/v1/checkout_sessions')
            ->withHeader('Content-Type: application/json')
            ->withHeader('accept: application/json')
            ->withHeader('Authorization: Basic c2tfdGVzdF9ZS1lMMnhaZWVRRDZjZ1dYWkJYZ1dHVU46')
            ->withData($data)
            ->asJson()
            ->post();

        if (isset($response->data)) {
            $checkoutSession = $response->data;
            $checkoutUrl = $checkoutSession->attributes->checkout_url;

            // Redirect the user to the GCash checkout URL
            return redirect()->away($checkoutUrl);
        } else {
            $this->notify('danger', 'Payment Failed', 'An error occurred while processing your payment. Please try again.');
            return null;
        }
    }

    protected function notify($status, $title, $message)
    {
        Notification::make()
            ->title($title)
            ->body($message)
            ->status($status)
            ->send();
    }

    protected function sendPaymentConfirmationEmail($tenant)
    {
        $user = $tenant->tenant;

        if ($user) {
            Mail::to($user->email)->send(new PaymentConfirmation($tenant, $user));
        }
    }

    public function handlePaymentSuccess($recordId)
    {
        $tenant = Space::findOrFail($recordId);
        
        // $this->sendPaymentConfirmationEmail($tenant);

        // Get the concourse associated with this space
        $concourse = $tenant->concourse;

        // Extract water and electricity bill amounts
        $waterBillAmount = 0;
        $electricityBillAmount = 0;
        foreach ($tenant->bills as $bill) {
            if ($bill['name'] === 'Water') {
                $waterBillAmount = $bill['amount'];
            } elseif ($bill['name'] === 'Electricity') {
                $electricityBillAmount = $bill['amount'];
            }
        }

        // Update concourse totals
        $concourse->total_monthly_water -= $waterBillAmount;
        $concourse->total_monthly_electricity -= $electricityBillAmount;
        $concourse->save();

        // Update tenant space
        $tenant->bills = [];
        $tenant->monthly_payment = 0;
        $tenant->payment_status = 'Paid';
        $tenant->save();

        // Create a new Payment record
        Payment::create([
            'tenant_id' => $tenant->user_id,
            'payment_type' => 'Monthly Rent',
            'payment_method' => 'GCash',
            'amount' => $tenant->monthly_payment,
            'payment_status' => 'Completed',
            'payment_details' => [
                'water' => $waterBillAmount,
                'electricity' => $electricityBillAmount,
            ],
        ]);

        $this->notify('success', 'Payment Successful', 'Your payment has been processed successfully.');
        return redirect()->route('filament.app.pages.tenant-space');
    }

    public function handlePaymentCancel()
    {
        $this->notify('warning', 'Payment Cancelled', 'Your payment has been cancelled.');
        return redirect()->route('filament.app.pages.tenant-space');
    }

    public function getTenantProperty()
    {
        return Space::with('concourse')->where('user_id', auth()->user()->id)->first();
    }
}
