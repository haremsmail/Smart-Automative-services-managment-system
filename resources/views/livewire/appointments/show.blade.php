@php
    $card = 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900';
    $badge = [
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
        'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
        'arrived' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/50 dark:text-violet-200',
        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200',
        'cancelled' => 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        'no_show' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-200',
    ];
    $actions = [
        'scheduled' => ['✓ پشتڕاستکردنەوە', 'bg-blue-600 hover:bg-blue-700 text-white'],
        'arrived' => ['🚗 کڕیار گەیشت', 'bg-violet-600 hover:bg-violet-700 text-white'],
        'completed' => ['✅ تەواوبوو', 'bg-emerald-600 hover:bg-emerald-700 text-white'],
        'no_show' => ['✗ نەهات', 'border border-rose-300 text-rose-700 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-300'],
    ];
    $wd = \App\Models\TechnicianSchedule::WEEKDAYS;
@endphp

<div class="mx-auto max-w-5xl space-y-6">
    {{-- Header --}}
    <div class="{{ $card }}">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="font-mono text-2xl font-black text-slate-900 dark:text-white">{{ $a->reference }}</h1>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badge[$a->status] ?? '' }}">{{ $a->statusLabel() }}</span>
                    <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] dark:bg-slate-800">{{ $a->sourceLabel() }}</span>
                </div>
                <div class="mt-2 text-lg font-bold text-slate-800 dark:text-slate-100">
                    📅 {{ $wd[$a->scheduled_at->dayOfWeek] }} {{ $a->scheduled_at->format('Y-m-d') }} · ⏰ {{ $a->scheduled_at->format('H:i') }}–{{ $a->ends_at->format('H:i') }}
                </div>
                <div class="mt-1 text-xs text-slate-500">{{ $a->branch?->name }} · {{ $a->bay?->name }} · {{ $a->duration_minutes }} خولەک</div>
            </div>
            <a href="{{ route('appointments.board', ['date' => $a->scheduled_at->toDateString()]) }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs dark:border-slate-700">تابلۆ</a>
        </div>

        @if ($a->status === 'cancelled' && $a->cancel_reason)
            <div class="mt-4 rounded-xl bg-slate-100 p-3 text-xs dark:bg-slate-800">هۆکاری هەڵوەشاندنەوە: {{ $a->cancel_reason }}</div>
        @endif

        {{-- Actions --}}
        @can('appointments.manage')
            @if ($a->isActive())
                <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                    @foreach ($actions as $status => [$text, $class])
                        @if ($a->canMoveTo($status))
                            <button type="button" wire:click="setStatus('{{ $status }}')" class="rounded-xl px-4 py-2 text-xs font-semibold {{ $class }}">{{ $text }}</button>
                        @endif
                    @endforeach
                    @if (in_array($a->status, ['pending', 'scheduled']))
                        <button type="button" wire:click="$toggle('showReschedule')" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold dark:border-slate-700">🕐 گۆڕینی کات</button>
                    @endif
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="rounded-xl border border-emerald-300 px-4 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400">💬 بیرخستنەوە بە واتسئاپ</a>
                    @if ($a->canMoveTo('cancelled'))
                        <span class="flex items-center gap-1">
                            <input wire:model="cancel_reason" placeholder="هۆکاری هەڵوەشاندنەوە" class="rounded-xl border border-slate-300 px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-800">
                            <button type="button" x-on:click="if (confirm('نۆرە هەڵبوەشێنرێتەوە؟')) $wire.setStatus('cancelled')" class="rounded-xl border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-600 dark:border-rose-800">هەڵوەشاندنەوە</button>
                        </span>
                    @endif
                </div>
            @endif
        @endcan

        @if ($showReschedule)
            <div class="mt-4 rounded-xl border-2 border-blue-200 p-4 dark:border-blue-900">
                <div class="mb-2 flex items-center gap-2">
                    <input type="date" wire:model.live="new_date" min="{{ today()->toDateString() }}" class="rounded-xl border border-slate-300 px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-800">
                    <span class="text-[11px] text-slate-500">{{ count($slots) }} کاتی بەتاڵ</span>
                </div>
                <div class="grid grid-cols-5 gap-1.5 sm:grid-cols-8">
                    @foreach ($slots as $sl)
                        <button type="button" wire:click="$set('new_time', '{{ $sl['time'] }}')" class="rounded-lg border px-2 py-1.5 font-mono text-[11px] {{ $new_time === $sl['time'] ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700' }}">{{ $sl['time'] }}</button>
                    @endforeach
                </div>
                @error('new_time') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                <button type="button" wire:click="reschedule" class="mt-3 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white">پاشەکەوتکردنی کاتی نوێ</button>
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Customer & vehicle --}}
            <div class="{{ $card }} grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-[11px] text-slate-400">کڕیار</div>
                    <a href="{{ route('customers.show', $a->customer_id) }}" class="text-sm font-bold text-blue-600 hover:underline">{{ $a->customer?->full_name }}</a>
                    <div class="font-mono text-xs text-slate-500" dir="ltr">{{ $a->customer?->phone }}</div>
                    @if ($noShows >= 2)
                        <div class="mt-2 inline-block rounded-lg bg-rose-100 px-2 py-1 text-[11px] font-semibold text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">⚠️ {{ $noShows }} جار نەهاتووە</div>
                    @endif
                </div>
                <div>
                    <div class="text-[11px] text-slate-400">ئۆتۆمبێل</div>
                    <a href="{{ route('vehicles.show', $a->vehicle_id) }}" class="font-mono text-sm font-bold text-blue-600 hover:underline">{{ $a->vehicle?->plate_number }}</a>
                    <div class="text-xs text-slate-500">{{ $a->vehicle?->title() }}</div>
                </div>
            </div>

            {{-- Services --}}
            <div class="{{ $card }}">
                <h2 class="mb-3 text-sm font-bold">خزمەتگوزارییەکان</h2>
                <table class="w-full text-right text-xs">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($a->items as $item)
                            <tr>
                                <td class="py-2">{{ $item->name }}</td>
                                <td class="py-2 text-slate-500">{{ (float) $item->standard_hours }} کاتژمێر</td>
                                <td class="py-2 text-left font-semibold">{{ number_format($item->price) }} د.ع</td>
                            </tr>
                        @endforeach
                        <tr class="font-bold"><td class="py-2" colspan="2">کۆی خەمڵێنراو</td><td class="py-2 text-left">{{ number_format($a->totalPrice()) }} د.ع</td></tr>
                    </tbody>
                </table>
                @if ($a->customer_notes) <div class="mt-3 rounded-xl bg-blue-50 p-3 text-xs dark:bg-blue-950/30">💬 کڕیار: {{ $a->customer_notes }}</div> @endif
                @if ($a->internal_notes) <div class="mt-2 rounded-xl bg-slate-50 p-3 text-xs dark:bg-slate-800/60">🔒 ناوخۆ: {{ $a->internal_notes }}</div> @endif
            </div>
        </div>

        <div class="space-y-6">
            {{-- Technician --}}
            <div class="{{ $card }}">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold">👨‍🔧 وەستا</h2>
                    @can('appointments.manage')
                        @if ($a->isActive())
                            <button type="button" wire:click="$toggle('showAssign')" class="text-xs text-blue-600 hover:underline">{{ $showAssign ? 'داخستن' : '🤖 AI Dispatch' }}</button>
                        @endif
                    @endcan
                </div>
                <div class="text-sm font-semibold {{ $a->technician ? '' : 'text-amber-600' }}">{{ $a->technician?->name ?? '⚠️ هێشتا دیاری نەکراوە' }}</div>

                @if ($showAssign)
                    <div class="mt-3 space-y-2">
                        @foreach ($ranking as $i => $r)
                            <div wire:key="as-{{ $r['user']->id }}" class="rounded-xl border p-2.5 text-[11px] {{ $r['available'] ? 'border-slate-200 dark:border-slate-700' : 'border-slate-100 opacity-50 dark:border-slate-800' }}">
                                <div class="flex items-center justify-between">
                                    <b>{{ $r['available'] ? (['🥇', '🥈', '🥉'][$i] ?? '') : '⛔' }} {{ $r['user']->name }}</b>
                                    <span class="font-mono">{{ $r['score'] }}/100</span>
                                </div>
                                @foreach ($r['reasons'] as $reason)
                                    <div class="text-slate-500">{{ $reason['ok'] === true ? '✓' : ($reason['ok'] === 'partial' ? '◐' : '✗') }} {{ $reason['text'] }}</div>
                                @endforeach
                                @if ($r['available'])
                                    <button type="button" wire:click="assign('{{ $r['user']->id }}')" class="mt-1.5 rounded-lg bg-blue-600 px-3 py-1 text-[11px] font-semibold text-white">سپاردن</button>
                                @endif
                            </div>
                        @endforeach
                        @if ($a->technician)
                            <button type="button" wire:click="assign('')" class="text-[11px] text-rose-600 hover:underline">لابردنی وەستا</button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- History --}}
            <div class="{{ $card }}">
                <h2 class="mb-3 text-sm font-bold">مێژوو</h2>
                <ul class="space-y-2 text-[11px]">
                    @foreach ($history as $log)
                        <li class="border-b border-slate-100 pb-1.5 dark:border-slate-800">
                            <div class="flex justify-between"><span class="font-mono">{{ $log->action }}</span><span class="text-slate-400">{{ $log->created_at->format('m/d H:i') }}</span></div>
                            <div class="text-slate-400">{{ $log->user?->name ?? 'کڕیار (ئۆنلاین)' }} {{ isset($log->new_values['status']) ? '→ '.(\App\Models\Appointment::STATUSES[$log->new_values['status']] ?? '') : '' }}</div>
                        </li>
                    @endforeach
                </ul>
                @if ($a->creator) <div class="mt-2 text-[11px] text-slate-400">دانراو لەلایەن: {{ $a->creator->name }}</div> @endif
            </div>
        </div>
    </div>
</div>
