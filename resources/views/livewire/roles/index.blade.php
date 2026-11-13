<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">بەڕێوەبردنی ڕۆڵ و دەسەڵاتەکان</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">پێناسەکردنی ڕۆڵەکان، بەستنەوەی دەسەڵاتەکان، و دیاریکردنی سنوری دەسەڵاتی کارمەندان</p>
        </div>
        <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            ڕۆڵی نوێ
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">ناوی ڕۆڵ</th>
                        <th class="px-4 py-3.5 font-semibold">ناوی تەکنیکی</th>
                        <th class="px-4 py-3.5 font-semibold">ژمارەی کارمەند</th>
                        <th class="px-4 py-3.5 font-semibold">ژمارەی دەسەڵات</th>
                        <th class="px-4 py-3.5 font-semibold">جۆری ڕۆڵ</th>
                        <th class="px-4 py-3.5 font-semibold text-left">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($roles as $role)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $role->display_name }}</div>
                                @if($role->description)
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $role->description }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-600 dark:text-slate-300" dir="ltr">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 border border-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">{{ $role->name }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $role->users_count }} کارمەند
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800">
                                    {{ $role->permissions_count }} دەسەڵات
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($role->is_system)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800">
                                        سیستەم (پارێزراو)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        تایبەت
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('roles.edit', $role) }}" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                                        دەستکاری
                                    </a>
                                    @if (! $role->is_system)
                                        <button type="button"
                                                class="rounded-lg border border-rose-200 px-2.5 py-1 text-[11px] font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-800/60 dark:text-rose-400 dark:hover:bg-rose-950/40 transition"
                                                x-on:click="if (confirm('دڵنیایت لە سڕینەوەی ئەم ڕۆڵە؟')) $wire.delete({{ $role->id }})">
                                            سڕینەوە
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
