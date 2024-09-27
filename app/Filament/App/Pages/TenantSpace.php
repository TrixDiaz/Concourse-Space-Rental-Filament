<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Tenant;

class TenantSpace extends Page implements HasForms, HasTable
{
    public $tenantId;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static string $view = 'filament.app.pages.tenant-space';

    public function table(Table $table): Table
    {
        return $table
            ->query(Tenant::query()
                ->where('is_active', true)
                ->where('tenant_id', auth()->user()->id))
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
                    ->label('Lease Start')
                    ->date('F j, Y')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                    Tables\Columns\TextColumn::make('bills')
                    ->searchable()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lease_status')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable()
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->actions([
                Tables\Actions\Action::make('payBills')
                    ->label('Pay Bills')
                    ->button(),
            ]);
    }
}
