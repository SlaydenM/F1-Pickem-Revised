@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- Race banner --}}
        <x-next-race-card type="countdown" :race="$race" />

        {{-- Section title --}}
        
        <div class="flex items-center gap-4 mb-5">
            <div class="w-1 h-10 bg-[#E10600]"></div>
            <div>
                <h1 class="font-['Barlow_Condensed'] font-black italic text-4xl text-white tracking-tight uppercase leading-none">
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

        {{-- Hidden store of pre-rendered card markup for JS cloning --}}
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

            {{-- Bonus + drop zones --}}
            <div class="relative mb-5 border border-white/[0.08] overflow-hidden"
                 style="border-radius:2px;background:rgba(15,15,15,0.7)">

                {{-- Bonus strip --}}
                <div class="flex items-center justify-between px-5 py-2.5 border-b border-white/[0.07]"
                     style="background:rgba(0,0,0,0.3)">
                    <span class="font-['JetBrains_Mono'] text-[#BBBBBB] text-[10px] tracking-widest uppercase">
                        Submission Bonus
                    </span>
                    <div class="flex items-center gap-2">
                        <div class="font-['Barlow_Condensed'] font-black italic text-base px-3 py-0.5"
                             style="color:{{ $bonusColor }};background:{{ $bonusColor }}18;
                                    border:1px solid {{ $bonusColor }}44;border-radius:2px">
                            {{ $bonusDisplay }}
                        </div>
                        <span class="font-['JetBrains_Mono'] text-[10px] tracking-widest uppercase"
                              style="color:{{ $bonusColor }}">{{ $bonusLabel }}</span>
                    </div>
                </div>

                {{-- Drop zones --}}
                <div class="flex gap-6 justify-center py-6 px-5">
                    @foreach([
                        ['slot' => 'first', 'label' => '1ST PLACE'],
                        ['slot' => 'tenth', 'label' => '10TH PLACE'],
                        ['slot' => 'last',  'label' => 'LAST PLACE'],
                    ] as $zone)
                        <div class="flex flex-col items-center gap-2">
                            <div class="font-['Barlow_Condensed'] font-black italic text-white text-sm uppercase tracking-widest">
                                {{ $zone['label'] }}
                            </div>
                            <div class="drop-zone"
                                 data-slot="{{ $zone['slot'] }}"
                                 style="width:150px;height:100px">
                                <div class="drop-placeholder w-full h-full flex items-center justify-center
                                            font-['JetBrains_Mono'] text-[10px] tracking-widest
                                            border-2 border-dashed border-white/40 text-[#BBBBBB]"
                                     style="border-radius:2px">· · ·</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Submit button --}}
            <div class="flex justify-end mb-6">
                <button type="submit" id="submit-btn" disabled
                        class="font-['Barlow_Condensed'] font-black italic uppercase text-lg px-10 py-3
                               transition-all duration-150 bg-[#232323] text-[#BBBBBB] cursor-not-allowed"
                        style="clip-path:polygon(12px 0%,100% 0%,calc(100% - 12px) 100%,0% 100%)">
                    🔒 Lock In Picks
                </button>
            </div>
        </form>

        {{-- Driver grid --}}
        <div class="font-['Barlow_Condensed'] font-bold uppercase tracking-widest text-sm text-[#BBBBBB] mb-4">
            {{ $year }} Driver Grid · drag to predict
        </div>
        <x-driver-grid :drivers="$drivers" />
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var picks  = { first: null, tenth: null, last: null };
    var bonus  = {{ $bonus }};
    var dragging = null; // driver id being dragged

    // ── Initialise drag on grid items ────────────────────────────────────────
    document.querySelectorAll('#driver-grid .driver-grid-item').forEach(function (wrapper) {
        var card = wrapper.querySelector('[draggable]');
        if (!card) return;
        card.addEventListener('dragstart', function (e) {
            dragging = wrapper.dataset.driverId;
            e.dataTransfer.effectAllowed = 'move';
            
            // ADD THIS LINE: Explicitly set data so the browser registers a valid drag
            e.dataTransfer.setData('text/plain', dragging); 
        });
        card.addEventListener('dragend', function () { dragging = null; });
    });

    // ── Drop zone behaviour ──────────────────────────────────────────────────
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
            e.stopPropagation(); // 3. IMPORTANT: Stop the global document drop from firing!
            
            zone.classList.remove('dz-over');
            if (!dragging) return;
            var slot = zone.dataset.slot;

            // If driver is already in another slot, clear it from there first
            for (var s in picks) {
                if (picks[s] === dragging) { clearSlot(s); break; }
            }

            // If the current slot is already occupied, clear it (returns old driver to grid)
            if (picks[slot]) {
                clearSlot(slot);
            }

            picks[slot] = dragging;
            renderSlot(slot, dragging);
            dimGridItem(dragging, true);
            updateBtn();
            
            dragging = null; // Reset after successful slot placement
        });
    });

    function renderSlot(slot, driverId) {
        var zone  = document.querySelector('.drop-zone[data-slot="' + slot + '"]');
        var store = document.querySelector('#driver-store [data-store-id="' + driverId + '"]');
        if (!zone || !store) return;

        var clone = store.cloneNode(true);
        clone.style.cursor = 'grab';

        // Wrap clone so it can be dragged back out
        var dragger = document.createElement('div');
        dragger.draggable = true;
        dragger.dataset.driverId = driverId;
        dragger.appendChild(clone);
        
        dragger.addEventListener('dragstart', function (e) {
            dragging = driverId;
            e.dataTransfer.effectAllowed = 'move';
            
            // ADD THIS LINE: Explicitly set data so the browser registers a valid drag
            e.dataTransfer.setData('text/plain', driverId);
            
            // UX Enhancement: Visually hide the slot item while it's being dragged
            // We use a timeout so it doesn't disappear before the browser generates the drag image
            setTimeout(function() { dragger.style.opacity = '0'; }, 0);
        });

        // 3. Handle dropping the item outside of a valid drop zone
        dragger.addEventListener('dragend', function (e) {
            dragger.style.opacity = '1'; // Restore visibility if the drag cancels
            
            // A dropEffect of 'none' means the user dropped it outside any valid zone
            if (e.dataTransfer.dropEffect === 'none') {
                clearSlot(slot);
                updateBtn();
            }
            dragging = null;
        });

        zone.innerHTML = '';
        zone.appendChild(dragger);
    }

    function clearSlot(slot, keepDimmed) {
        var zone = document.querySelector('.drop-zone[data-slot="' + slot + '"]');
        if (!zone) return;
        
        var prev = picks[slot];
        picks[slot] = null;
        
        if (prev && !keepDimmed) dimGridItem(prev, false); // Undims in the grid
        
        zone.innerHTML = '<div class="drop-placeholder w-full h-full flex items-center justify-center ' +
            'font-mono text-[10px] tracking-widest border-2 border-dashed border-white/40 text-gray-400" ' +
            'style="border-radius:2px">· · ·</div>';
    }

    function dimGridItem(driverId, dim) {
        var item = document.querySelector('#driver-grid [data-driver-id="' + driverId + '"]');
        if (!item) return;
        
        // Walk up to the .driver-grid-item wrapper
        var wrapper = item.closest('.driver-grid-item') || item;
        wrapper.style.opacity = dim ? '0.30' : '';
        wrapper.style.filter  = dim ? 'saturate(0.30)' : '';
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

    // ── Form submission ───────────────────────────────────────────────────────
    document.getElementById('picks-form').addEventListener('submit', function (e) {
        if (!picks.first || !picks.tenth || !picks.last) { e.preventDefault(); return; }
        document.getElementById('bettor-input').value = JSON.stringify({
            bets:  [parseInt(picks.first), parseInt(picks.tenth), parseInt(picks.last)],
            bonus: bonus
        });
    });

    // ── Drop-zone hover styles ────────────────────────────────────────────────
    var styleEl = document.createElement('style');
    styleEl.textContent = '.drop-zone.dz-over .drop-placeholder{border-color:#E10600!important;color:#E10600!important;}';
    document.head.appendChild(styleEl);
})();
</script>
{{-- <script>
(function () {
    var picks  = { first: null, tenth: null, last: null };
    var bonus  = {{ $bonus }};
    var dragging = null; // driver id being dragged

    // ── Global Drag Behaviour (Prevents 'not-allowed' cursor) ────────────────
    
    // 1. Make the entire document a valid drop zone to keep the 'move' cursor active
    document.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (e.dataTransfer) {
            e.dataTransfer.dropEffect = 'move';
        }
    });

    // 2. Handle drops anywhere outside the valid slots
    document.addEventListener('drop', function(e) {
        e.preventDefault();
        
        // If the drop didn't happen inside a valid .drop-zone
        if (!e.target.closest('.drop-zone') && dragging) {
            // Find if the dragged driver belongs to a slot and clear it
            for (var s in picks) {
                if (picks[s] === dragging) {
                    clearSlot(s);
                    updateBtn();
                    break;
                }
            }
        }
        dragging = null; // Reset
    });

    // ── Initialise drag on grid items ────────────────────────────────────────
    document.querySelectorAll('#driver-grid .driver-grid-item').forEach(function (wrapper) {
        var card = wrapper.querySelector('[draggable]');
        if (!card) return;
        card.addEventListener('dragstart', function (e) {
            dragging = wrapper.dataset.driverId;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', dragging); 
            e.dataTransfer.setDragImage(card, card.offsetWidth / 2, card.offsetHeight / 2);
        });
        card.addEventListener('dragend', function () { dragging = null; });
    });

    // ── Drop zone behaviour ──────────────────────────────────────────────────
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
            e.stopPropagation(); // 3. IMPORTANT: Stop the global document drop from firing!
            
            zone.classList.remove('dz-over');
            if (!dragging) return;
            var slot = zone.dataset.slot;

            // If driver is already in another slot, clear it from there first
            for (var s in picks) {
                if (picks[s] === dragging) { clearSlot(s); break; }
            }

            // If the current slot is already occupied, clear it (returns old driver to grid)
            if (picks[slot]) {
                clearSlot(slot);
            }

            picks[slot] = dragging;
            renderSlot(slot, dragging);
            dimGridItem(dragging, true);
            updateBtn();
            
            dragging = null; // Reset after successful slot placement
        });
    });

    function renderSlot(slot, driverId) {
        var zone  = document.querySelector('.drop-zone[data-slot="' + slot + '"]');
        var store = document.querySelector('#driver-store [data-store-id="' + driverId + '"]');
        if (!zone || !store) return;

        var clone = store.cloneNode(true);
        clone.style.cursor = 'grab'; // Reset cursor for the clone

        var dragger = document.createElement('div');
        dragger.draggable = true;
        dragger.dataset.driverId = driverId;
        dragger.appendChild(clone);
        
        dragger.addEventListener('dragstart', function (e) {
            dragging = driverId;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', driverId);
            e.dataTransfer.setDragImage(dragger, dragger.offsetWidth / 2, dragger.offsetHeight / 2);
            
            setTimeout(function() { dragger.style.opacity = '0'; }, 0);
        });

        // Restore opacity when the drag ends, regardless of where it dropped
        dragger.addEventListener('dragend', function (e) {
            dragger.style.opacity = '1';
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

    // ── Form submission ───────────────────────────────────────────────────────
    document.getElementById('picks-form').addEventListener('submit', function (e) {
        if (!picks.first || !picks.tenth || !picks.last) { e.preventDefault(); return; }
        document.getElementById('bettor-input').value = JSON.stringify({
            bets:  [parseInt(picks.first), parseInt(picks.tenth), parseInt(picks.last)],
            bonus: bonus
        });
    });

    // ── Drop-zone hover styles ────────────────────────────────────────────────
    var styleEl = document.createElement('style');
    styleEl.textContent = '.drop-zone.dz-over .drop-placeholder{border-color:#E10600!important;color:#E10600!important;}';
    document.head.appendChild(styleEl);
})();
</script> --}}
@endpush
