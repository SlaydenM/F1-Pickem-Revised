@props(['drivers'])

{{--
    Mobile:  3 equal columns (tap-to-pick flow)
    Desktop: auto-fill with min 150 px cols (drag-and-drop flow)
--}}
<div id="driver-grid"
     class="grid grid-cols-3 gap-2 md:gap-3"
     style="--grid-min:150px">

    @foreach($drivers as $driver)
        <div class="driver-grid-item" data-driver-id="{{ $driver->id }}">
            {{-- sm card on mobile, md on desktop --}}
            <div class="block md:hidden">
                <x-driver-card :driver="$driver" size="sm" :draggable="true" />
            </div>
            <div class="hidden md:block">
                <x-driver-card :driver="$driver" size="md" :draggable="true" />
            </div>
        </div>
    @endforeach
</div>

@push('styles')
<style>
/* On desktop, switch to auto-fill so cards wrap naturally */
@media (min-width: 768px) {
    #driver-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
}
/* Make sm-size driver cards fill their grid cell on mobile */
@media (max-width: 767px) {
    #driver-grid .driver-grid-item > div > div {
        width: 100% !important;
    }
    #driver-grid .driver-grid-item > div > div > div:first-child {
        width: 100% !important;
    }
}
</style>
@endpush
