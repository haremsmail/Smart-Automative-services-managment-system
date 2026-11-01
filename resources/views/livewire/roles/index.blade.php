<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">بەڕێوەبردنی ڕۆڵ و دەسەڵاتەکان</h1>
            <p class="text-sm text-slate-500">پێناسەکردنی ڕۆڵەکان، بەستنەوەی دەسەڵاتەکان، و دیاریکردنی سنوری دەسەڵاتی کارمەندان</p>
        </div>
        <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            ڕۆڵی نوێ
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-semibold">ناوی ڕۆڵ</th>
                        <th class="px-4 py-3 font-semibold">ناوی تەکنیکی</th>
                        <th class="px-4 py-3 font-semibold">ژمارەی کارمەند</th>
                        <th class="px-4 py-3 font-semibold">ژمارەی دەسەڵات</th>
                        <th class="px-4 py-3 font-semibold">جۆری ڕۆڵ</th>
                        <th class="px-4 py-3 font-semibold text-left">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($roles as $role)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $role->display_name }}</div>
                                @if($role->description)
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $role->description }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-600" dir="ltr">
                                <span class="rounded bg-slate-100 px-2 py-0.5 border border-slate-200">{{ $role->name }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">
                                    {{ $role->users_count }} کارمەند
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700 border border-blue-200">
                                    {{ $role->permissions_count }} دەسەڵات
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($role->is_system)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 border border-amber-200">
                                        سیستەم (پارێزراو)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                        تایبەت
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('roles.edit', $role) }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 transition">
                                        دەستکاری
                                    </a>
                                    @unless ($role->is_system)
                                        <button type="button" class="rounded-md border border-rose-200 px-2.5 py-1 text-[11px] font-medium text-rose-600 hover:bg-rose-50 transition"
                                                x-on:click="if (confirm('دڵنیایت لە سڕینەوەی ئەم ڕۆڵە؟')) $wire.delete({{ $role->id }})">
                                            سڕینەوە
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
