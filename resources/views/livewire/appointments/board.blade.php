@php
    $statusColor = [
        'pending' => 'border-amber-400 bg-amber-50 text-amber-900 dark:border-amber-600 dark:bg-amber-950/60 dark:text-amber-100',
        'scheduled' => 'border-blue-400 bg-blue-50 text-blue-900 dark:border-blue-600 dark:bg-blue-950/60 dark:text-blue-100',
        'arrived' => 'border-violet-400 bg-violet-50 text-violet-900 dark:border-violet-600 dark:bg-violet-950/60 dark:text-violet-100',
        'completed' => 'border-emerald-400 bg-emerald-50 text-emerald-900 dark:border-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-100',
        'no_show' => 'border-rose-300 bg-rose-50 text-rose-800 line-through dark:border-rose-700 dark:bg-rose-950/40 dark:text-rose-200',
    ];
    $heatColor = fn ($p) => $p >= 85 ? 'bg-rose-500' : ($p >= 60 ? 'bg-amber-400' : ($p >= 25 ? 'bg-emerald-400' : 'bg-emerald-200 dark:bg-emerald-900'));
    $wd = \App\Models\TechnicianSchedule::WEEKDAYS;
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">تابلۆی وۆرکشۆپ</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $wd[$day->dayOfWeek] }} · {{ $day->format('Y-m-d') }} · {{ $appointments->whereIn('status', \App\Models\Appointment::ACTIVE)->count() }} نۆرەی چالاک</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($branches->count() > 1)
                <select wire:model.live="branch_id" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-800">
                    @foreach ($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                </select>
            @endif
            <button type="button" wire:click="shiftDay(1)" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900">→</button>
            <input type="date" wire:model.live="date" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-800">
            <button type="button" wire:click="shiftDay(-1)" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900">←</button>
            <button type="button" wire:click="today" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold dark:border-slate-700 dark:bg-slate-900">ئەمڕۆ</button>
            @can('appointments.manage')
                <a href="{{ route('appointments.create', ['walkin' => 1]) }}" class="rounded-xl border border-violet-300 bg-violet-50 px-3 py-2 text-xs font-semibold text-violet-700 hover:bg-violet-100 dark:border-violet-800 dark:bg-violet-950/40 dark:text-violet-300">⚡ Walk-in</a>
                <a href="{{ route('appointments.create', ['date' => $day->toDateString()]) }}" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">+ نۆرەی نوێ</a>
            @endcan
        </div>
    </div>

    {{-- Week heatmap --}}
    <div class="grid grid-cols-7 gap-2">
        @foreach ($heat as $h)
            <button type="button" wire:click="$set('date', '{{ $h['date']->toDateString() }}')"
                    class="rounded-xl border p-2 text-center transition {{ $h['date']->isSameDay($day) ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-900' : 'border-slate-200 dark:border-slate-800' }} bg-white dark:bg-slate-900">
                <div class="text-[10px] text-slate-500">{{ $wd[$h['date']->dayOfWeek] }}</div>
                <div class="text-xs font-bold">{{ $h['date']->format('m/d') }}</div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full {{ $heatColor($h['percent']) }}" style="width: {{ max(4, $h['percent']) }}%"></div>
                </div>
                <div class="mt-1 text-[10px] text-slate-400">{{ $h['percent'] }}% · {{ $h['count'] }}</div>
            </button>
        @endforeach
    </div>

    {{-- Pending online bookings --}}
    @if ($pending->isNotEmpty())
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
            <h2 class="mb-2 text-xs font-bold text-amber-900 dark:text-amber-200">🌐 نۆرەی ئۆنلاین، چاوەڕێی پشتڕاستکردنەوە ({{ $pending->count() }})</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($pending as $p)
                    <a href="{{ route('appointments.show', $p) }}" class="rounded-xl border border-amber-300 bg-white px-3 py-2 text-[11px] hover:bg-amber-100 dark:border-amber-700 dark:bg-slate-900">
                        <b>{{ $p->scheduled_at->format('m/d H:i') }}</b> · {{ $p->customer?->full_name }} · <span class="font-mono">{{ $p->vehicle?->plate_number }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- On shift today --}}
    <div class="flex flex-wrap items-center gap-2 text-[11px]">
        <span class="font-semibold text-slate-500">وەستای ئەم ڕۆژە:</span>
        @forelse ($onShift as $t)
            <span class="rounded-lg border border-slate-200 bg-white px-2 py-1 dark:border-slate-700 dark:bg-slate-900">👨‍🔧 {{ $t->name }}</span>
        @empty
            <span class="text-rose-600">هیچ وەستایەک شەفتی نییە. <a href="{{ route('technicians.index') }}" class="underline">خشتەی شەفت</a></span>
        @endforelse
    </div>

    {{-- Board --}}
    @if ($bays->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-xs text-slate-400 dark:border-slate-700">
            ئەم لقە هیچ شوێنێکی کاری نییە. @can('bays.manage') <a href="{{ route('bays.index') }}" class="font-semibold text-blue-600 underline">شوێنی کار زیاد بکە</a> @endcan
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full min-w-[640px] table-fixed border-collapse text-right text-xs">
                <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="w-16 border-b border-slate-200 px-2 py-3 text-center text-[11px] text-slate-500 dark:border-slate-700">کات</th>
                        @foreach ($bays as $bay)
                            <th class="border-b border-r border-slate-200 px-2 py-3 dark:border-slate-700 {{ $bay->isUsable() ? '' : 'bg-amber-50 dark:bg-amber-950/30' }}">
                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ $bay->name }}</div>
                                <div class="text-[10px] font-normal text-slate-400">{{ $bay->typeLabel() }} {{ $bay->isUsable() ? '' : '· 🔧' }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $rowTime)
                        <tr class="h-11 {{ $rowTime->minute === 0 ? 'border-t border-slate-200 dark:border-slate-700' : 'border-t border-dashed border-slate-100 dark:border-slate-800' }}">
                            <td class="px-2 text-center font-mono text-[11px] {{ $rowTime->minute === 0 ? 'text-slate-600 dark:text-slate-300' : 'text-slate-300 dark:text-slate-600' }}">{{ $rowTime->format('H:i') }}</td>
                            @foreach ($bays as $bay)
                                @php $cell = $grid[$bay->id][$i] ?? null; @endphp
                                @if ($cell === 'covered')
                                    @continue
                                @elseif ($cell instanceof \App\Models\Appointment)
                                    <td rowspan="{{ $spans[$cell->id] }}" class="border-r border-slate-100 p-1 align-top dark:border-slate-800">
                                        <a href="{{ route('appointments.show', $cell) }}" class="block h-full rounded-lg border-r-4 p-2 text-[11px] leading-snug shadow-sm transition hover:shadow-md {{ $statusColor[$cell->status] ?? '' }}">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-mono font-bold">{{ $cell->vehicle?->plate_number }}</span>
                                                <span class="text-[10px] opacity-70">{{ $cell->scheduled_at->format('H:i') }}–{{ $cell->ends_at->format('H:i') }}</span>
                                            </div>
                                            <div class="truncate font-semibold">{{ $cell->customer?->full_name }}</div>
                                            <div class="truncate opacity-80">{{ $cell->requested_services }}</div>
                                            <div class="mt-1 truncate text-[10px]">{{ $cell->technician ? '👨‍🔧 '.$cell->technician->name : '⚠️ وەستا دیاری نەکراوە' }}</div>
                                        </a>
                                    </td>
                                @else
                                    <td class="border-r border-slate-100 dark:border-slate-800 {{ $bay->isUsable() ? '' : 'bg-amber-50/50 dark:bg-amber-950/10' }}">
                                        @if ($bay->isUsable() && $rowTime->isFuture())
                                            @can('appointments.manage')
                                                <a href="{{ route('appointments.create', ['date' => $rowTime->toDateString(), 'time' => $rowTime->format('H:i'), 'bay' => $bay->id]) }}" class="block h-full w-full opacity-0 transition hover:bg-blue-50 hover:opacity-100 dark:hover:bg-blue-950/30">
                                                    <span class="flex h-full items-center justify-center text-[10px] text-blue-500">+</span>
                                                </a>
                                            @endcan
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-3 text-[11px]">
            @foreach (['pending', 'scheduled', 'arrived', 'completed', 'no_show'] as $s)
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border {{ $statusColor[$s] }}"></span>{{ \App\Models\Appointment::STATUSES[$s] }}</span>
            @endforeach
        </div>
    @endif
</div>
