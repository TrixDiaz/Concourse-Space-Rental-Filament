<?php

namespace App\Filament\Admin\Resources\ConcourseResource\Pages;

use App\Filament\Admin\Resources\ConcourseResource;
use App\Models\Space;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use League\CommonMark\Extension\CommonMark\Parser\Inline\BacktickParser;

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

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->spaces = $this->record->spaces()->get();
        $this->canCreateSpace = $this->record->dimension !== null; 
    }

    public function createSpace()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $spaceWidth = rand(5, 20);
        $spaceLength = rand(5, 20);
        $spaceCoordinatesX = rand(0, 100);
        $spaceCoordinatesY = rand(0, 100);

        Space::create([
            'user_id' => null,
            'concourse_id' => $this->record->id,
            'name' => $this->name,
            'price' => $this->price,
            'status' => 'available',
            'is_active' => true,
            'space_width' => $spaceWidth,
            'space_length' => $spaceLength,
            'space_area' => $spaceWidth * $spaceLength,
            'space_dimension' => $spaceWidth . 'x' . $spaceLength,
            'space_coordinates_x' => $spaceCoordinatesX,
            'space_coordinates_y' => $spaceCoordinatesY,
            'space_coordinates_x2' => $spaceCoordinatesX + $spaceWidth,
            'space_coordinates_y2' => $spaceCoordinatesY + $spaceLength,
        ]);

        $this->reset(['name', 'price']);

        Notification::make()
            ->title('Space Created')
            ->body('A new space has been created. The space is ' . '. Please Refresh the page to see the new space.')
            ->success()
            ->send(User::all());

    }
}
