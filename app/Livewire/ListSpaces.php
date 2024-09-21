<?php

namespace App\Livewire;

use App\Models\Space;
use App\Models\Concourse;
use App\Services\RequirementForm;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;

class ListSpaces extends Component implements HasTable, HasForms
{
    use InteractsWithForms, InteractsWithTable;

    public $concourseId;

    public function mount()
    {
        $this->concourseId = request()->query('concourse_id');
    }

    public function render()
    {
        return view('livewire.list-spaces');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Space::query()
                ->where('is_active', true)
                ->when($this->concourseId, function ($query) {
                    $query->where('concourse_id', $this->concourseId);
                }))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Tenant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->searchable()
                    ->sortable()
                    ->money('PHP'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize']),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('F j, Y')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'pending' => 'Pending',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('apply')
                    ->label('Rent Space')
                    ->slideOver()
                    ->form(RequirementForm::schema())
            ])
            ->headerActions([
                Tables\Actions\Action::make('View Requirements')
                    ->button()
                    ->color('warning')
                    ->url(fn() => route('filament.app.pages.requirement-page'))
                    ->openUrlInNewTab(),
            ]);
    }
}
