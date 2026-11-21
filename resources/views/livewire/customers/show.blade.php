@php
    $card = 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900';
    $woStatus = [
        'open' => 'کراوە', 'in_progress' => 'لە کاردایە', 'waiting_parts' => 'چاوەڕێی پارچە',
        'qc' => 'پشکنینی کوالیتی', 'completed' => 'تەواوبوو', 'delivered' => 'ڕادەستکرا', 'cancelled' => 'هەڵوەشێنرایەوە',
    ];
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="{{ $card }}">
        @if ($customer->trashed())
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                ئەم کڕیارە لە {{ $customer->deleted_at->format('Y-m-d') }} ئەرشیف کراوە. مێژووەکەی پارێزراوە.
            </div>
        @endif

        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-xl font-black {{ $customer->isCompany() ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/60 dark:text-violet-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' }}">
                    {{ mb_substr($customer->full_name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $customer->full_name }}</h1>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span class="rounded-md border border-slate-200 px-2 py-0.5 dark:border-slate-700">{{ $customer->typeLabel() }}</span>
                        <span>کڕیار لە {{ $customer->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($customer->trashed())
                    @can('customers.delete')
                        <button type="button" wire:click="restore" class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">گەڕاندنەوە</button>
                    @endcan
                @else
                    <a href="tel:{{ $customer->phone }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">📞 پەیوەندی</a>
                    @php $wa = preg_replace('/^0/', '964', $customer->whatsapp ?: $customer->phone); @endphp
                    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="rounded-xl border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-950/40">واتسئاپ</a>
                    @can('customers.update')
                        <a href="{{ route('customers.edit', $customer) }}" class="rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300">دەستکاری</a>
                    @endcan
                    @can('customers.delete')
                        <button type="button" x-on:click="if (confirm('ئەم کڕیارە ئەرشیف بکرێت؟')) $wire.archive()" class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-400 dark:hover:bg-rose-950/40">ئەرشیف</button>
                    @endcan
                @endif
            </div>
        </div>

        <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-4 text-xs dark:border-slate-800 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="font-medium text-slate-400">تەلەفۆن</dt>
                <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-200" dir="ltr">{{ $customer->phone }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-400">واتسئاپ</dt>
                <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-200" dir="ltr">{{ $customer->whatsapp ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-400">ئیمەیڵ</dt>
                <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-200" dir="ltr">{{ $customer->email ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-400">ناونیشان</dt>
                <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-200">{{ $customer->address ?? '—' }}</dd>
            </div>
            @if ($customer->isCompany())
                <div>
                    <dt class="font-medium text-slate-400">ژمارەی باج</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $customer->tax_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-400">سنووری قەرز</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-200">{{ number_format($customer->credit_limit) }} د.ع</dd>
                </div>
            @endif
        </dl>

        @if ($customer->notes)
            <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">📝 {{ $customer->notes }}</div>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['ئۆتۆمبێل', $stats['vehicles'], null],
            ['سەردان (کارنامە)', $stats['visits'], $stats['last_visit'] ? 'کۆتا: '.\Illuminate\Support\Carbon::parse($stats['last_visit'])->format('Y-m-d') : null],
            ['کۆی خەرجی', number_format($stats['total_spent']).' د.ع', null],
            ['قەرزی ماوە', number_format($stats['balance']).' د.ع', null],
        ] as [$title, $value, $sub])
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="text-[11px] font-medium text-slate-400">{{ $title }}</div>
                <div class="mt-1 text-lg font-bold {{ $loop->last && $stats['balance'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $value }}</div>
                @if ($sub) <div class="text-[11px] text-slate-400">{{ $sub }}</div> @endif
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Vehicles --}}
        <div class="{{ $card }} lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">ئۆتۆمبێلەکان</h2>
                @if (! $customer->trashed())
                    @can('vehicles.create')
                        <a href="{{ route('vehicles.create', ['customer' => $customer->id]) }}" class="rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">+ ئۆتۆمبێلی نوێ</a>
                    @endcan
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($customer->vehicles as $vehicle)
                    <a wire:key="v-{{ $vehicle->id }}" href="{{ route('vehicles.show', $vehicle->id) }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/40 dark:border-slate-700 dark:hover:border-blue-700 dark:hover:bg-blue-950/20">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-bold text-slate-900 group-hover:text-blue-700 dark:text-white dark:group-hover:text-blue-300">{{ $vehicle->title() }}</div>
                                <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">{{ $vehicle->color ?? '' }} {{ $vehicle->engineLabel() ? '· '.$vehicle->engineLabel() : '' }}</div>
                            </div>
                            <span class="rounded-md border-2 border-slate-800 bg-white px-2 py-0.5 font-mono text-xs font-bold text-slate-900 dark:border-slate-300 dark:bg-slate-100">{{ $vehicle->plate_number }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                            <span>{{ number_format($vehicle->current_mileage) }} کم</span>
                            @if ($vehicle->vin) <span class="font-mono" dir="ltr">{{ $vehicle->vin }}</span> @endif
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400 dark:border-slate-700">
                        هیچ ئۆتۆمبێلێک تۆمار نەکراوە.
                    </div>
                @endforelse
            </div>

            @if ($archivedVehicles->isNotEmpty())
                <details class="mt-4 text-xs">
                    <summary class="cursor-pointer text-slate-500 dark:text-slate-400">ئۆتۆمبێلی ئەرشیفکراو ({{ $archivedVehicles->count() }})</summary>
                    <ul class="mt-2 space-y-1">
                        @foreach ($archivedVehicles as $v)
                            <li><a href="{{ route('vehicles.show', $v->id) }}" class="text-slate-500 hover:underline">{{ $v->plate_number }} · {{ $v->title() }}</a></li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>

        {{-- Audit --}}
        <div class="{{ $card }}">
            <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">تۆماری گۆڕانکاری</h2>
            <ul class="space-y-2 text-xs">
                @forelse ($history as $log)
                    <li class="border-b border-slate-100 pb-2 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ $log->action }}</span>
                            <span class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400">{{ $log->user?->name ?? 'سیستەم' }}</div>
                    </li>
                @empty
                    <li class="text-slate-400">هیچ تۆمارێک نییە.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Recent visits --}}
    <div class="{{ $card }}">
        <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">کۆتا سەردانەکان</h2>
        @if ($recentWorkOrders->isEmpty())
            <p class="text-xs text-slate-400">هێشتا هیچ کارنامەیەک نییە. کاتێک مۆدیوڵی کارنامە کارا بێت، سەردانەکان لێرە دەردەکەون.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="text-slate-500"><tr>
                        <th class="py-2">کارنامە</th><th class="py-2">ئۆتۆمبێل</th><th class="py-2">بەروار</th><th class="py-2">دۆخ</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($recentWorkOrders as $wo)
                            <tr>
                                <td class="py-2 font-mono">{{ $wo->wo_number }}</td>
                                <td class="py-2">{{ $wo->vehicle?->plate_number }}</td>
                                <td class="py-2">{{ $wo->created_at->format('Y-m-d') }}</td>
                                <td class="py-2">{{ $woStatus[$wo->status] ?? $wo->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
