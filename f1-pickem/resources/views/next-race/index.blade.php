@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8">

        {{-- Race banner --}}
        <x-next-race-card type="countdown" :race="$race" />

        {{-- Picks locked confirmation --}}
        <div class="flex items-center gap-3 mb-6 bg-[#0d1a0d] border border-green-900/40 px-5 py-4"
             style="clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%)">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="flex-shrink-0">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <div>
                <div class="font-['Barlow_Condensed'] font-black italic text-white text-lg uppercase">
                    Picks Locked In!
                </div>
                <div class="font-['Inter'] text-green-800 text-sm">
                    Your predictions for {{ $race?->name ?? 'this race' }} are confirmed.
                </div>
            </div>
        </div>

        {{-- My picks --}}
        @if($currentPick)
            @php
                $mySlots = [
                    ['driver' => $currentPick->d1, 'label' => '1ST',  'color' => '#FFD700'],
                    ['driver' => $currentPick->d2, 'label' => '10TH', 'color' => '#E10600'],
                    ['driver' => $currentPick->d3, 'label' => 'LAST', 'color' => '#555555'],
                ];
            @endphp
            <div class="mb-6">
                <div class="font-['Barlow_Condensed'] font-bold text-sm uppercase tracking-widest text-[#BBBBBB] mb-3">
                    Your Picks
                </div>
                {{-- 3 columns always; driver card size adapts --}}
                <div class="grid grid-cols-3 gap-2 md:gap-4">
                    @foreach($mySlots as $slot)
                        <div class="relative bg-[#1c1c1c] border border-white/[0.07] overflow-hidden"
                             style="clip-path:polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0% 100%)">
                            <div class="absolute inset-x-0 top-0 h-[3px]"
                                 style="background:{{ $slot['color'] }}"></div>
                            <div class="p-2 md:p-4">
                                <div class="font-['JetBrains_Mono'] text-[9px] md:text-[10px] tracking-widest uppercase mb-2 md:mb-3"
                                     style="color:{{ $slot['color'] }}">{{ $slot['label'] }}</div>
                                @if($slot['driver'])
                                    {{-- sm on mobile, lg on desktop --}}
                                    <div class="block md:hidden w-full">
                                        <x-driver-card :driver="$slot['driver']" size="sm" />
                                    </div>
                                    <div class="hidden md:block">
                                        <x-driver-card :driver="$slot['driver']" size="lg" />
                                    </div>
                                @else
                                    <div class="font-['Inter'] text-[#BBBBBB] text-sm">—</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Other players' picks --}}
        <x-picks-list
            type="countdown"
            :picks="$picks"
            :currentPick="$currentPick"
        />
    </div>
</div>
@endsection
