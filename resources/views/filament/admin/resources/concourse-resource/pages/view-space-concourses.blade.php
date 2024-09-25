<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->record->name }}
        </x-slot>

        <x-slot name="description">
            {{ $this->record->address }}
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::button icon="heroicon-m-arrow-path" color="secondary" wire:click="reload">
                Refresh
            </x-filament::button>
            <x-filament::button wire:click="toggleDrawMode">
                {{ $drawMode ? 'Cancel Drawing' : 'Draw Layout' }}
            </x-filament::button>
            <x-filament::modal width="5xl">
                <x-slot name="heading" wire:ignore>
                    Add Space
                </x-slot>

                @if($this->canCreateSpace && $this->spaceDimensions)
                <x-slot name="trigger">
                    <x-filament::button color="secondary">
                        Create Space
                    </x-filament::button>
                </x-slot>
                @endif

                <form wire:submit.prevent="createSpace">
                    <x-filament::section class="grid grid-cols-2 gap-2">
                        <label for="name">Name</label>
                        <x-filament::input.wrapper class="mb-2">
                            <x-filament::input
                                type="text"
                                placeholder="Name"
                                wire:model="name" />
                        </x-filament::input.wrapper>

                        <label for="sqm">SQM</label>
                        <x-filament::input.wrapper class="mb-2">
                            <x-filament::input
                                type="number"
                                placeholder="SQM"
                                min="0"
                                wire:model.live="sqm" />
                            <x-slot name="suffix" > 
                               X Rate {{ $rate }} 
                            </x-slot>
                        </x-filament::input.wrapper>

                        <label for="price">Price</label>
                        <x-filament::input.wrapper disabled class="mb-2">
                            <x-filament::input
                                type="number"
                                placeholder="Price"
                                wire:model="price"
                                disabled />
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
        <div class="relative" wire:ignore>
            <canvas id="floorMapCanvas" class="absolute top-0 left-0 z-10"></canvas>
            <img id="concourseLayout" src="{{ Storage::url($this->record->layout) }}" alt="Concourse Layout" class="max-w-full h-auto">
            @foreach($this->spaces as $space)
            <div class="absolute border-2 border-red-500"
                style="left: {{ $space->space_coordinates_x }}%; top: {{ $space->space_coordinates_y }}%; width: {{ $space->space_width }}%; height: {{ $space->space_length }}%;">
                <span class="absolute top-0 left-0 text-md p-2 text-center w-full text-wrap">{{ $space->id }}</span>
            </div>

            <x-filament::modal id="{{ $space->id }}">
                <x-slot name="trigger" wire:ignore>
                    <x-filament::button class="mt-3">
                        {{ $space->id }}
                    </x-filament::button>
                </x-slot>

                <x-filament::section>
                    <h3 class="text-lg font-semibold">{{ $space->name }}</h3>
                    <p>Price: ₱{{ number_format($space->price, 2) }}</p>
                    <p>Sqm: {{ $space->sqm }}</p>
                    <p>Status: {{ ucfirst($space->status) }}</p>
                    </x-filament::section>
            </x-filament::modal>
            @endforeach
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                const $canvas = $('#floorMapCanvas');
                const ctx = $canvas[0].getContext('2d');
                const $img = $('#concourseLayout');
                let isDrawing = false;
                let startX, startY;

                function resizeCanvas() {
                    $canvas.attr({
                        width: $img.width(),
                        height: $img.height()
                    });
                }

                $img.on('load', resizeCanvas);
                $(window).on('resize', resizeCanvas);
                resizeCanvas();

                $canvas.on('mousedown', function(e) {
                    if (!@this.drawMode) return;
                    isDrawing = true;
                    const offset = $canvas.offset();
                    startX = e.pageX - offset.left;
                    startY = e.pageY - offset.top;
                });

                $canvas.on('mousemove', function(e) {
                    if (!isDrawing || !@this.drawMode) return;
                    const offset = $canvas.offset();
                    const endX = e.pageX - offset.left;
                    const endY = e.pageY - offset.top;

                    ctx.clearRect(0, 0, $canvas.width(), $canvas.height());
                    ctx.strokeStyle = 'red';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(startX, startY, endX - startX, endY - startY);
                });

                $canvas.on('mouseup mouseleave', function(e) {
                    if (!isDrawing || !@this.drawMode) return;
                    isDrawing = false;
                    const offset = $canvas.offset();
                    const endX = e.pageX - offset.left;
                    const endY = e.pageY - offset.top;
                    const width = endX - startX;
                    const height = endY - startY;

                    const dimensions = {
                        x: (startX / $canvas.width()) * 100,
                        y: (startY / $canvas.height()) * 100,
                        width: (width / $canvas.width()) * 100,
                        height: (height / $canvas.height()) * 100,
                    };

                    @this.call('setSpaceDimensions', dimensions);
                });

                Livewire.on('drawModeToggled', function(mode) {
                    $canvas.css('pointer-events', mode ? 'auto' : 'none');
                });
            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('reload-page', () => {
                    window.location.reload();
                });
            });
        </script>
        @else
        <p class="mt-4 text-gray-500">No layout image available</p>
        @endif
    </x-filament::section>

    <x-filament::section collapsible collapsed>
        <x-slot name="heading">
            Spaces
        </x-slot>

        @if($this->spaces->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
            @foreach($this->spaces as $space)
            <div class="p-4 rounded-lg shadow-md border">
                <h3 class="text-lg font-semibold">{{ $space->name }}</h3>
                <p>Price: ₱{{ number_format($space->price, 2) }}</p>
                <p>Sqm: {{ $space->sqm }}</p>
                <p>Status: {{ ucfirst($space->status) }}</p>
                  </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500">No spaces available for this concourse.</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>