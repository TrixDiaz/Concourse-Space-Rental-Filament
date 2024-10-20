<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Space;
use App\Models\Concourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;

class ConcourseSpaces extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.concourse-spaces';

    protected static bool $shouldRegisterNavigation = false;

    public $concourse;

    protected function updateWaterBills($state, $set, $get, $record)
    {
        if ($record && $record->status === 'occupied') {
            $concourse = $record->concourse;

            // Update the space's water consumption
            $record->update(['water_consumption' => $state]);

            // Recalculate the concourse's total water consumption
            $concourse->updateTotalWaterConsumption();

            // Recalculate water bills for all occupied spaces in this concourse
            $occupiedSpaces = $concourse->spaces()->where('status', 'occupied')->get();
            foreach ($occupiedSpaces as $space) {
                $space->calculateWaterBill();
            }

            // Update the form field
            $set('water_bills', $record->water_bills);

            Notification::make()
                ->title('Water bills updated')
                ->success()
                ->send();
        }
    }

    protected function updateElectricityBills($state, $set, $get, $record)
    {
        if ($record && $record->status === 'occupied') {
            $concourse = $record->concourse;

            // Update the space's electricity consumption
            $record->update(['electricity_consumption' => $state]);

            // Recalculate the concourse's total electricity consumption
            $concourse->updateTotalElectricityConsumption();

            // Recalculate electricity bills for all occupied spaces in this concourse
            $occupiedSpaces = $concourse->spaces()->where('status', 'occupied')->get();
            foreach ($occupiedSpaces as $space) {
                $space->calculateElectricityBill();
            }

            // Update the form field
            $set('electricity_bills', $record->electricity_bills);

            Notification::make()
                ->title('Electricity bills updated')
                ->success()
                ->send();
        }
    }

    public static function getRoutes(): \Closure
    {
        return function () {
            Route::get('/concourse-spaces', static::class)
                ->name('filament.admin.pages.concourse-spaces');
        };
    }

    public function mount(Request $request)
    {
        $concourseId = $request->query('concourseId');
        $this->concourse = Concourse::findOrFail($concourseId);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Space::query()->where('concourse_id', $this->concourse->id))
            ->columns([
              Tables\Columns\TextColumn::make('user.name')
                ->label('Tenant')
                ->default(fn($record) => $record->user->name ?? 'No Tenant')
                ->description(fn($record) => $record->name)
                ->extraAttributes(['class' => 'capitalize'])
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('price')
                ->label('Price')
                ->numeric()
                ->sortable()
                ->prefix('₱')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('sqm')
                ->label('Sqm')
                ->numeric()
                ->sortable()
                ->description(fn($record) => 'Price: ' . '₱' . number_format($record->price ?? 0, 2))
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('Lease Term')
                ->label('Lease Term')
                ->default(fn($record) => 'Lease Due:' . \Carbon\Carbon::parse($record->lease_due)->format('F j, Y'))
                ->description(fn($record) => 'Lease End: ' . \Carbon\Carbon::parse($record->lease_end)->format('F j, Y'))
                ->numeric(),
            Tables\Columns\TextColumn::make('water_bills')
                ->label('Water Bills')
                ->default(fn($record) => 'Water: ' . '₱' . number_format($record->water_bills ?? 0, 2))
                ->description(fn($record) => 'Status: ' . $record->water_payment_status ?? null . ', Consumption: ' . $record->water_consumption . ' m3')
                ->numeric()
                ->sortable()
                ->money('PHP')
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('electricity_bills')
                ->label('Electricity Bills')
                ->default(fn($record) => '₱' . number_format($record->electricity_bills ?? 0, 2))
                ->description(fn($record) => 'Status: ' . $record->electricity_payment_status ?? null)
                ->numeric()
                ->sortable()
                ->money('PHP')
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('rent_bills')
                ->label('Rent Bills')
                ->numeric()
                ->sortable()
                ->money('PHP')
                ->default(fn($record) => 'Rent: ' . number_format($record->rent_bills ?? 0, 2))
                ->description(fn($record) => 'Status: ' . $record->rent_payment_status ?? null)
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->extraAttributes(['class' => 'capitalize']),
            Tables\Columns\TextColumn::make('Consumptions')
                ->label('Consumptions')
                ->numeric()
                ->sortable()
                ->default(fn($record) => 'Water: ' . number_format($record->water_consumption ?? 0, 2) . ' m3')
                ->description(fn($record) => 'Electricity: ' . number_format($record->electricity_consumption ?? 0, 2) . ' kWh'),
            Tables\Columns\IconColumn::make('is_active')
                ->label('Visible in Tenant')
                ->boolean()
                ->extraAttributes(['class' => 'capitalize'])
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Add any header actions if needed
        ];
    }

    public function getTitle(): string
    {
        return "Spaces for Concourse: {$this->concourse->name}";
    }
}