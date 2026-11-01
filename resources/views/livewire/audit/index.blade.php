<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 shadow-sm border border-blue-100 dark:bg-blue-950/60 dark:text-blue-400 dark:border-blue-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">تۆماری گۆڕانکاری و چالاکییەکان</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">چاودێری و پشکنینی هەموو کرداری چوونەژوورەوە، گۆڕینی ڕۆڵ، و دەستکارییەکانی ناو سیستەم</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                کۆی گشتی: {{ number_format($logs->total()) }} تۆمار
            </span>
            @if($action !== '' || $user_id !== '' || $from !== '' || $to !== '')
                <button wire:click="resetFilters" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/60 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    پاککردنەوەی فلتەرەکان
                </button>
            @endif
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Action Filter --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">کردار (Action)</label>
                <div class="relative">
                    <input type="text"
                           wire:model.live.debounce.300ms="action"
                           list="action-suggestions"
                           placeholder="کردار (وەک user.created)..."
                           class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900/60 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:bg-slate-900">
                    <datalist id="action-suggestions">
                        <option value="user.created">دروستکردنی کارمەند</option>
                        <option value="user.updated">دەستکاریی کارمەند</option>
                        <option value="user.status">دۆخی کارمەند</option>
                        <option value="role.assigned">دیاریکردنی ڕۆڵ</option>
                        <option value="permission.granted">پێدانی دەسەڵات</option>
                        <option value="permission.revoked">لێسەندنەوەی دەسەڵات</option>
                        <option value="role.created">دروستکردنی ڕۆڵ</option>
                        <option value="role.updated">دەستکاریی ڕۆڵ</option>
                        <option value="role.permissions">دەسەڵاتی ڕۆڵ</option>
                        <option value="role.deleted">سڕینەوەی ڕۆڵ</option>
                        <option value="branch.created">دروستکردنی لق</option>
                        <option value="branch.updated">دەستکاریی لق</option>
                        <option value="login.success">چوونەژوورەوەی سەرکەوتوو</option>
                        <option value="login.failed">شکستی چوونەژوورەوە</option>
                    </datalist>
                </div>
            </div>

            {{-- User Filter --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">کارمەند (ئەنجامدەر)</label>
                <select wire:model.live="user_id" class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:bg-slate-900">
                    <option value="">هەموو کارمەندەکان</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">لە بەرواری</label>
                <input type="date" wire:model.live="from" class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:bg-slate-900">
            </div>

            {{-- Date To --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">تا بەرواری</label>
                <input type="date" wire:model.live="to" class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:bg-slate-900">
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50/90 text-slate-600 border-b border-slate-200 dark:bg-slate-800/90 dark:text-slate-300 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold">کات و بەروار</th>
                        <th class="px-4 py-3 font-semibold">ئەنجامدەر</th>
                        <th class="px-4 py-3 font-semibold">کردار</th>
                        <th class="px-4 py-3 font-semibold">بابەت</th>
                        <th class="px-4 py-3 font-semibold min-w-[200px]">بەهای پێشوو (Before)</th>
                        <th class="px-4 py-3 font-semibold min-w-[200px]">بەهای نوێ (After)</th>
                        <th class="px-4 py-3 font-semibold">ناونیشانی IP</th>
                        <th class="px-4 py-3 font-semibold text-center">وردەکاری</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($logs as $log)
                        @php
                            $meta = $this->getActionMeta($log->action);
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                            {{-- Date & Time --}}
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-300">
                                <div class="font-medium text-slate-900 dark:text-slate-100" dir="ltr">{{ $log->created_at->format('Y-m-d') }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500" dir="ltr">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>

                            {{-- Actor --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($log->user)
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                                            {{ mb_substr($log->user->name, 0, 1) }}
                                        </div>
                                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        سیستەم
                                    </span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $meta['label'] }}</span>
                                    <span class="inline-block rounded border px-1.5 py-0.5 font-mono text-[11px] {{ $meta['bg'] }} dark:bg-opacity-20" dir="ltr">
                                        {{ $log->action }}
                                    </span>
                                </div>
                            </td>

                            {{-- Target / Subject --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($log->model_type)
                                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-slate-700 border border-slate-200 dark:bg-slate-700/60 dark:text-slate-200 dark:border-slate-600">
                                        <span class="font-medium">{{ $this->getModelLabel($log->model_type) }}</span>
                                        <span class="font-mono text-slate-500 dark:text-slate-400">#{{ $log->model_id }}</span>
                                    </span>
                                    <span class="sr-only">{{ $log->model_type.'#'.$log->model_id }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>

                            {{-- Old Values (Formatted) --}}
                            <td class="px-4 py-3">
                                @if (!empty($log->old_values))
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach ($log->old_values as $key => $val)
                                            <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700 border border-slate-200 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-200">
                                                <span class="text-slate-500 dark:text-slate-400">{{ $this->getFieldLabel($key) }}:</span>
                                                <span class="font-semibold text-slate-900 dark:text-slate-100" dir="ltr">
                                                    @if(is_array($val))
                                                        {{ implode(', ', $val) }}
                                                    @elseif(is_bool($val))
                                                        {{ $val ? 'بەڵێ' : 'نەخێر' }}
                                                    @elseif(is_null($val))
                                                        <span class="text-slate-400 font-normal">بەتاڵ</span>
                                                    @else
                                                        {{ $val }}
                                                    @endif
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 font-mono dark:text-slate-500">—</span>
                                @endif
                            </td>

                            {{-- New Values (Formatted) --}}
                            <td class="px-4 py-3">
                                @if (!empty($log->new_values))
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach ($log->new_values as $key => $val)
                                            <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-[11px] text-blue-800 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800">
                                                <span class="text-blue-600 dark:text-blue-400">{{ $this->getFieldLabel($key) }}:</span>
                                                <span class="font-semibold text-blue-950 dark:text-blue-200" dir="ltr">
                                                    @if(is_array($val))
                                                        {{ implode(', ', $val) }}
                                                    @elseif(is_bool($val))
                                                        {{ $val ? 'بەڵێ' : 'نەخێر' }}
                                                    @elseif(is_null($val))
                                                        <span class="text-blue-400 font-normal">بەتاڵ</span>
                                                    @else
                                                        {{ $val }}
                                                    @endif
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 font-mono dark:text-slate-500">—</span>
                                @endif
                            </td>

                            {{-- IP Address --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($log->ip_address)
                                    <span class="inline-block rounded bg-slate-100 px-2 py-0.5 font-mono text-[11px] text-slate-600 dark:bg-slate-700 dark:text-slate-300" dir="ltr">
                                        {{ $log->ip_address }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>

                            {{-- View Details Modal Trigger --}}
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <button type="button"
                                        wire:click="viewDetails({{ $log->id }})"
                                        class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600 transition">
                                    <svg class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    وردەکاری
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">هیچ تۆمارێک بەم فلتەرانە نەدۆزرایەوە.</span>
                                    @if($action !== '' || $user_id !== '' || $from !== '' || $to !== '')
                                        <button wire:click="resetFilters" class="mt-2 text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400">
                                            سڕینەوەی فلتەرەکان
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

    {{-- Details Modal (When clicked) --}}
    @if ($selectedLog)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm"
             wire:keydown.escape="viewDetails(null)">
            <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">وردەکاریی تۆمار #{{ $selectedLog->id }}</h3>
                            <span class="text-xs text-slate-500 font-mono dark:text-slate-400" dir="ltr">{{ $selectedLog->action }}</span>
                        </div>
                    </div>
                    <button wire:click="viewDetails(null)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="space-y-4 px-6 py-5 text-xs text-slate-700 dark:text-slate-300 max-h-[75vh] overflow-y-auto">
                    {{-- Key Info Cards --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-200 dark:bg-slate-900/60 dark:border-slate-700">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">ئەنجامدەر</div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $selectedLog->user?->name ?? 'سیستەم' }}</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-200 dark:bg-slate-900/60 dark:border-slate-700">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">کات و بەروار</div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" dir="ltr">{{ $selectedLog->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-200 dark:bg-slate-900/60 dark:border-slate-700">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">بابەت</div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $this->getModelLabel($selectedLog->model_type) }} #{{ $selectedLog->model_id ?? '—' }}</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-200 dark:bg-slate-900/60 dark:border-slate-700">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">ناونیشانی IP</div>
                            <div class="font-mono font-semibold text-slate-800 dark:text-slate-200 mt-0.5" dir="ltr">{{ $selectedLog->ip_address ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Values Diff (Before & After) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Before --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 dark:border-slate-700 dark:bg-slate-900/40">
                            <div class="mb-2 font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                بەهای پێشوو (Old Values)
                            </div>
                            @if (!empty($selectedLog->old_values))
                                <pre class="overflow-x-auto rounded-lg bg-white p-3 text-[11px] font-mono text-slate-800 border border-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-200 leading-relaxed" dir="ltr">{{ json_encode($selectedLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <div class="p-3 text-center text-slate-400 bg-white rounded-lg border border-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-500 italic">هیچ بەهایەکی پێشوو نییە</div>
                            @endif
                        </div>

                        {{-- After --}}
                        <div class="rounded-xl border border-blue-200 bg-blue-50/40 p-3.5 dark:border-blue-900/60 dark:bg-blue-950/20">
                            <div class="mb-2 font-bold text-blue-900 dark:text-blue-300 flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                بەهای نوێ (New Values)
                            </div>
                            @if (!empty($selectedLog->new_values))
                                <pre class="overflow-x-auto rounded-lg bg-white p-3 text-[11px] font-mono text-blue-900 border border-blue-100 dark:bg-slate-900 dark:border-slate-700 dark:text-blue-300 leading-relaxed" dir="ltr">{{ json_encode($selectedLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <div class="p-3 text-center text-slate-400 bg-white rounded-lg border border-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-500 italic">هیچ بەهایەکی نوێ نییە</div>
                            @endif
                        </div>
                    </div>

                    @if ($selectedLog->user_agent)
                        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-200 text-[11px] text-slate-500 dark:bg-slate-900/60 dark:border-slate-700 dark:text-slate-400">
                            <span class="font-semibold text-slate-600 dark:text-slate-300">User Agent:</span>
                            <span class="font-mono break-all" dir="ltr">{{ $selectedLog->user_agent }}</span>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="border-t border-slate-200 bg-slate-50/50 dark:border-slate-700 dark:bg-slate-800/80 px-6 py-3 rounded-b-2xl flex justify-end">
                    <button type="button"
                            wire:click="viewDetails(null)"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600 transition">
                        داخستن
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
