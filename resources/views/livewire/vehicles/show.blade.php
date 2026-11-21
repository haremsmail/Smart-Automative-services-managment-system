@php
    $card = 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900';
    $input = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
    $types = [
        'work_order' => ['label' => 'کارنامە', 'dot' => 'bg-blue-500', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800'],
        'inspection' => ['label' => 'پشکنین', 'dot' => 'bg-amber-500', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800'],
        'warranty' => ['label' => 'گەرەنتی', 'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'],
    ];
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="{{ $card }}">
        @if ($vehicle->trashed())
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                ئەم ئۆتۆمبێلە لە {{ $vehicle->deleted_at->format('Y-m-d') }} ئەرشیف کراوە. مێژووەکەی پارێزراوە.
            </div>
        @endif

        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-lg border-[3px] border-slate-800 bg-white px-3 py-1 font-mono text-lg font-black text-slate-900 dark:border-slate-300 dark:bg-slate-100">{{ $vehicle->plate_number }}</span>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $vehicle->title() }}</h1>
                </div>
                @if ($vehicle->vin)
                    <div class="mt-2 font-mono text-xs tracking-wider text-slate-500 dark:text-slate-400" dir="ltr">VIN: {{ $vehicle->vin }}</div>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($vehicle->trashed())
                    @can('vehicles.delete')
                        <button type="button" wire:click="restore" class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">گەڕاندنەوە</button>
                    @endcan
                @else
                    @can('vehicles.update')
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300">دەستکاری</a>
                    @endcan
                    @can('vehicles.transfer')
                        <button type="button" wire:click="openTransfer" class="rounded-xl border border-violet-200 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50 dark:border-violet-800 dark:text-violet-300 dark:hover:bg-violet-950/40">گواستنەوەی خاوەندارێتی</button>
                    @endcan
                    @can('vehicles.delete')
                        <button type="button" x-on:click="if (confirm('ئەم ئۆتۆمبێلە ئەرشیف بکرێت؟')) $wire.archive()" class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-400 dark:hover:bg-rose-950/40">ئەرشیف</button>
                    @endcan
                @endif
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 text-xs dark:border-slate-800 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([
                'کیلۆمەتر' => number_format($vehicle->current_mileage).' کم',
                'ساڵ' => $vehicle->year ?? '—',
                'ڕەنگ' => $vehicle->color ?? '—',
                'سووتەمەنی' => $vehicle->engineLabel() ?? '—',
                'گێڕ' => $vehicle->transmissionLabel() ?? '—',
                'تۆمارکراو' => $vehicle->created_at->format('Y-m-d'),
            ] as $title => $value)
                <div>
                    <dt class="font-medium text-slate-400">{{ $title }}</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-200">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($vehicle->notes)
            <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">📝 {{ $vehicle->notes }}</div>
        @endif
    </div>

    {{-- Transfer panel --}}
    @if ($showTransfer)
        <div class="rounded-2xl border-2 border-violet-300 bg-violet-50/50 p-6 shadow-sm dark:border-violet-800 dark:bg-violet-950/20">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-violet-900 dark:text-violet-200">گواستنەوەی خاوەندارێتی</h2>
                <button type="button" wire:click="$set('showTransfer', false)" class="text-xs text-slate-500 hover:underline">داخستن</button>
            </div>
            <p class="mb-4 text-xs text-slate-600 dark:text-slate-300">
                خاوەنی ئێستا: <b>{{ $vehicle->customer?->full_name }}</b>.
                مێژووی چاککردنەوە لەگەڵ ئۆتۆمبێلەکە دەمێنێتەوە و گواستنەوەکە لە Audit تۆمار دەکرێت.
            </p>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-3">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">خاوەنی نوێ <span class="text-red-500">*</span></label>
                    @if ($transferTarget)
                        <div class="flex items-center justify-between rounded-xl border border-violet-200 bg-white px-4 py-2.5 dark:border-violet-800 dark:bg-slate-900">
                            <span class="text-xs font-bold">{{ $transferTarget->full_name }} <span class="font-mono font-normal text-slate-500" dir="ltr">{{ $transferTarget->phone }}</span></span>
                            <button type="button" wire:click="$set('transfer_customer_id', '')" class="text-xs text-rose-600 hover:underline">گۆڕین</button>
                        </div>
                    @else
                        <input wire:model.live.debounce.300ms="transferSearch" placeholder="ناو یان ژمارەی تەلەفۆن..." class="{{ $input }}">
                        @if ($transferResults->isNotEmpty())
                            <ul class="mt-2 divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-700 dark:bg-slate-900">
                                @foreach ($transferResults as $c)
                                    <li wire:key="t-{{ $c->id }}">
                                        <button type="button" wire:click="pickTransferCustomer({{ $c->id }})" class="flex w-full justify-between px-4 py-2 text-right text-xs hover:bg-violet-50 dark:hover:bg-slate-800">
                                            <span class="font-semibold">{{ $c->full_name }}</span>
                                            <span class="font-mono text-slate-500" dir="ltr">{{ $c->phone }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                    @error('transfer_customer_id') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">کیلۆمەتر لە کاتی گواستنەوە</label>
                    <input type="number" wire:model="transfer_mileage" min="{{ $vehicle->current_mileage }}" placeholder="{{ $vehicle->current_mileage }}" dir="ltr" class="{{ $input }}">
                    @error('transfer_mileage') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">تێبینی</label>
                    <input wire:model="transfer_note" placeholder="بۆ نموونە: فرۆشرا لە بازاڕی ئۆتۆمبێل" class="{{ $input }}">
                </div>
            </div>

            <button type="button" wire:click="transfer" wire:loading.attr="disabled" class="mt-4 rounded-xl bg-violet-600 px-5 py-2.5 text-xs font-semibold text-white hover:bg-violet-700">گواستنەوە</button>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Digital Repair History --}}
        <div class="{{ $card }} lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">مێژووی دیجیتاڵی چاککردنەوە</h2>
                <span class="text-[11px] text-slate-400">{{ $timeline->count() }} تۆمار</span>
            </div>

            @if ($timeline->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <div class="text-3xl">🔧</div>
                    <p class="mt-2 text-xs font-semibold text-slate-600 dark:text-slate-300">هێشتا هیچ مێژوویەک نییە</p>
                    <p class="mt-1 text-[11px] text-slate-400">کارنامە، پشکنین و گەرەنتییەکان خۆکارانە لێرە دەردەکەون.</p>
                </div>
            @else
                <ol class="relative border-r-2 border-slate-200 pr-6 dark:border-slate-700">
                    @foreach ($timeline as $item)
                        @php $t = $types[$item['type']]; @endphp
                        <li class="relative mb-6 last:mb-0">
                            <span class="absolute -right-[31px] top-1 h-3.5 w-3.5 rounded-full ring-4 ring-white dark:ring-slate-900 {{ $t['dot'] }}"></span>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $t['badge'] }}">{{ $t['label'] }}</span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $item['title'] }}</span>
                                @if ($item['status'])
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $item['status'] }}</span>
                                @endif
                            </div>
                            <div class="mt-1 text-[11px] text-slate-400">
                                {{ $item['date']->format('Y-m-d') }}
                                @if ($item['mileage']) · {{ number_format($item['mileage']) }} کم @endif
                                @if ($item['extra']) · {{ $item['extra'] }} @endif
                            </div>
                            @if ($item['details'])
                                <p class="mt-1.5 text-xs text-slate-600 dark:text-slate-300">{{ $item['details'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Owner --}}
            <div class="{{ $card }}">
                <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">خاوەن</h2>
                @if ($vehicle->customer)
                    <div class="text-sm font-bold text-slate-900 dark:text-white">
                        @can('customers.view')
                            <a href="{{ route('customers.show', $vehicle->customer->id) }}" class="hover:text-blue-600 hover:underline">{{ $vehicle->customer->full_name }}</a>
                        @else
                            {{ $vehicle->customer->full_name }}
                        @endcan
                    </div>
                    <div class="mt-1 font-mono text-xs text-slate-500" dir="ltr">{{ $vehicle->customer->phone }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">{{ $vehicle->customer->typeLabel() }}</div>
                @endif

                @if ($previousOwners->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <div class="mb-2 text-[11px] font-semibold text-slate-500">مێژووی خاوەندارێتی</div>
                        <ul class="space-y-1.5 text-[11px] text-slate-600 dark:text-slate-300">
                            @foreach ($previousOwners as $log)
                                <li>
                                    {{ $log->created_at->format('Y-m-d') }}:
                                    {{ $log->old_values['owner'] ?? '?' }} ← {{ $log->new_values['owner'] ?? '?' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
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
    </div>
</div>
