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
use App\Services\RenewForm;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Models\Application;
use Carbon\Carbon;

class TenantSpace extends Page implements HasForms, HasTable
{
    public $tenantId;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationLabel = 'My Space';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static string $view = 'filament.app.pages.tenant-space';

    public function getTenantSpacesProperty()
    {
        return Space::with('concourse')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();
    }

    public function getConcourseLayoutProperty()
    {
        $firstSpace = $this->tenantSpaces->first();
        return $firstSpace ? $firstSpace->concourse : null;
    }

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
                Tables\Columns\TextColumn::make('name')
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
                    ->tooltip(fn($record) => "Electricity: ₱" . number_format($record->electricity_bills, 2) . 
                                             "Water: ₱" . number_format($record->water_bills, 2) . 
                                             "Rent: ₱" . number_format($record->rent_bills, 2))
                    ->visible(fn($record) => $record->electricity_bills > 0 || $record->water_bills > 0 || $record->rent_bills > 0),
                Tables\Actions\Action::make('renew')
                    ->label('Renew Lease')
                    ->button()
                    ->slideOver()
                    ->form(fn ($record) => RenewForm::schema($record))
                    ->action(function (array $data, $record) {
                        // Get the application_id from the Space record
                        $applicationId = $record->application_id;
                        
                        // Try to find the application, including soft-deleted ones
                        $application = Application::withTrashed()->find($applicationId);

                        if ($application) {
                            // If the application exists (even if soft-deleted), restore and update it
                            $application->restore();
                            $application->update([
                                'user_id' => auth()->id(),
                                'space_id' => $record->id,
                                'concourse_id' => $record->concourse_id,
                                'business_name' => $data['business_name'] ?? null,
                                'owner_name' => $data['owner_name'] ?? null,
                                'address' => $data['address'] ?? null,
                                'phone_number' => $data['phone_number'] ?? null,
                                'email' => $data['email'] ?? null,
                                'business_type' => $data['business_type'] ?? null,
                                'requirements_status' => 'pending',
                                'application_status' => 'pending',
                                'space_type' => 'renewal',
                                'concourse_lease_term' => $data['concourse_lease_term'] ?? null,
                                'remarks' => $data['remarks'] ?? null,
                            ]);
                        } else {
                            // If no application exists, create a new one
                            $application = Application::create([
                                'user_id' => auth()->id(),
                                'space_id' => $record->id,
                                'concourse_id' => $record->concourse_id,
                                'business_name' => $data['business_name'] ?? null,
                                'owner_name' => $data['owner_name'] ?? null,
                                'address' => $data['address'] ?? null,
                                'phone_number' => $data['phone_number'] ?? null,
                                'email' => $data['email'] ?? null,
                                'business_type' => $data['business_type'] ?? null,
                                'requirements_status' => 'pending',
                                'application_status' => 'pending',
                                'space_type' => 'renewal',
                                'concourse_lease_term' => $data['concourse_lease_term'] ?? null,
                                'remarks' => $data['remarks'] ?? null,
                            ]);

                            // Update the Space with the new application_id
                            $record->update(['application_id' => $application->id]);
                        }

                        Notification::make()
                            ->title('Lease Renewal Application Submitted')
                            ->body('Your application for lease renewal has been submitted successfully.')
                            ->success()
                            ->send();
                    })
                    ->visible(function ($record) {
                        // Get current date
                        $now = Carbon::now();
                        
                        // Calculate the date 3 months before lease end
                        $threeMonthsBefore = $record->lease_end->copy()->subMonths(3);

                        // Check if current date is after or equal to 3 months before lease end
                        // and the lease end is still in the future
                        $isWithinRenewalPeriod = $now->greaterThanOrEqualTo($threeMonthsBefore) && $record->lease_end->isFuture();

                        // Check if there's an associated application
                        if ($record->application_id) {
                            // Find the application, including soft-deleted ones
                            $application = Application::withTrashed()->find($record->application_id);

                            if ($application && $application->user_id === auth()->id()) {
                                return true;
                            }
                        }

                        // Show the button if within renewal period and no matching application found
                        return $isWithinRenewalPeriod;
                    }),
            ]);
    }

    protected function payWithGCash($record)
    {
        $waterBill = $record->water_bills;
        $electricityBill = $record->electricity_bills;
        $monthlyRent = $record->rent_bills;
        $total = $waterBill + $electricityBill + $monthlyRent;

        $lineItems = [];

        if ($waterBill > 0) {
            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $waterBill * 100,
                'description' => 'Water Bill',
                'name' => 'Water Bill',
                'quantity' => 1,
            ];
        }

        if ($electricityBill > 0) {
            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $electricityBill * 100,
                'description' => 'Electricity Bill',
                'name' => 'Electricity Bill',
                'quantity' => 1,
            ];
        }

        if ($monthlyRent > 0) {
            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $monthlyRent * 100,
                'description' => 'Monthly Rent',
                'name' => 'Monthly Rent',
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
                    'description' => 'Payment for bills',
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
        
         $this->sendPaymentConfirmationEmail($tenant);

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

}
