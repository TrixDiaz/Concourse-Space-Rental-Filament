<?php

namespace App\Filament\Admin\Pages;

use App\Models\Application;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;

class Approve extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationGroup = 'Applications Settings';

    protected static ?string $navigationLabel = 'Tenants';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.approve';

    public static function getNavigationBadge(): ?string
    {
        return Application::onlyTrashed()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Application::onlyTrashed())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->label('Tenant'),
                Tables\Columns\TextColumn::make('concourse.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('space.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('business_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('business_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('concourse_lease_term')
                    ->label('Lease Term')
                    ->date()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        if ($record->concourse_lease_term) {
                            return $record->created_at->addMonths($record->concourse_lease_term);
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('remarks')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-bolt-slash')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->poll('10s');
    }
}
