<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">بەڕێوەبردنی لقەکان</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">سەرجەم لقەکانی وۆرکشۆپ و ناوەندەکانی خزمەتگوزاری</p>
        </div>
        <button type="button" wire:click="create" class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            لقی نوێ
        </button>
    </div>

    @if ($showForm)
        <form wire:submit="save" class="rounded-2xl border border-blue-200 bg-white p-5 shadow-sm space-y-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs">{{ $editingId ? 'دەستکاریی لق' : 'زیادکردنی لقی نوێ' }}</h3>
                <button type="button" wire:click="cancel" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">داخستن</button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ناوی لق <span class="text-red-500">*</span></label>
                    <input wire:model="name" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
                    @error('name') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">کۆدی لق <span class="text-red-500">*</span></label>
                    <input wire:model="code" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 uppercase focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
                    @error('code') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ژمارەی تەلەفۆن</label>
                    <input wire:model="phone" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
                    @error('phone') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ئیمەیڵ</label>
                    <input wire:model="email" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800">
                    @error('email') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">ناونیشان</label>
                    <textarea wire:model="address" rows="2" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800"></textarea>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input type="checkbox" id="branch_active" wire:model="is_active" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800">
                    <label for="branch_active" class="text-xs font-medium text-slate-700 dark:text-slate-300">لقی چالاکە</label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    پاشەکەوتکردن
                </button>
                <button type="button" wire:click="cancel" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                    پاشگەزبوونەوە
                </button>
            </div>
        </form>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">ناوی لق</th>
                        <th class="px-4 py-3.5 font-semibold">کۆدی لق</th>
                        <th class="px-4 py-3.5 font-semibold">تەلەفۆن</th>
                        <th class="px-4 py-3.5 font-semibold">کارمەندەکان</th>
                        <th class="px-4 py-3.5 font-semibold">دۆخ</th>
                        <th class="px-4 py-3.5 font-semibold text-left">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($branches as $branch)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $branch->name }}</div>
                                @if($branch->address)
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 truncate max-w-xs">{{ $branch->address }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-600 dark:text-slate-300" dir="ltr">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 border border-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">{{ $branch->code }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-600 dark:text-slate-300" dir="ltr">{{ $branch->phone ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $branch->users_count }} کارمەند
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $branch->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $branch->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $branch->is_active ? 'چالاک' : 'ناچالاک' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" wire:click="edit({{ $branch->id }})" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                                        دەستکاری
                                    </button>
                                    <button type="button"
                                            class="rounded-lg border px-2.5 py-1 text-[11px] font-medium transition {{ $branch->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-800/60 dark:text-amber-400 dark:hover:bg-amber-950/40' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800/60 dark:text-emerald-400 dark:hover:bg-emerald-950/40' }}"
                                            x-on:click="if (confirm('دڵنیایت لە گۆڕینی دۆخی ئەم لقە؟')) $wire.toggleActive({{ $branch->id }})">
                                        {{ $branch->is_active ? 'ناچالاککردن' : 'چالاککردن' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                هیچ لقێک تۆمار نەکراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
