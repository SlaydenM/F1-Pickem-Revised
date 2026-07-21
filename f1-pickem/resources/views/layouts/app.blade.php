<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>F1 Pick'em</title>
    <link rel="icon" type="image/png" href="{{ asset('f1pickem-logo-square.PNG') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,600;0,700;0,800;0,900;1,400;1,700;1,800;1,900&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-black text-white min-h-screen" style="font-family:'Inter',sans-serif;">

    {{-- Fixed gradient background --}}
    <div class="fixed -z-10 pointer-events-none" style="top:0;left:0;width:100vw;height:100vw;">
        <img src="{{ asset('slanted-gradient-3.svg') }}" alt="" class="w-full" style="object-fit:fill;" draggable="false">
    </div>

    {{-- Nav (authenticated pages only) --}}
    @auth
    @php
        $navTabs = [
            ['label' => 'Home',       'route' => 'home',       'match' => 'home'],
            ['label' => 'Next Race',  'route' => 'next-race',  'match' => 'next-race*'],
            ['label' => 'Past Races', 'route' => 'past-races', 'match' => 'past-races'],
            ['label' => 'Rules',      'route' => 'rules',      'match' => 'rules'],
        ];
    @endphp

    <header class="fixed top-0 left-0 right-0 z-50 border-b border-white/30"
            style="background:#000000;">

        {{-- ── DESKTOP NAV (md+) ───────────────────────────────────────────── --}}
        <div class="hidden md:flex items-center h-[72px] px-6 max-w-7xl mx-auto w-full">

            {{-- Wide logo --}}
            <div class="mr-auto flex-shrink-0 h-full flex items-center">
                <img src="{{ asset('f1pickem-logo-wide.PNG') }}"
                     alt="F1 Pick'em"
                     class="h-full w-auto object-contain">
            </div>

            {{-- Navigation tabs --}}
            <nav class="flex items-stretch h-full">
                @foreach($navTabs as $tab)
                    @php $isActive = request()->routeIs($tab['match']); @endphp
                    <a href="{{ route($tab['route']) }}"
                       class="relative px-6 flex items-center font-['Barlow_Condensed'] font-bold text-base uppercase tracking-widest transition-all duration-150 {{ $isActive ? 'text-white' : 'text-[#BBBBBB] hover:text-white' }}"
                       style="height:72px">
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

        {{-- ── MOBILE NAV (below md) ───────────────────────────────────────── --}}
        <div class="flex md:hidden items-center h-[60px] px-4 w-full relative">

            {{-- Hamburger button --}}
            <button id="mobile-menu-btn"
                    class="flex-shrink-0 flex flex-col justify-center items-center w-10 h-10 gap-[5px] cursor-pointer"
                    aria-label="Open menu">
                <span class="mobile-bar block w-6 h-[2px] bg-white transition-all duration-200"></span>
                <span class="mobile-bar block w-6 h-[2px] bg-white transition-all duration-200"></span>
                <span class="mobile-bar block w-6 h-[2px] bg-white transition-all duration-200"></span>
            </button>

            {{-- Square logo — centred absolutely so it doesn't shift with sidebar state --}}
            <div class="absolute left-1/2 -translate-x-1/2 h-[44px] flex items-center">
                <img src="{{ asset('f1pickem-logo-square.PNG') }}"
                     alt="F1 Pick'em"
                     class="h-full w-auto object-contain">
            </div>

            {{-- User section --}}
            <div class="ml-auto flex items-center gap-2">
                <span class="font-['Inter'] text-xs text-[#BBBBBB] hidden sm:block">{{ Auth::user()->name }}</span>
                <div class="w-8 h-8 bg-[#2a2a2a] border border-white/10 flex items-center justify-center"
                     style="clip-path:polygon(5px 0%,100% 0%,calc(100% - 5px) 100%,0% 100%)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="#BBBBBB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="font-['JetBrains_Mono'] text-[9px] text-[#BBBBBB] hover:text-white tracking-widest uppercase transition-colors cursor-pointer">
                        Out
                    </button>
                </form>
            </div>
        </div>

        {{-- ── MOBILE DROPDOWN MENU ────────────────────────────────────────── --}}
        <nav id="mobile-menu"
             class="md:hidden overflow-hidden transition-all duration-200 ease-in-out"
             style="max-height:0">
            <div class="border-t border-white/10">
                @foreach($navTabs as $tab)
                    @php $isActive = request()->routeIs($tab['match']); @endphp
                    <a href="{{ route($tab['route']) }}"
                       class="flex items-center gap-4 px-6 py-4 font-['Barlow_Condensed'] font-bold text-lg uppercase tracking-widest transition-colors
                              {{ $isActive ? 'text-white bg-[#E10600]/10 border-l-[3px] border-[#E10600]' : 'text-[#BBBBBB] border-l-[3px] border-transparent hover:text-white hover:bg-white/5' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
                <div class="px-6 py-3 border-t border-white/[0.07]">
                    <span class="font-['Inter'] text-sm text-[#BBBBBB] block mb-1">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </nav>
    </header>
    @endauth

    {{-- Page content --}}
    <main class="{{ Auth::check() ? 'pt-[60px] md:pt-[72px]' : '' }}">
        @yield('content')
    </main>

    @stack('scripts')

    @auth
    <script>
    (function () {
        var btn  = document.getElementById('mobile-menu-btn');
        var menu = document.getElementById('mobile-menu');
        var bars = btn ? btn.querySelectorAll('.mobile-bar') : [];
        var open = false;

        if (!btn || !menu) return;

        btn.addEventListener('click', function () {
            open = !open;
            menu.style.maxHeight = open ? menu.scrollHeight + 'px' : '0';

            // Animate hamburger → X
            if (open) {
                bars[0].style.transform = 'translateY(7px) rotate(45deg)';
                bars[1].style.opacity   = '0';
                bars[2].style.transform = 'translateY(-7px) rotate(-45deg)';
            } else {
                bars[0].style.transform = '';
                bars[1].style.opacity   = '';
                bars[2].style.transform = '';
            }
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (open && !btn.contains(e.target) && !menu.contains(e.target)) {
                open = false;
                menu.style.maxHeight = '0';
                bars[0].style.transform = '';
                bars[1].style.opacity   = '';
                bars[2].style.transform = '';
            }
        });
    })();
    </script>
    @endauth
</body>
</html>
