<!doctype html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'چوونەژوورەوە' }} | AutoMaster</title>
    
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
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 dark:bg-slate-900 p-4 text-slate-800 dark:text-slate-100 transition-colors duration-200">
    {{ $slot }}
    @livewireScripts
</body>
</html>
