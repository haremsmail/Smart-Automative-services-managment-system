<!doctype html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'نۆرەی ئۆنلاین' }} | AutoMaster</title>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        body { font-family: 'Vazirmatn', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen bg-gradient-to-b from-blue-50 to-slate-100 text-slate-800 antialiased dark:from-slate-950 dark:to-slate-900 dark:text-slate-100">
    <header class="mx-auto flex max-w-2xl items-center gap-3 px-4 pt-6">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 font-black text-white shadow-md">AM</div>
        <div>
            <div class="text-lg font-black">Auto<span class="text-blue-600">Master</span></div>
            <div class="text-[11px] text-slate-500">نۆرەی ئۆنلاینی گەراج</div>
        </div>
    </header>
    <main class="mx-auto max-w-2xl px-4 py-6">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
