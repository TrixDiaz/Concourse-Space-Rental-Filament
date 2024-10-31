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
use Filament\Forms\Components\Checkbox;
use App\Models\User;

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
                    ->description(fn($record) => $record->name)
                    ->searchable(),
                Tables\Columns\TextColumn::make('Contract')
                    ->label('Contract')
                    ->default(fn($record) => 'Start: ' . $record->lease_start->format('F j, Y'))
                    ->description(fn($record) => 'End: ' . $record->lease_end->format('F j, Y')),
                Tables\Columns\TextColumn::make('Rent Bills')
                    ->label('Rent Bills')
                    ->default(fn($record) => $record->rent_bills > 0 ? '₱' . number_format($record->rent_bills, 2) : 'N/A')
                    ->description(fn($record) => $record->rent_payment_status == 'paid' ? '' : 'Unpaid'),
                Tables\Columns\TextColumn::make('Water Bills')
                    ->label('Water Bills')
                    ->default(fn($record) => $record->water_bills > 0 ? '₱' . number_format($record->water_bills, 2) : 'N/A')
                    ->description(fn($record) => $record->water_payment_status == 'paid' ? '' : 'Unpaid'),
                Tables\Columns\TextColumn::make('Electricity Bills')
                    ->label('Electricity Bills')
                    ->default(fn($record) => $record->electricity_bills > 0 ? '₱' . number_format($record->electricity_bills, 2) : 'N/A')
                    ->description(fn($record) => $record->electricity_payment_status == 'paid' ? '' : 'Unpaid'),
            ])
            ->actions([

                Tables\Actions\Action::make('renew')
                    ->label('Renew Lease')
                    ->button()
                    ->slideOver()
                    ->form(fn($record) => RenewForm::schema($record))
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

                        // Check if lease_end is null
                        if (!$record->lease_end) {
                            return false;
                        }

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
                Tables\Actions\Action::make('payBills')
                    ->label('Pay Bills')
                    ->button()
                    ->action(fn($record, array $data) => $this->payWithGCash($record, $data))
                    ->form(function ($record) {
                        $checkboxes = [];
                        $now = now();

                        if ($record->water_bills > 0) {
                            $waterDue = Carbon::parse($record->water_due);
                            $penalty = $now->gt($waterDue) ? ($record->water_bills * 0.02) : 0;
                            $totalWater = $record->water_bills + $penalty;

                            $label = "Water Bill: ₱" . number_format($record->water_bills, 2);
                            $label .= "\n*********************************** Due: " . $waterDue->format('F j, Y');
                            if ($penalty > 0) {
                                $label .= "\n2% Penalty: ₱" . number_format($penalty, 2);
                                $label .= "\nTotal Amount Due: ₱" . number_format($totalWater, 2);
                            }

                            $checkboxes[] = Checkbox::make('pay_water')
                                ->label($label)
                                ->default(true);
                        }

                        if ($record->electricity_bills > 0) {
                            $electricityDue = Carbon::parse($record->electricity_due);
                            $penalty = $now->gt($electricityDue) ? ($record->electricity_bills * 0.02) : 0;
                            $totalElectricity = $record->electricity_bills + $penalty;

                            $label = "Electricity Bill: ₱" . number_format($record->electricity_bills, 2);
                            $label .= "\n*********************************** Due: " . $electricityDue->format('F j, Y');
                            if ($penalty > 0) {
                                $label .= "\n2% Penalty: ₱" . number_format($penalty, 2);
                                $label .= "\nTotal Amount Due: ₱" . number_format($totalElectricity, 2);
                            }

                            $checkboxes[] = Checkbox::make('pay_electricity')
                                ->label($label)
                                ->default(true);
                        }

                        if ($record->rent_bills > 0) {
                            $rentDue = Carbon::parse($record->rent_due);
                            $penalty = $now->gt($rentDue) ? ($record->rent_bills * 0.02) : 0;
                            $totalRent = $record->rent_bills + $penalty;

                            $label = "Rent: ₱" . number_format($record->rent_bills, 2);
                            $label .= "\n*********************************** Due: " . $rentDue->format('F j, Y');
                            if ($penalty > 0) {
                                $label .= "\n2% Penalty: ₱" . number_format($penalty, 2);
                                $label .= "\nTotal Amount Due: ₱" . number_format($totalRent, 2);
                            }

                            $checkboxes[] = Checkbox::make('pay_rent')
                                ->label($label)
                                ->default(true);
                        }

                        return $checkboxes;
                    })
                    ->visible(fn($record) => $record->electricity_bills > 0 || $record->water_bills > 0 || $record->rent_bills > 0),
            ]);
    }

    protected function payWithGCash($record, $data)
    {
        $lineItems = [];
        $totalAmount = 0;
        $description = "Payment for: ";
        $now = now();
        $dueData = [];

        if (isset($data['pay_water']) && $data['pay_water']) {
            $waterDue = Carbon::parse($record->water_due);
            $penalty = $now->gt($waterDue) ? ($record->water_bills * 0.02) : 0;
            $totalWater = $record->water_bills + $penalty;

            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $totalWater * 100,
                'description' => 'Water Bill' . ($penalty > 0 ? ' + 2% Penalty' : ''),
                'name' => 'Water Bill',
                'quantity' => 1,
            ];
            $totalAmount += $totalWater;
            $description .= "Water Bill" . ($penalty > 0 ? " (incl. ₱" . number_format($penalty, 2) . " penalty), " : ", ");

            // Check if payment is late
            if ($record->water_due && $waterDue->isPast()) {
                $dueData['water_due'] = $record->water_due;
                $dueData['paid_late'] = $now;
            }
        }

        if (isset($data['pay_electricity']) && $data['pay_electricity']) {
            $electricityDue = Carbon::parse($record->electricity_due);
            $penalty = $now->gt($electricityDue) ? ($record->electricity_bills * 0.02) : 0;
            $totalElectricity = $record->electricity_bills + $penalty;

            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $totalElectricity * 100,
                'description' => 'Electricity Bill' . ($penalty > 0 ? ' + 2% Penalty' : ''),
                'name' => 'Electricity Bill',
                'quantity' => 1,
            ];
            $totalAmount += $totalElectricity;
            $description .= "Electricity Bill" . ($penalty > 0 ? " (incl. ₱" . number_format($penalty, 2) . " penalty), " : ", ");

            // Check if payment is late
            if ($record->electricity_due && $electricityDue->isPast()) {
                $dueData['electricity_due'] = $record->electricity_due;
                if (!isset($dueData['paid_late'])) {
                    $dueData['paid_late'] = $now;
                }
            }
        }

        if (isset($data['pay_rent']) && $data['pay_rent']) {
            $rentDue = Carbon::parse($record->rent_due);
            $penalty = $now->gt($rentDue) ? ($record->rent_bills * 0.02) : 0;
            $totalRent = $record->rent_bills + $penalty;

            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $totalRent * 100,
                'description' => 'Monthly Rent' . ($penalty > 0 ? ' + 2% Penalty' : ''),
                'name' => 'Monthly Rent',
                'quantity' => 1,
            ];
            $totalAmount += $totalRent;
            $description .= "Monthly Rent" . ($penalty > 0 ? " (incl. ₱" . number_format($penalty, 2) . " penalty), " : ", ");

            // Check if payment is late
            if ($record->rent_due && $rentDue->isPast()) {
                $dueData['rent_due'] = $record->rent_due;
                if (!isset($dueData['paid_late'])) {
                    $dueData['paid_late'] = $now;
                }
            }
        }

        // Remove trailing comma and space from description
        $description = rtrim($description, ", ");

        // Store due data in session for later use
        session(['payment_due_data' => $dueData]);

        // Get authenticated user details
        $user = auth()->user();

        $sessionData = [
            'data' => [
                'attributes' => [
                    'line_items' => $lineItems,
                    'amount_total' => $totalAmount * 100,
                    'payment_method_types' => ['gcash'],
                    'success_url' => route('filament.app.pages.tenant-space.payment-success', ['record' => $record->id]),
                    'cancel_url' => route('filament.app.pages.tenant-space.payment-cancel'),
                    'description' => $description,
                    'customer' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone_number ?? '',
                    ],
                    'billing' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone_number ?? '',
                    ],
                ],
            ],
        ];

        // Store payment data in session
        session(['payment_data' => $sessionData]);

        $response = Curl::to('https://api.paymongo.com/v1/checkout_sessions')
            ->withHeader('Content-Type: application/json')
            ->withHeader('accept: application/json')
            ->withHeader('Authorization: Basic c2tfdGVzdF9ZS1lMMnhaZWVRRDZjZ1dYWkJYZ1dHVU46')
            ->withData($sessionData)
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

    protected function sendPaymentConfirmationEmail($space, $payment)
    {
        $user = auth()->user();
        $admin = User::find(1);

        // Only send the email if there's an actual payment amount
        if ($payment->amount > 0) {
            // Send email to the user who made the payment
            Mail::to($user->email)->send(new PaymentConfirmation($space, $user, $payment));

            // Send email to the admin (user with ID 1) only if it's a different user
            if ($admin && $admin->id !== $user->id) {
                Mail::to($admin->email)->send(new PaymentConfirmation($space, $admin, $payment));
            }
        }
    }

    public function handlePaymentSuccess($recordId)
    {
        $space = Space::findOrFail($recordId);

        // Retrieve the payment data from the session
        $paymentData = session('payment_data', []);


        // Check if payment has already been processed
        if (!$paymentData || !isset($paymentData['data']['attributes']['line_items'])) {
            $this->notify('warning', 'Payment Already Processed', 'This payment has already been processed.');
            return redirect()->route('filament.app.pages.tenant-space');
        }

        $totalPaid = 0;
        $waterBillPaid = 0;
        $electricityBillPaid = 0;
        $electricityConsumptionPaid = 0;
        $waterConsumptionPaid = 0;
        $rentBillPaid = 0;
        $spaceId = $space->id;

        foreach ($paymentData['data']['attributes']['line_items'] as $item) {
            switch ($item['name']) {
                case 'Water Bill':
                    $waterBillPaid = $space->water_bills;
                    $space->water_bills = 0;
                    $space->water_payment_status = 'paid';
                    $waterConsumptionPaid = $space->water_consumption;
                    $space->water_consumption = 0;
                    $totalPaid += $waterBillPaid;
                    break;
                case 'Electricity Bill':
                    $electricityBillPaid = $space->electricity_bills;
                    $space->electricity_bills = 0;
                    $space->electricity_payment_status = 'paid';
                    $electricityConsumptionPaid = $space->electricity_consumption;
                    $space->electricity_consumption = 0;
                    $totalPaid += $electricityBillPaid;
                    break;
                case 'Monthly Rent':
                    $rentBillPaid = $space->rent_bills;
                    $space->rent_bills = 0;
                    $space->rent_payment_status = 'paid';
                    $totalPaid += $rentBillPaid;
                    break;
            }
        }

        $space->save();
        $space->refresh();

        // Create payment record only if total paid is greater than 0
        if ($totalPaid > 0) {
            // Get the due data from session
            $dueData = session('payment_due_data', []);
            $now = now();

            $paymentData = [
                'tenant_id' => $space->user_id,
                'space_id' => $spaceId,
                'concourse_id' => $space->concourse_id,
                'payment_type' => 'e-wallet',
                'payment_method' => 'gcash',
                'water_bill' => $waterBillPaid,
                'electricity_bill' => $electricityBillPaid,
                'electricity_consumption' => $electricityConsumptionPaid,
                'water_consumption' => $waterConsumptionPaid,
                'rent_bill' => $rentBillPaid,
                'amount' => $totalPaid,
                'payment_status' => 'paid',
                'paid_date' => $now,
            ];

            // Add due dates and check for late payments
            if (!empty($dueData)) {
                if (isset($dueData['water_due'])) {
                    $paymentData['water_due'] = $dueData['water_due'];
                    $paymentData['is_water_late'] = true;
                }

                if (isset($dueData['electricity_due'])) {
                    $paymentData['electricity_due'] = $dueData['electricity_due'];
                    $paymentData['is_electricity_late'] = true;
                }

                if (isset($dueData['rent_due'])) {
                    $paymentData['rent_due'] = $dueData['rent_due'];
                    $paymentData['is_rent_late'] = true;
                }

                if (isset($dueData['paid_late'])) {
                    $paymentData['due_date'] = $dueData['paid_late'];
                    $paymentData['is_penalty'] = true;
                }
            }

            $payment = Payment::create($paymentData);

            // Send email confirmation
            $this->sendPaymentConfirmationEmail($space, $payment);
        }

        // Clear the payment data from the session
        session()->forget('payment_data');

        $this->notify('success', 'Payment Successful', 'Your payment has been processed successfully.');
        return redirect()->route('filament.app.pages.tenant-space');
    }

    public function handlePaymentCancel()
    {
        $this->notify('warning', 'Payment Cancelled', 'Your payment has been cancelled.');
        return redirect()->route('filament.app.pages.tenant-space');
    }
}
