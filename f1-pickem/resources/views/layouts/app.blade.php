<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>F1 Pick'em</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,600;0,700;0,800;0,900;1,400;1,700;1,800;1,900&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-black text-white min-h-screen" style="font-family:'Inter',sans-serif;">

    {{-- Fixed gradient background --}}
    <div class="fixed -z-10 pointer-events-none" style="top:0;left:0;width:100vw;height:100vw;">
        <img src="{{ asset('bg.svg') }}" alt="" class="w-full" style="object-fit:fill;" draggable="false">
    </div>

    {{-- Nav (authenticated pages only) --}}
    @auth
    <header class="fixed top-0 left-0 right-0 z-50 border-b border-white/30"
            style="height:72px;background:rgba(8,8,8,0.95);backdrop-filter:blur(12px);">
        <div class="max-w-7xl mx-auto h-full flex items-center px-6" style="background:#000000;">

            {{-- Logo --}}
            <div class="mr-auto flex-shrink-0">
                <span class="font-['Barlow_Condensed'] font-black italic text-white text-2xl tracking-wider uppercase">
                    F1 Pick'em
                </span>
            </div>

            {{-- Navigation tabs --}}
            <nav class="flex items-stretch h-full">
                @php
                    $navTabs = [
                        ['label' => 'Home',       'route' => 'home',       'match' => 'home'],
                        ['label' => 'Next Race',  'route' => 'next-race',  'match' => 'next-race*'],
                        ['label' => 'Past Races', 'route' => 'past-races', 'match' => 'past-races'],
                        ['label' => 'Rules',      'route' => 'rules',      'match' => 'rules'],
                    ];
                @endphp
                @foreach($navTabs as $tab)
                    @php $isActive = request()->routeIs($tab['match']); @endphp
                    <a href="{{ route($tab['route']) }}"
                       class="relative px-6 flex items-center font-['Barlow_Condensed'] font-bold text-base uppercase tracking-widest transition-all duration-150 {{ $isActive ? 'text-white' : 'text-[#BBBBBB] hover:text-white' }}">
                        @if($isActive)
                            <div class="absolute inset-0 bg-[#E10600]/[0.07]"></div>
                            <div class="absolute inset-x-0 bottom-0 h-[3px] bg-[#E10600]"
                                 style="clip-path:polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)"></div>
                        @endif
                        <span class="relative">{{ $tab['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- User + logout --}}
            <div class="ml-auto pl-6 flex items-center gap-3">
                <span class="font-['Inter'] text-sm text-[#BBBBBB]">{{ Auth::user()->name }}</span>
                <div class="w-9 h-9 bg-[#2a2a2a] border border-white/10 flex items-center justify-center"
                     style="clip-path:polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="#BBBBBB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-1">
                    @csrf
                    <button type="submit"
                            class="font-['JetBrains_Mono'] text-[10px] text-[#BBBBBB] hover:text-white tracking-widest uppercase transition-colors cursor-pointer">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>
    @endauth

    {{-- Page content --}}
    <main class="{{ Auth::check() ? 'pt-[72px]' : '' }}">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
