@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8">

        {{--
            Layout strategy:
            • Mobile  → flex-col; action cards come first (order-1), standings last (order-2)
            • Desktop → 2-col grid; standings on left (HTML order), action panel on right
        --}}
        <div class="flex flex-col md:grid md:[grid-template-columns:1fr_380px] gap-4 md:gap-6 items-start">

            {{-- ── STANDINGS — order-2 mobile, left column desktop ── --}}
            <div class="w-full order-2 md:order-none">
                <x-player-standings :players="$players" :rankChanges="$rankChanges" :year="$year" :round="$round" />
            </div>

            {{-- ── ACTION PANEL — order-1 mobile, right column desktop ── --}}
            <section class="w-full order-1 md:order-none flex flex-col gap-4">
                <x-next-race-card type="dashboard" :race="$race" />
                <x-submit-prompt-card :submitted="(bool) $currentPick" :race="$race" />
                <x-my-season-card :myRank="$myRank" :myScore="$myScore" :round="$round" />
            </section>
        </div>
    </div>
</div>
@endsection
