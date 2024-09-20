<?php

namespace App\Livewire;

use App\Models\Concourse;
use Livewire\Component;
use Livewire\WithPagination;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ListConcourses extends Component implements HasForms
{
    use WithPagination;
    use InteractsWithForms;

    public $perPage = 4;
    public $search = ''; // Add this line

    // Add this method
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getConcourses()
    {
        return Concourse::where('is_active', true)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('address', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.list-concourses', [
            'concourses' => $this->getConcourses(),
        ]);
    }
}
