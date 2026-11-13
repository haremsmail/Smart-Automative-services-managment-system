<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $roleId ? 'دەستکاریکردنی ڕۆڵ' : 'ڕۆڵی نوێ' }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">ناوی ڕۆڵ و کۆمەڵەی دەسەڵاتەکان دەستنیشان بکە</p>
        </div>
        <a href="{{ route('roles.index') }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
            گەڕانەوە
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ناوی تەکنیکی (پیتی بچووک و _)</label>
                <input wire:model="name" dir="ltr" @disabled($is_system) class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:disabled:bg-slate-800/50">
                @error('name') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ناوی پیشاندان</label>
                <input wire:model="display_name" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                @error('display_name') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">وەسف</label>
                <textarea wire:model="description" rows="2" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"></textarea>
            </div>
        </div>

        <div>
            <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">دەسەڵاتەکان</h2>
            @foreach ($groups as $module => $permissions)
                <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="permissionGroup">
                    <div class="mb-3 flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
                        <strong dir="ltr" class="text-xs uppercase text-blue-600 dark:text-blue-400 font-bold tracking-wider">{{ $module }}</strong>
                        <button type="button" x-on:click="toggleAll()" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            هەڵبژاردن / لابردنی هەموو
                        </button>
                    </div>
                    <div class="grid gap-2.5 md:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2.5 rounded-xl border border-slate-100 p-2.5 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/60 text-xs transition cursor-pointer">
                                <input type="checkbox" value="{{ $permission->id }}" wire:model="permission_ids" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $permission->display_name }}</span>
                                <span dir="ltr" class="text-[10px] text-slate-400 dark:text-slate-500 font-mono ms-auto">{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @error('permission_ids.*') <p class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                پاشەکەوتکردن
            </button>
            <a href="{{ route('roles.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                پاشگەزبوونەوە
            </a>
        </div>
    </form>
</div>
