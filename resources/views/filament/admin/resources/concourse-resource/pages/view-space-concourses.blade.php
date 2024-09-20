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

                        <x-filament::button type="submit">Create Space</x-filament::button>
                    </x-filament::section>
                </form>
            </x-filament::modal>
        </x-slot>

        @if($this->record->layout)
        <img src="{{ Storage::url($this->record->layout) }}" alt="Concourse Layout" class="mt-4 max-w-full h-auto">
        @else
        <p class="mt-4 text-gray-500">No layout image available</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>