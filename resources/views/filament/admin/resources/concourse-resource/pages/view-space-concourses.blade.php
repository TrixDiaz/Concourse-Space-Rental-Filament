<x-filament-panels::page>
    <x-filament::section
        collapsible
        collapsed>
        <x-slot name="heading">
            {{ $this->record->name }} 
        </x-slot>

        <x-slot name="description">
            {{ $this->record->address }}
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::button>
                Draw Layout
            </x-filament::button>
            <x-filament::modal width="5xl">
                <x-slot name="heading">
                    Add Space
                </x-slot>

                <x-slot name="trigger">
                    <x-filament::button color="secondary">
                        Create Space
                    </x-filament::button>
                </x-slot>

                <form wire:submit.prevent="createSpace">
                    <x-filament::section>
                        <label for="name">Name</label>
                        <x-filament::input.wrapper class="mb-2">
                            <x-filament::input
                                type="text"
                                placeholder="Name"
                                wire:model="name" />
                        </x-filament::input.wrapper>

                        <label for="price">Price</label>
                        <x-filament::input.wrapper class="mb-2">
                            <x-filament::input
                                type="number"
                                placeholder="Price"
                                wire:model="price" />
                        </x-filament::input.wrapper>

                        <x-filament::button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-70 cursor-wait">
                            <span wire:loading.remove>Create Space</span>
                            <span wire:loading>Loading...</span>
                        </x-filament::button>
                    </x-filament::section>
                </form>
            </x-filament::modal>
        </x-slot>

        @if($this->record->layout)
        <div class="relative">
            <img src="{{ Storage::url($this->record->layout) }}" alt="Concourse Layout" class="max-w-full h-auto">
            @foreach($this->spaces as $space)
            <div class="absolute border-2 border-red-500"
                style="left: {{ $space->space_coordinates_x }}%; top: {{ $space->space_coordinates_y }}%; width: {{ $space->space_width }}%; height: {{ $space->space_length }}%;">
                <span class="absolute top-0 left-0 bg-white text-xs p-1">{{ $space->name }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="mt-4 text-gray-500">No layout image available</p>
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Spaces
        </x-slot>

        @if($this->spaces->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->spaces as $space)
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold">{{ $space->name }}</h3>
                <p>Price: ${{ number_format($space->price, 2) }}</p>
                <p>Status: {{ ucfirst($space->status) }}</p>
                <p>Dimensions: {{ $space->space_dimension }}</p>
                <p>Area: {{ $space->space_area }} sq units</p>
                <p>Coordinates: ({{ $space->space_coordinates_x }}, {{ $space->space_coordinates_y }}) to ({{ $space->space_coordinates_x2 }}, {{ $space->space_coordinates_y2 }})</p>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500">No spaces available for this concourse.</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>