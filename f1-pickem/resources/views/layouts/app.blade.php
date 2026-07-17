<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>F1 Pick'em</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto max-w-[1400px] px-4 py-6 lg:px-8">
        <header class="mb-8 flex flex-col gap-4 rounded-[2rem] border-4 border-black bg-slate-900/95 p-6 shadow-[0_15px_45px_rgba(0,0,0,0.55)] lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="page-name">F1 Pick'em</h1>
                <p class="text-sm text-slate-400">A Laravel rewrite of the classic F1 Pick'em page.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('picks.index') }}" class="button-primary">Set Picks</a>
                    <a href="{{ route('picks.view') }}" class="button-secondary">View Picks</a>
                    <a href="{{ route('logout') }}" class="button-secondary">Logout</a>
                @else
                    <a href="{{ route('login') }}" class="button-primary">Login</a>
                @endauth
            </div>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-3xl border border-slate-200/10 bg-slate-800/80 p-4 text-slate-200">
                {{ session('status') }}
            </div>
        @endif

        <main>
            {{ $slot ?? '' }}
        </main>
    </div>
</body>
</html>
