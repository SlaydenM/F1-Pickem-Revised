@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="grid gap-6 items-start" style="grid-template-columns:1fr 380px">

            {{-- LEFT: Season Standings --}}
            <x-player-standings :players="$players" :rankChanges="$rankChanges" :year="$year" :round="$round" />

            {{-- RIGHT: Action Centre --}}
            <section class="flex flex-col gap-4">
                <x-next-race-card type="dashboard" :race="$race" />
                <x-my-season-card :myRank="$myRank" :myScore="$myScore" :round="$round" />
                <x-submit-prompt-card :submitted="(bool) $currentPick" :race="$race" />
            </section>
        </div>
    </div>
</div>
@endsection
