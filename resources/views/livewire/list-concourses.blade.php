<div>
    <x-filament::section>
        <x-slot name="heading">
            Available Concourses
        </x-slot>

        <x-slot name="description">
            This is the list of all the concourses available in the system.
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::input.wrapper>
                <div class="flex flex-row justify-start items-center gap-2">
                    <x-filament::input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by name or address" />
                </div>
            </x-filament::input.wrapper>
        </x-slot>

        <div class="flex flex-col justify-between gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($concourses as $concourse)
                <x-filament::card class="flex flex-col h-full">
                    <div class="flex-grow">
                        @if ($concourse->image === null)
                        <img src="https://placehold.co/600x250" alt="Default Concourse Image" class="w-full max-h-56 object-cover rounded-xl">
                        @else
                        <img src="{{ asset('storage/' . $concourse->image) }}" alt="{{ $concourse->name }}" class="w-full max-h-56 object-cover rounded-xl">
                        @endif
                    </div>
                    <div class="p-4 text-center">
                        <h2 class="text-xl font-bold mb-2">{{ $concourse->name }}</h2>
                        <p class="text-sm ">{{ $concourse->address }}</p>
                    </div>
                </x-filament::card>
                @endforeach
            </div>

            <div class="mt-2">
                <div class="mt-2">
                    <x-filament::pagination
                        :paginator="$this->getConcourses()"
                        :current-page-option-property="$perPage"
                        extreme-links />
                </div>
            </div>
        </div>
    </x-filament::section>
</div>