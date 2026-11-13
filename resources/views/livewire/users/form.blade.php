<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $userId ? 'دەستکاریکردنی کارمەند' : 'کارمەندی نوێ' }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">زانیارییەکانی هەژمار، لقی کارکردن، و ڕۆڵی سیستەم پڕبکەرەوە</p>
        </div>
        <a href="{{ route('users.index') }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
            گەڕانەوە
        </a>
    </div>

    <form wire:submit="save" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ناو <span class="text-red-500">*</span></label>
            <input wire:model="name" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
            @error('name') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ئیمەیڵ <span class="text-red-500">*</span></label>
            <input type="email" wire:model="email" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
            @error('email') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">تەلەفۆن</label>
            <input wire:model="phone" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
            @error('phone') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">لق</label>
            <select wire:model="branch_id" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="">— هیچ —</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branch_id') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">تێچووی کاتژمێر (Hourly Rate)</label>
            <input type="number" step="0.01" min="0" wire:model="hourly_rate" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
            @error('hourly_rate') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        @can('users.assign_role')
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ڕۆڵ</label>
                <select wire:model="role_id" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">بێ ڕۆڵ</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                    @endforeach
                </select>
                @error('role_id') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
        @endcan

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">دۆخی هەژمار</label>
            <select wire:model="status" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="active">چالاک</option>
                <option value="inactive">ناچالاک</option>
            </select>
            @error('status') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="col-span-full border-t border-slate-100 pt-3 dark:border-slate-800">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white">
                {{ $userId ? 'گۆڕینی وشەی تێپەڕ (ئارەزوومەندانە)' : 'وشەی تێپەڕ (Password)' }}
            </h3>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">وشەی تێپەڕ</label>
            <input type="password" wire:model="password" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
            @error('password') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">دووبارەکردنەوەی وشەی تێپەڕ</label>
            <input type="password" wire:model="password_confirmation" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
        </div>

        <div class="col-span-full flex items-center gap-3 pt-3">
            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                پاشەکەوتکردن
            </button>
            <a href="{{ route('users.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                پاشگەزبوونەوە
            </a>
        </div>
    </form>
</div>
