<x-filament-panels::page>
    <div>
        <section class="mb-6">
            @if($this->tenant && $this->tenant->concourse)
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h2 class="text-xl font-semibold mb-4">{{ $this->tenant->concourse->name }} Layout</h2>
                    @if($this->tenant->concourse->layout)
                        <img src="{{ Storage::url($this->tenant->concourse->layout) }}" alt="{{ $this->tenant->concourse->name }} Layout" class="w-full h-auto rounded-lg">
                    @else
                        <p class="text-gray-600">No layout image available for this concourse.</p>
                    @endif
                </div>
            @else
                <div class="bg-gray-100 rounded-lg p-4 text-center">
                    <p class="text-gray-600">No concourse information available</p>
                </div>
            @endif
        </section>

        <section class="pt-4">
            {{ $this->table }}
        </section>
    </div>
</x-filament-panels::page>