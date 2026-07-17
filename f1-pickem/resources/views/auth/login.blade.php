<x-layouts.app>
    <div class="mx-auto max-w-xl rounded-[2rem] border border-slate-500/30 bg-slate-900/90 p-8 shadow-2xl">
        <h2 class="mb-4 text-4xl font-semibold text-slate-100">Login to F1 Pick'em</h2>

        @if ($errors->any())
            <div class="mb-4 rounded-3xl bg-rose-900/90 p-4 text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
            @csrf
            <div class="space-y-2">
                <label for="username" class="block text-sm font-medium text-slate-200">Username</label>
                <input id="username" name="username" type="text" required autofocus
                    class="w-full rounded-3xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-slate-100 shadow-inner focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/30" />
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-slate-200">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-3xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-slate-100 shadow-inner focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/30" />
            </div>

            <button type="submit" class="button-primary w-full">Login</button>
        </form>
    </div>
</x-layouts.app>
