@props(['drivers'])

<div class="flex flex-wrap gap-3" id="driver-grid">
    @foreach($drivers as $driver)
        <div class="driver-grid-item" data-driver-id="{{ $driver->id }}">
            <x-driver-card :driver="$driver" size="md" :draggable="true" />
        </div>
    @endforeach
</div>
