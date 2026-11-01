<div class="w-full max-w-sm rounded-2xl bg-white p-7 shadow-xl border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
    <div class="mb-6 text-center">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white font-black text-lg shadow-md mb-2">
            AM
        </div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Auto<span class="text-blue-600 dark:text-blue-400">Master</span></h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">چوونەژوورەوە بۆ سیستەمی بەڕێوەبردنی وۆرکشۆپ</p>
    </div>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">ئیمەیڵ</label>
            <input type="email" wire:model="email" dir="ltr" autofocus
                   class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:bg-slate-900">
            @error('email') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">پاسوۆرد</label>
            <input type="password" wire:model="password" dir="ltr"
                   class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:bg-slate-900">
            @error('password') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-300">
                <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700">
                <span>بیرمبێتەوە</span>
            </label>
        </div>

        <button type="submit" wire:loading.attr="disabled"
                class="w-full rounded-lg bg-blue-600 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 transition">
            <span wire:loading.remove>چوونەژوورەوە</span>
            <span wire:loading>چاوەڕێ بە...</span>
        </button>
    </form>
</div>
