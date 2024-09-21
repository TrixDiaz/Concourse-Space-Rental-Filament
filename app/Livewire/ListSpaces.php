<?php

namespace App\Livewire;

use App\Models\Space;
use App\Services\RequirementForm;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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
                    ->searchable()
                    ->state(function ($record) {
                        return $record->user ? $record->user->name : 'No Tenant';
                    }),
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
                Tables\Actions\CreateAction::make()
                    ->disableCreateAnother()
                    ->label('Rent Space')
                    ->slideOver()
                    ->icon('heroicon-o-plus')
                    ->form(function ($record) {
                        $spaceId = $record ? $record->id : null;
                        return RequirementForm::schema($this->concourseId, $spaceId);
                    })
                    ->using(function (array $data, $record) {
                        $application = \App\Models\Application::create($data);
                        if ($record) {
                            $record->update([
                                'user_id' => Auth::id(),
                                'status' => 'pending'
                            ]);
                        }
                        return $application;
                    })
                    ->hidden(function ($record) {
                        if (!$record) return false;
                        return \App\Models\Application::where('user_id', Auth::id())->exists()
                            && (\App\Models\Application::where('user_id', Auth::id())
                                ->where('concourse_id', $this->concourseId)
                                ->where('space_id', $record->id)
                                ->exists());
                    }),
                Tables\Actions\Action::make('Edit Application')
                    ->link()
                    ->icon('heroicon-o-pencil')
                    ->url(fn($record) => route('filament.app.pages.edit-requirement', ['concourse_id' => $this->concourseId, 'space_id' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(function ($record) {
                        return \App\Models\Application::where('user_id', Auth::id())
                            ->where('concourse_id', $this->concourseId)
                            ->where('space_id', $record->id)
                            ->exists();
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('View Requirements')
                    ->button()
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->url(fn() => route('filament.app.pages.requirement-page'))
                    ->openUrlInNewTab(),
            ]);
    }
}
