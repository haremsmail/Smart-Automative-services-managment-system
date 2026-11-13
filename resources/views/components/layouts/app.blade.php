<!doctype html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AutoMaster DMS' }} | سیستەمی بەڕێوەبردنی وۆرکشۆپ</title>
    
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
                            box.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                },
            }));
        });
    </script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100"
      x-data="{
          sidebarOpen: false,
          dark: document.documentElement.classList.contains('dark'),
          toggleTheme() {
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

    <div class="flex min-h-screen">
        {{-- Mobile Overlay Backdrop --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden">
        </div>

        {{-- SIDEBAR --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 right-0 z-50 flex w-72 flex-col justify-between border-l border-slate-200 bg-white transition-transform duration-300 ease-in-out dark:border-slate-800 dark:bg-slate-900 lg:static lg:translate-x-0">
            
            {{-- Sidebar Top: Brand & Logo --}}
            <div>
                <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 font-black text-white shadow-md shadow-blue-500/20">
                            AM
                        </div>
                        <div>
                            <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Auto<span class="text-blue-600 dark:text-blue-400">Master</span></span>
                            <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500">DMS • وۆرکشۆپی زیرەک</span>
                        </div>
                    </a>

                    {{-- Close Button for Mobile --}}
                    <button @click="sidebarOpen = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200 lg:hidden">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Navigation Links --}}
                <div class="space-y-6 px-3 py-4 text-xs">
                    {{-- Module 1: IAM & System --}}
                    <div>
                        <div class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            M1 · بەکارهێنەران و دەسەڵات (IAM)
                        </div>
                        <nav class="space-y-1">
                            @can('users.view')
                                <a href="{{ route('users.index') }}"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700 shadow-sm dark:bg-blue-900/40 dark:text-blue-300 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('users.*') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>کارمەندان (Users)</span>
                                </a>
                            @endcan

                            @can('roles.manage')
                                <a href="{{ route('roles.index') }}"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition {{ request()->routeIs('roles.*') ? 'bg-blue-50 text-blue-700 shadow-sm dark:bg-blue-900/40 dark:text-blue-300 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('roles.*') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>ڕۆڵ و دەسەڵات (Roles)</span>
                                </a>
                            @endcan

                            @can('branches.manage')
                                <a href="{{ route('branches.index') }}"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition {{ request()->routeIs('branches.*') ? 'bg-blue-50 text-blue-700 shadow-sm dark:bg-blue-900/40 dark:text-blue-300 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('branches.*') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>لقەکان (Branches)</span>
                                </a>
                            @endcan

                            @can('audit.view')
                                <a href="{{ route('audit.index') }}"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition {{ request()->routeIs('audit.*') ? 'bg-blue-50 text-blue-700 shadow-sm dark:bg-blue-900/40 dark:text-blue-300 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                                    <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('audit.*') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>تۆماری چاودێری (Audit)</span>
                                </a>
                            @endcan
                        </nav>
                    </div>

                    {{-- Upcoming Modules Section --}}
                    <div>
                        <div class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            مۆدیوولەکانی وۆرکشۆپ
                        </div>
                        <nav class="space-y-1">
                            <div class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                <span class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span>M2 · کڕیار و ئۆتۆمبێل</span>
                                </span>
                                <span class="rounded bg-blue-100/70 px-1.5 py-0.5 text-[9px] font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-400">دواتر</span>
                            </div>

                            <div class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                <span class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>M3 · نۆرە و شوێنی کار</span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                <span class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <span>M4 · پشکنینی دیجیتاڵی</span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                <span class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <span>M5 · کۆگا و پارچەکان</span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between rounded-xl px-3 py-2 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                <span class="flex items-center gap-3">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                                    </svg>
                                    <span>M6 · فاکتۆر و POS</span>
                                </span>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Sidebar Bottom: User Profile & Dark Mode Toggle --}}
            <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 font-bold text-white shadow-sm">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="truncate text-xs font-bold text-slate-800 dark:text-slate-100">{{ auth()->user()->name }}</div>
                            <div class="truncate text-[10px] text-slate-400 dark:text-slate-500">{{ auth()->user()->role?->display_name ?? 'بێ ڕۆڵ' }}</div>
                        </div>
                    </div>

                    {{-- Dark Mode Switcher --}}
                    <button type="button" @click="toggleTheme()"
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

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white py-2 text-xs font-semibold text-rose-600 shadow-sm hover:bg-rose-50 hover:border-rose-200 dark:border-slate-800 dark:bg-slate-800/80 dark:text-rose-400 dark:hover:bg-rose-950/40 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        چوونەدەرەوە
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT AREA --}}
        <div class="flex flex-1 flex-col overflow-x-hidden">
            {{-- Top Header on Mobile --}}
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 lg:hidden">
                <div class="flex items-center gap-2">
                    <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <span class="font-extrabold text-slate-900 dark:text-white">Auto<span class="text-blue-600 dark:text-blue-400">Master</span></span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="toggleTheme()"
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <svg x-show="dark" class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg x-show="!dark" class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </header>

            {{-- Main Container --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
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
        </div>
    </div>

    @livewireScripts
</body>
</html>
