<!doctype html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AutoMaster' }} | AutoMaster</title>
    
    {{-- Dark Mode Early Initializer --}}
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- Vazirmatn Kurdish / Arabic Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        body { font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @livewireStyles

    {{-- Alpine: هەڵبژاردن/لابردنی هەموو دەسەڵاتەکانی یەک گرووپ --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('permissionGroup', () => ({
                boxes() {
                    return [...this.$el.querySelectorAll('input[type=checkbox]')];
                },
                toggleAll() {
                    const boxes = this.boxes();
                    const turnOn = !boxes.every((box) => box.checked);
                    boxes.forEach((box) => {
                        if (box.checked !== turnOn) {
                            box.checked = turnOn;
                            // Livewire wire:model بە change event دەخوێنێتەوە
                            box.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                },
            }));
        });
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur shadow-sm dark:border-slate-800 dark:bg-slate-900/95">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-2.5 sm:px-6">
            <div class="flex flex-wrap items-center gap-6">
                {{-- Logo --}}
                <a href="{{ route('users.index') }}" class="flex items-center gap-2 text-blue-700 dark:text-blue-400 transition">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white font-black text-sm shadow-sm">
                        AM
                    </span>
                    <span class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">Auto<span class="text-blue-600 dark:text-blue-400">Master</span></span>
                </a>

                {{-- Links --}}
                <div class="flex items-center gap-1 text-xs sm:text-sm font-medium">
                    @can('users.view')
                        <a href="{{ route('users.index') }}" class="rounded-lg px-3 py-1.5 transition {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/40 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                            کارمەندان
                        </a>
                    @endcan
                    @can('roles.manage')
                        <a href="{{ route('roles.index') }}" class="rounded-lg px-3 py-1.5 transition {{ request()->routeIs('roles.*') ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/40 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                            ڕۆڵ و دەسەڵات
                        </a>
                    @endcan
                    @can('branches.manage')
                        <a href="{{ route('branches.index') }}" class="rounded-lg px-3 py-1.5 transition {{ request()->routeIs('branches.*') ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/40 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                            لقەکان
                        </a>
                    @endcan
                    @can('audit.view')
                        <a href="{{ route('audit.index') }}" class="rounded-lg px-3 py-1.5 transition {{ request()->routeIs('audit.*') ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/40 dark:text-blue-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                            تۆماری گۆڕانکاری
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Controls, Theme Toggle & User Info --}}
            <div class="flex items-center gap-2.5 text-xs sm:text-sm">
                {{-- Dark Mode Toggle Button --}}
                <div x-data="{
                    dark: document.documentElement.classList.contains('dark'),
                    toggle() {
                        this.dark = !this.dark;
                        if (this.dark) {
                            document.documentElement.classList.add('dark');
                            localStorage.theme = 'dark';
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.theme = 'light';
                        }
                    }
                }">
                    <button type="button" @click="toggle()"
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-600 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition"
                            :title="dark ? 'دۆخی ڕووناک (Light)' : 'دۆخی تاریک (Dark)'">
                        <svg x-show="dark" class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg x-show="!dark" class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-2 rounded-full bg-slate-100 py-1 pe-3 ps-1 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-[11px] font-bold text-white">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </span>
                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50 hover:border-rose-200 dark:border-slate-700 dark:text-rose-400 dark:hover:bg-rose-950/40 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        چوونەدەرەوە
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
        {{-- Flash message toast --}}
        <div x-data="{ show: false, msg: '', ok: true, timer: null }"
             x-on:flash.window="
                msg = $event.detail.message;
                ok = $event.detail.type !== 'error';
                show = true;
                clearTimeout(timer);
                timer = setTimeout(() => show = false, 3500);
             "
             x-show="show" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-5 flex items-center gap-2 rounded-xl px-4 py-3 text-xs sm:text-sm font-medium shadow-sm border"
             :class="ok ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800'">
            <span x-text="msg"></span>
        </div>

        @if (session('success'))
            <div x-data x-init="$dispatch('flash', { message: @js(session('success')), type: 'ok' })"></div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
