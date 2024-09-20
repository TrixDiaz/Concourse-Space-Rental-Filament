<?php

namespace App\Filament\Admin\Resources\ConcourseResource\Pages;

use App\Filament\Admin\Resources\ConcourseResource;
use App\Models\Space;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class ViewSpaceConcourses extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ConcourseResource::class;

    protected static string $view = 'filament.admin.resources.concourse-resource.pages.view-space-concourses';

    public $name;
    public $price;
    public $status = 'available';
    public $spaces;
    public $canCreateSpace = false;
    public $drawMode = false;
    public $spaceDimensions = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->spaces = $this->record->spaces()->get();
        $this->canCreateSpace = $this->record->layout !== null;
    }

    public function toggleDrawMode()
    {
        $this->drawMode = !$this->drawMode;
        $this->dispatch('drawModeToggled', $this->drawMode);
    }

    public function setSpaceDimensions($dimensions)
    {
        $this->spaceDimensions = $dimensions;
    }

    public function createSpace()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if (!$this->spaceDimensions) {
            Notification::make()
                ->title('Error')
                ->body('Please draw the space on the layout before creating.')
                ->danger()
                ->send();
            return;
        }

        Space::create([
            'user_id' => null,
            'concourse_id' => $this->record->id,
            'name' => $this->name,
            'price' => $this->price,
            'status' => 'available',
            'is_active' => true,
            'space_width' => $this->spaceDimensions['width'],
            'space_length' => $this->spaceDimensions['height'],
            'space_area' => $this->spaceDimensions['width'] * $this->spaceDimensions['height'],
            'space_dimension' => $this->spaceDimensions['width'] . 'x' . $this->spaceDimensions['height'],
            'space_coordinates_x' => $this->spaceDimensions['x'],
            'space_coordinates_y' => $this->spaceDimensions['y'],
            'space_coordinates_x2' => $this->spaceDimensions['x'] + $this->spaceDimensions['width'],
            'space_coordinates_y2' => $this->spaceDimensions['y'] + $this->spaceDimensions['height'],
        ]);

        $this->reset(['name', 'price', 'spaceDimensions']);
        $this->drawMode = false;

        Notification::make()
            ->title('Space Created')
            ->body('A new space has been created. Please refresh the page to see the new space.')
            ->success()
            ->send();

        $this->spaces = $this->record->spaces()->get();
    }
}
