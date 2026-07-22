@extends('layouts.app')

@section('content')
<div class="min-h-screen" id="drag-container">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 sm:py-8">

        {{-- Race banner --}}
        <x-next-race-card type="countdown" :race="$race" />

        {{-- Section title --}}
        <div class="flex flex-row items-start gap-3 mb-5 sm:items-center sm:gap-4">
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
                <div class="flex flex-row gap-6 py-6 px-4 justify-center">
                    @foreach([
                        ['slot' => 'first', 'label' => '1ST PLACE'],
                        ['slot' => 'tenth', 'label' => '10TH PLACE'],
                        ['slot' => 'last',  'label' => 'LAST PLACE'],
                    ] as $zone)
                        <div class="flex flex-col items-center gap-2 w-full sm:w-auto">
                            <div class="font-['Barlow_Condensed'] font-black italic text-white text-sm uppercase tracking-widest">
                                {{ $zone['label'] }}
                            </div>
                            <div class="drop-zone w-full w-[150px] h-[100px] max-sm:w-[120px] max-sm:h-[80px]"
                                 data-slot="{{ $zone['slot'] }}">
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
            <div class="flex justify-center mb-6 sm:justify-end">
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
            {{ $year }} Driver Grid · drag to pick
        </div>
        <x-driver-grid :drivers="$drivers" />
    </div>
</div>
@endsection

@push('scripts')
{{-- jQuery & jQuery UI Dependencies --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js" integrity="sha512-0bEtK0USNd96MnO4XhH8jhv3nyRF0eK87pJke6pkYf3cM0uDIhNJy9ltuzqgypoIFXw3JSuiy04tVk4AjpZdZw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<style>
    /* Prevent the page from scrolling sideways when dragging near edges */
    html, body { 
        overflow-x: hidden; 
    }

    /* Prevent mobile browsers from trying to pan the page when touching a card */
    .driver-grid-item > div, .slot-item {
        touch-action: none !important;
    }
</style>

<script>
$(document).ready(function() {
    var picks = { first: null, tenth: null, last: null };
    var bonus = {{ $bonus }}; 

    // 1. CRITICAL: Strip native HTML5 draggable attributes to prevent browser conflicts with jQuery UI
    $('[draggable]').removeAttr('draggable');

    // 2. Initialize Draggables on the Grid
    function initGridDraggables() {
        $('#driver-grid .driver-grid-item > div').draggable({
            helper: 'clone',      
            revert: 'invalid',    
            appendTo: 'body',     
            zIndex: 1000,
            scroll: false,
            containment: '#drag-container', // BOUNDARY FIX: Target your specific div
            start: function(event, ui) {
                ui.helper
                    .removeClass('transition-all duration-150 hover:scale-105')
                    .css({
                        'transition': 'none',
                        'transform': 'none'
                    });
                
                var driverId = $(this).closest('.driver-grid-item').data('driver-id');
                ui.helper.data('driver-id', driverId);

                // UX FIX: Immediately dim the original grid item
                dimGridItem(driverId, true);
            },
            stop: function(event, ui) {
                var driverId = $(this).closest('.driver-grid-item').data('driver-id');
                
                // UX FIX: If dropped in empty space, check if it's placed. If not, un-dim it.
                var isPlaced = Object.values(picks).some(function(val) { 
                    return val == driverId; 
                });
                
                if (!isPlaced) {
                    dimGridItem(driverId, false);
                }
            }
        });
    }

    initGridDraggables();

    // 3. Initialize the 3 Drop Zones
    $('.drop-zone').droppable({
        accept: '.driver-grid-item > div, .slot-item', // Accept cards from the grid OR other slots
        hoverClass: 'dz-over',
        greedy: true, // Prevents the drop event from bubbling up to the body droppable
        drop: function(event, ui) {
            var $dragged = ui.draggable;
            var $helper = ui.helper;
            
            var driverId = $helper.data('driver-id') || $dragged.data('driver-id');
            var sourceSlot = $dragged.data('slot'); // Will exist if it was moved from another slot
            var targetSlot = $(this).data('slot');

            if (!driverId) return;

            // If dragged from another slot, empty that source slot
            if (sourceSlot && sourceSlot !== targetSlot) {
                picks[sourceSlot] = null;
                renderEmptySlot(sourceSlot);
            }

            // If target slot is currently occupied, undim the old driver in the grid
            if (picks[targetSlot] && picks[targetSlot] != driverId) {
                dimGridItem(picks[targetSlot], false);
            }

            // Lock in the new pick
            picks[targetSlot] = driverId;
            
            renderFilledSlot(targetSlot, driverId);
            dimGridItem(driverId, true);
            updateBtn();
        }
    });

    // 4. Document Body Droppable (Allows user to remove a pick by dragging it out of a slot and dropping it anywhere)
    $('body').droppable({
        accept: '.slot-item',
        drop: function(event, ui) {
            var slot = ui.draggable.data('slot');
            if (slot) {
                clearSlot(slot);
                updateBtn();
            }
        }
    });

    // 5. Helper Functions
    function renderFilledSlot(slot, driverId) {
        var $zone = $('.drop-zone[data-slot="' + slot + '"]');
        var $store = $('#driver-store [data-store-id="' + driverId + '"]');
        
        if (!$zone.length || !$store.length) return;

        // Clone the clean card from the hidden store
        var $clone = $store.children().first().clone();
        
        // Add identifying properties to the clone
        $clone.addClass('slot-item')
              .data('slot', slot)
              .data('driver-id', driverId)
              .css('cursor', 'grab');

        $zone.empty().append($clone);

        // Make the new placed card draggable so it can be removed or swapped
        $clone.draggable({
            helper: 'clone',
            revert: 'invalid',
            appendTo: 'body',
            zIndex: 1000,
            scroll: false,
            containment: '#drag-container', // BOUNDARY FIX: Target your specific div here too
            start: function(event, ui) {
                $(this).css('opacity', '0.01'); // Instantly hides the original in the slot
                
                ui.helper.data('driver-id', driverId);
            },
            stop: function(event, ui) {
                $(this).css('opacity', '1'); // Restores visibility if dropped in invalid space
            }
        });
    }

    function clearSlot(slot) {
        var prev = picks[slot];
        picks[slot] = null;
        
        if (prev) { dimGridItem(prev, false); } // Un-dim in the grid
        
        renderEmptySlot(slot);
    }

    function renderEmptySlot(slot) {
        var $zone = $('.drop-zone[data-slot="' + slot + '"]');
        $zone.html('<div class="drop-placeholder w-full h-full flex items-center justify-center font-[\'JetBrains_Mono\'] text-[10px] tracking-widest border-2 border-dashed border-white/40 text-[#BBBBBB]" style="border-radius:2px">· · ·</div>');
    }

    function dimGridItem(driverId, dim) {
        var $item = $('#driver-grid [data-driver-id="' + driverId + '"]');
        if ($item.length) {
            $item.css({
                'opacity': dim ? '0.30' : '',
                'filter': dim ? 'saturate(0.30)' : ''
            });
        }
    }

    function updateBtn() {
        var ready = picks.first && picks.tenth && picks.last;
        var $btn = $('#submit-btn');
        
        $btn.prop('disabled', !ready);
        
        if (ready) {
            $btn.removeClass('bg-[#232323] text-[#BBBBBB] cursor-not-allowed')
                .addClass('bg-[#E10600] text-white cursor-pointer');
        } else {
            $btn.removeClass('bg-[#E10600] text-white cursor-pointer')
                .addClass('bg-[#232323] text-[#BBBBBB] cursor-not-allowed');
        }
    }

    // 6. Form Submission
    $('#picks-form').on('submit', function (e) {
        if (!picks.first || !picks.tenth || !picks.last) { 
            e.preventDefault(); 
            return; 
        }
        $('#bettor-input').val(JSON.stringify({
            bets:  [parseInt(picks.first), parseInt(picks.tenth), parseInt(picks.last)],
            bonus: bonus
        }));
    });
    
    // Inject dynamic CSS for the jQuery UI drop-zone hover class
    $('<style>')
        .prop('type', 'text/css')
        .html('.drop-zone.dz-over .drop-placeholder{border-color:#E10600!important;color:#E10600!important;}')
        .appendTo('head');
});
</script>
@endpush
