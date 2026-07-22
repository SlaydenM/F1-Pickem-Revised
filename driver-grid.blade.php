@props(['drivers'])

<div class="grid grid-cols-3 gap-3 md:hidden" id="driver-grid">
    @foreach($drivers as $driver)
        <div class="driver-grid-item mx-auto" data-driver-id="{{ $driver->id }}">
            <x-driver-card :driver="$driver" size="sm" :draggable="true" />
        </div>
    @endforeach
</div>
<div class="hidden md:flex gap-2 flex-wrap" id="driver-grid">
    @foreach($drivers as $driver)
        <div class="driver-grid-item mx-auto" data-driver-id="{{ $driver->id }}">
            <x-driver-card :driver="$driver" size="md" :draggable="true" />
        </div>
    @endforeach
</div>
