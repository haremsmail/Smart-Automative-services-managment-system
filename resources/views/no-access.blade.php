<x-layouts.guest>
    <x-slot:title>دەسەڵات نییە</x-slot:title>

    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="text-4xl">🔒</div>
        <h1 class="mt-3 text-lg font-bold text-slate-900 dark:text-white">سڵاو {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            هێشتا هیچ دەسەڵاتێکت پێ نەدراوە. تکایە پەیوەندی بە بەڕێوەبەرەوە بکە بۆ ئەوەی ڕۆڵێکت بۆ دیاری بکات.
        </p>
        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-slate-700">چوونەدەرەوە</button>
        </form>
    </div>
</x-layouts.guest>
