<div>
    @if($getRecord() && $getRecord()->exists && $getRecord()->requirements)
        @if($getRecord()->requirements->count() > 0)
            <ul class="space-y-1">
                @foreach($getRecord()->requirements as $requirement)
                    <li class="flex items-center space-x-2">
                        <span class="text-sm font-medium">{{ $requirement->name }}</span>
                        <span class="text-xs px-2 py-1 rounded-full {{ $requirement->is_approved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $requirement->is_approved ? 'Approved' : 'Not Approved' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">No requirements found.</p>
        @endif
    @else
        <p class="text-sm text-gray-500">Requirements will be available after saving the application.</p>
    @endif
</div>