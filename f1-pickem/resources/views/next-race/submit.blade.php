@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8">

        {{-- Race banner --}}
        <x-next-race-card type="countdown" :race="$race" />

        {{-- Section title --}}
        <div class="flex items-center gap-4 mb-5">
            <div class="w-1 h-10 bg-[#E10600]"></div>
            <div>
                <h1 class="font-['Barlow_Condensed'] font-black italic text-3xl md:text-4xl text-white tracking-tight uppercase leading-none">
                    Place Picks
                </h1>
                <div class="font-['JetBrains_Mono'] text-[#BBBBBB] text-[10px] tracking-wider mt-0.5">
                    @if ($numPicks > 1)
                        {{ $numPicks }} players have picked · You should too
                    @elseif ($numPicks == 1)
                        1 player has picked · Make it a pair
                    @else
                        No players have picked · Be the first
                    @endif
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 border border-[#E10600]/30 px-4 py-3 font-['Inter'] text-sm text-[#E10600]"
                 style="background:#160500;border-radius:2px">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Hidden store of pre-rendered driver cards for JS cloning --}}
        <div id="driver-store" class="hidden" aria-hidden="true">
            @foreach($drivers as $driver)
                <div data-store-id="{{ $driver->id }}"
                     data-name="{{ $driver->name }}"
                     data-team="{{ $driver->team }}"
                     data-number="{{ $driver->number }}"
                     data-color="{{ $driver->primary_color }}"
                     data-img="{{ $driver->getPath() }}">
                    <x-driver-card :driver="$driver" size="md" />
                </div>
            @endforeach
        </div>

        {{-- Picks form --}}
        <form id="picks-form" method="POST" action="{{ route('submit-picks') }}">
            @csrf
            <input type="hidden" name="bettor" id="bettor-input">

            {{-- Bonus strip + drop zones --}}
            <div class="relative mb-4 border border-white/[0.08] overflow-hidden"
                 style="border-radius:2px;background:rgba(15,15,15,0.7)">

                {{-- Bonus strip --}}
                <div class="flex items-center justify-between px-4 md:px-5 py-2.5 border-b border-white/[0.07]"
                     style="background:rgba(0,0,0,0.3)">
                    <span class="font-['JetBrains_Mono'] text-[#BBBBBB] text-[9px] md:text-[10px] tracking-widest uppercase">
                        Bonus
                    </span>
                    <div class="flex items-center gap-2">
                        <div class="font-['Barlow_Condensed'] font-black italic text-sm md:text-base px-2 md:px-3 py-0.5"
                             style="color:{{ $bonusColor }};background:{{ $bonusColor }}18;
                                    border:1px solid {{ $bonusColor }}44;border-radius:2px">
                            {{ $bonusDisplay }}
                        </div>
                        <span class="font-['JetBrains_Mono'] text-[9px] md:text-[10px] tracking-widest uppercase"
                              style="color:{{ $bonusColor }}">{{ $bonusLabel }}</span>
                    </div>
                </div>

                {{-- Drop zones — flex row on all breakpoints, sizes adapt --}}
                <div class="flex gap-2 md:gap-6 justify-center py-4 md:py-6 px-3 md:px-5">
                    @foreach([
                        ['slot' => 'first', 'label' => '1ST'],
                        ['slot' => 'tenth', 'label' => '10TH'],
                        ['slot' => 'last',  'label' => 'LAST'],
                    ] as $zone)
                        <div class="flex flex-col items-center gap-1 md:gap-2 flex-1">
                            <div class="font-['Barlow_Condensed'] font-black italic text-white text-xs md:text-sm uppercase tracking-widest">
                                {{ $zone['label'] }}
                            </div>
                            <div class="drop-zone w-full"
                                 data-slot="{{ $zone['slot'] }}"
                                 style="height:80px">
                                <div class="drop-placeholder w-full h-full flex items-center justify-center
                                            font-['JetBrains_Mono'] text-[10px] tracking-widest
                                            border-2 border-dashed border-white/40 text-[#BBBBBB]"
                                     style="border-radius:2px">· · ·</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Lock In button — full width on mobile, right-aligned on desktop --}}
            <div class="mb-5 md:flex md:justify-end">
                <button type="submit" id="submit-btn" disabled
                        class="w-full md:w-auto font-['Barlow_Condensed'] font-black italic uppercase text-lg px-10 py-3
                               transition-all duration-150 bg-[#232323] text-[#BBBBBB] cursor-not-allowed"
                        style="clip-path:polygon(12px 0%,100% 0%,calc(100% - 12px) 100%,0% 100%)">
                    🔒 Lock In Picks
                </button>
            </div>
        </form>

        {{-- Driver grid label --}}
        <div class="font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-sm text-[#BBBBBB] mb-3 md:mb-4">
            {{ $year }} Driver Grid · tap to pick
        </div>

        {{-- Driver grid: 3 columns on mobile, auto-fill on desktop --}}
        <x-driver-grid :drivers="$drivers" />
    </div>
</div>
@endsection

@push('styles')
<style>
/* Make drop-zone-placed cards fill their container on mobile */
@media (max-width: 767px) {
    .drop-zone > div,
    .drop-zone > div > div {
        width: 100% !important;
    }
    .drop-zone > div > div > div:first-child {
        width: 100% !important;
        height: 80px !important;
    }
    .drop-zone [data-store-id] {
        width: 100% !important;
    }
    .drop-zone .driver-img {
        width: 100% !important;
        height: 100% !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    var picks  = { first: null, tenth: null, last: null };
    var bonus  = {{ $bonus }};
    var dragging = null;

    // ── Initialise drag on grid items ────────────────────────────────────────
    document.querySelectorAll('#driver-grid .driver-grid-item').forEach(function (wrapper) {
        var card = wrapper.querySelector('[draggable]');
        if (!card) return;
        card.addEventListener('dragstart', function (e) {
            dragging = wrapper.dataset.driverId;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', dragging);
        });
        card.addEventListener('dragend', function () { dragging = null; });
    });

    // ── Drop zone behaviour ───────────────────────────────────────────────────
    document.querySelectorAll('.drop-zone').forEach(function (zone) {
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('dz-over');
        });
        zone.addEventListener('dragleave', function () {
            zone.classList.remove('dz-over');
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('dz-over');
            if (!dragging) return;
            var slot = zone.dataset.slot;

            for (var s in picks) {
                if (picks[s] === dragging) { clearSlot(s); break; }
            }
            if (picks[slot]) clearSlot(slot);

            picks[slot] = dragging;
            renderSlot(slot, dragging);
            dimGridItem(dragging, true);
            updateBtn();
            dragging = null;
        });
    });

    // ── Touch / tap support (mobile) ─────────────────────────────────────────
    var tapSelected = null; // driver id selected by tap

    document.querySelectorAll('#driver-grid .driver-grid-item').forEach(function (wrapper) {
        wrapper.addEventListener('click', function () {
            var driverId = wrapper.dataset.driverId;
            if (picks[Object.keys(picks).find(function(k){return picks[k]===driverId;})] === driverId) {
                // Already placed — deselect
                tapSelected = null;
                clearSelectedStyle();
                return;
            }
            tapSelected = driverId;
            clearSelectedStyle();
            wrapper.style.outline = '2px solid #E10600';
            wrapper.style.outlineOffset = '2px';
        });
    });

    document.querySelectorAll('.drop-zone').forEach(function (zone) {
        zone.addEventListener('click', function () {
            if (!tapSelected) return;
            var slot = zone.dataset.slot;

            for (var s in picks) {
                if (picks[s] === tapSelected) { clearSlot(s); break; }
            }
            if (picks[slot]) clearSlot(slot);

            picks[slot] = tapSelected;
            renderSlot(slot, tapSelected);
            dimGridItem(tapSelected, true);
            updateBtn();

            clearSelectedStyle();
            tapSelected = null;
        });
    });

    function clearSelectedStyle() {
        document.querySelectorAll('#driver-grid .driver-grid-item').forEach(function (w) {
            w.style.outline = '';
            w.style.outlineOffset = '';
        });
    }

    function renderSlot(slot, driverId) {
        var zone  = document.querySelector('.drop-zone[data-slot="' + slot + '"]');
        var store = document.querySelector('#driver-store [data-store-id="' + driverId + '"]');
        if (!zone || !store) return;

        var clone = store.cloneNode(true);
        clone.style.cursor = 'grab';
        clone.style.width  = '100%';

        var dragger = document.createElement('div');
        dragger.draggable = true;
        dragger.dataset.driverId = driverId;
        dragger.style.width = '100%';
        dragger.appendChild(clone);

        dragger.addEventListener('dragstart', function (e) {
            dragging = driverId;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', driverId);
            setTimeout(function() { dragger.style.opacity = '0'; }, 0);
        });
        dragger.addEventListener('dragend', function (e) {
            dragger.style.opacity = '1';
            if (e.dataTransfer.dropEffect === 'none') {
                clearSlot(slot);
                updateBtn();
            }
            dragging = null;
        });

        // Tap to remove from slot
        dragger.addEventListener('click', function () {
            clearSlot(slot);
            updateBtn();
        });

        zone.innerHTML = '';
        zone.appendChild(dragger);
    }

    function clearSlot(slot, keepDimmed) {
        var zone = document.querySelector('.drop-zone[data-slot="' + slot + '"]');
        if (!zone) return;
        var prev = picks[slot];
        picks[slot] = null;
        if (prev && !keepDimmed) dimGridItem(prev, false);
        zone.innerHTML = '<div class="drop-placeholder w-full h-full flex items-center justify-center ' +
            'font-mono text-[10px] tracking-widest border-2 border-dashed border-white/40 text-[#BBBBBB]" ' +
            'style="border-radius:2px">· · ·</div>';
    }

    function dimGridItem(driverId, dim) {
        var item = document.querySelector('#driver-grid [data-driver-id="' + driverId + '"]');
        if (!item) return;
        var wrapper = item.closest('.driver-grid-item') || item;
        wrapper.style.opacity = dim ? '0.30' : '';
        wrapper.style.filter  = dim ? 'saturate(0.30)' : '';
        wrapper.style.outline = '';
    }

    function updateBtn() {
        var btn   = document.getElementById('submit-btn');
        var ready = picks.first && picks.tenth && picks.last;
        btn.disabled = !ready;
        if (ready) {
            btn.classList.replace('bg-[#232323]', 'bg-[#E10600]');
            btn.classList.replace('text-[#BBBBBB]', 'text-white');
            btn.classList.replace('cursor-not-allowed', 'cursor-pointer');
        } else {
            btn.classList.replace('bg-[#E10600]', 'bg-[#232323]');
            btn.classList.replace('text-white', 'text-[#BBBBBB]');
            btn.classList.replace('cursor-pointer', 'cursor-not-allowed');
        }
    }

    document.getElementById('picks-form').addEventListener('submit', function (e) {
        if (!picks.first || !picks.tenth || !picks.last) { e.preventDefault(); return; }
        document.getElementById('bettor-input').value = JSON.stringify({
            bets:  [parseInt(picks.first), parseInt(picks.tenth), parseInt(picks.last)],
            bonus: bonus
        });
    });

    var styleEl = document.createElement('style');
    styleEl.textContent = '.drop-zone.dz-over .drop-placeholder{border-color:#E10600!important;color:#E10600!important;}';
    document.head.appendChild(styleEl);
})();
</script>
@endpush
