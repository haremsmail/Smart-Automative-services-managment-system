@php
    $input = 'w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
    $card = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900';
    $step = 'mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white';
    $num = 'flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-[11px] text-white';
    $wd = \App\Models\TechnicianSchedule::WEEKDAYS;
@endphp

<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $source === 'walk_in' ? '⚡ Walk-in (بەبێ نۆرە)' : 'نۆرەی نوێ' }}</h1>
            <p class="text-xs text-slate-500">کڕیار ← خزمەتگوزاری ← کات ← وەستا (AI)</p>
        </div>
        <a href="{{ route('appointments.board') }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs dark:border-slate-700">گەڕانەوە</a>
    </div>

    <div class="grid gap-5 lg:grid-cols-5">
        <div class="space-y-5 lg:col-span-3">
            {{-- ١. کڕیار --}}
            <div class="{{ $card }}">
                <div class="{{ $step }}"><span class="{{ $num }}">١</span> کڕیار و ئۆتۆمبێل</div>
                @if ($customer)
                    <div class="flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50/60 px-4 py-3 dark:border-blue-900 dark:bg-blue-950/30">
                        <div>
                            <div class="text-sm font-bold">{{ $customer->full_name }}</div>
                            <div class="font-mono text-[11px] text-slate-500" dir="ltr">{{ $customer->phone }}</div>
                        </div>
                        <button type="button" wire:click="clearCustomer" class="text-xs text-rose-600 hover:underline">گۆڕین</button>
                    </div>
                    @if ($noShows >= 2)
                        <div class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[11px] font-semibold text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300">
                            ⚠️ مەترسیی نەهاتن: ئەم کڕیارە {{ $noShows }} جار نۆرەی گرتووە و نەهاتووە. باشترە پێشتر پەیوەندی پێوە بکەیت.
                        </div>
                    @endif
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @forelse ($customer->vehicles as $v)
                            <label wire:key="veh-{{ $v->id }}" class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-xs {{ (string) $v->id === $vehicle_id ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="vehicle_id" value="{{ $v->id }}">
                                <span class="font-mono font-bold">{{ $v->plate_number }}</span>
                                <span class="text-slate-500">{{ $v->title() }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-slate-500">ئەم کڕیارە ئۆتۆمبێلی نییە. <a href="{{ route('vehicles.create', ['customer' => $customer->id]) }}" class="font-semibold text-blue-600 underline">ئۆتۆمبێل زیاد بکە</a></p>
                        @endforelse
                    </div>
                    @error('vehicle_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                @else
                    <input wire:model.live.debounce.300ms="customerSearch" placeholder="ناو، تەلەفۆن یان تابلۆ..." class="{{ $input }}" autofocus>
                    @if ($customerResults->isNotEmpty())
                        <ul class="mt-2 divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 dark:divide-slate-800 dark:border-slate-700">
                            @foreach ($customerResults as $c)
                                <li wire:key="cr-{{ $c->id }}">
                                    <button type="button" wire:click="selectCustomer({{ $c->id }})" class="flex w-full items-center justify-between px-4 py-2 text-right text-xs hover:bg-blue-50 dark:hover:bg-slate-800">
                                        <span><b>{{ $c->full_name }}</b> <span class="font-mono text-slate-500" dir="ltr">{{ $c->phone }}</span></span>
                                        <span class="font-mono text-[11px] text-slate-400">{{ $c->vehicles->pluck('plate_number')->implode(' · ') }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @elseif (mb_strlen(trim($customerSearch)) >= 2)
                        <p class="mt-2 text-xs text-slate-500">نەدۆزرایەوە. <a href="{{ route('customers.create') }}" class="font-semibold text-blue-600 underline">کڕیاری نوێ</a></p>
                    @endif
                    @error('customer_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                @endif
            </div>

            {{-- ٢. خزمەتگوزاری --}}
            <div class="{{ $card }}">
                <div class="{{ $step }}"><span class="{{ $num }}">٢</span> خزمەتگوزارییەکان</div>
                @if ($branches->count() > 1)
                    <select wire:model.live="branch_id" class="{{ $input }} mb-3">
                        @foreach ($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                    </select>
                @endif
                @forelse ($services as $category => $items)
                    <div class="mb-3">
                        <div class="mb-1.5 text-[11px] font-bold text-slate-500">{{ $category }}</div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($items as $s)
                                <label wire:key="s-{{ $s->id }}" class="flex cursor-pointer items-center justify-between gap-2 rounded-xl border px-3 py-2 text-xs {{ in_array((string) $s->id, $service_ids, true) ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40' : 'border-slate-200 dark:border-slate-700' }}">
                                    <span class="flex items-center gap-2"><input type="checkbox" wire:model.live="service_ids" value="{{ $s->id }}"> {{ $s->name }}</span>
                                    <span class="whitespace-nowrap text-[10px] text-slate-500">{{ (float) $s->standard_hours }}h · {{ number_format($s->base_price) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">هیچ خزمەتگوزارییەک نییە. @can('services.manage') <a href="{{ route('services.index') }}" class="text-blue-600 underline">زیاد بکە</a> @endcan</p>
                @endforelse
                @error('service_ids') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                @if ($reqError) <p class="mt-2 rounded-lg bg-rose-50 p-2 text-xs text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">{{ $reqError }}</p> @endif
                @if ($req)
                    <div class="mt-2 flex flex-wrap gap-3 rounded-xl bg-slate-50 p-3 text-[11px] dark:bg-slate-800/60">
                        <span>⏱️ ماوە: <b>{{ $req['minutes'] }} خولەک</b></span>
                        <span>💰 کۆ: <b>{{ number_format($selected->sum('base_price')) }} د.ع</b></span>
                        <span>🏗️ شوێن: <b>{{ config('scheduling.bay_types.'.$req['bay_type']) }}</b></span>
                    </div>
                @endif
            </div>

            {{-- ٣. کات --}}
            @if ($req)
                <div class="{{ $card }}">
                    <div class="{{ $step }}"><span class="{{ $num }}">٣</span> کات</div>

                    @if ($suggestions)
                        <div class="mb-3">
                            <div class="mb-1.5 text-[11px] font-bold text-emerald-700 dark:text-emerald-400">✨ نزیکترین کاتە بەتاڵەکان</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($suggestions as $sg)
                                    <button type="button" wire:click="pickSlot('{{ $sg['start']->toDateString() }}', '{{ $sg['time'] }}')"
                                            class="rounded-xl border px-3 py-2 text-xs font-semibold {{ $date === $sg['start']->toDateString() && $time === $sg['time'] ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                                        {{ $sg['start']->isToday() ? 'ئەمڕۆ' : ($sg['start']->isTomorrow() ? 'سبەینێ' : $wd[$sg['start']->dayOfWeek].' '.$sg['start']->format('m/d')) }} {{ $sg['time'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2">
                        <input type="date" wire:model.live="date" min="{{ today()->toDateString() }}" class="{{ $input }} w-auto">
                        <span class="text-[11px] text-slate-500">{{ count($slots) }} کاتی بەتاڵ</span>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-1.5 sm:grid-cols-6">
                        @forelse ($slots as $sl)
                            <button type="button" wire:click="$set('time', '{{ $sl['time'] }}')" title="{{ $sl['technicians'] }} وەستا · {{ $sl['bays'] }} شوێن"
                                    class="rounded-lg border px-2 py-1.5 font-mono text-[11px] {{ $time === $sl['time'] ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 hover:border-blue-400 dark:border-slate-700' }}">
                                {{ $sl['time'] }}
                            </button>
                        @empty
                            <p class="col-span-full text-xs text-slate-500">ئەم ڕۆژە هیچ کاتێکی بەتاڵ نییە (شوێن یان وەستا بەردەست نییە).</p>
                        @endforelse
                    </div>
                    @error('time') <p class="mt-2 rounded-lg bg-rose-50 p-2 text-xs text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">{{ $message }}</p> @enderror

                    <div class="mt-3">
                        <label class="mb-1 block text-[11px] text-slate-500">شوێنی کار</label>
                        <select wire:model="bay_id" class="{{ $input }}">
                            <option value="">🤖 خۆکار (یەکەم شوێنی بەتاڵ)</option>
                            @foreach ($bays as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>

        {{-- ٤. AI Dispatch --}}
        <div class="space-y-5 lg:col-span-2">
            <div class="{{ $card }} lg:sticky lg:top-4">
                <div class="{{ $step }}"><span class="{{ $num }}">٤</span> 🤖 AI Dispatch: باشترین وەستا</div>

                @if ($ranking->isEmpty())
                    <p class="text-xs text-slate-400">پاش هەڵبژاردنی خزمەتگوزاری و کات، وەستاکان لێرە خاڵ دەدرێن و ڕیز دەکرێن.</p>
                @else
                    <label class="mb-2 flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-xs {{ $technician_id === 'auto' ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40' : 'border-slate-200 dark:border-slate-700' }}">
                        <input type="radio" wire:model.live="technician_id" value="auto"> <b>خۆکار:</b> باشترین وەستای بەردەست
                    </label>
                    <div class="space-y-2">
                        @foreach ($ranking as $i => $r)
                            <label wire:key="rk-{{ $r['user']->id }}" class="block rounded-xl border p-3 text-xs {{ $r['available'] ? 'cursor-pointer' : 'opacity-50' }} {{ $technician_id === (string) $r['user']->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40' : 'border-slate-200 dark:border-slate-700' }}">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2 font-bold">
                                        <input type="radio" wire:model.live="technician_id" value="{{ $r['user']->id }}" @disabled(! $r['available'])>
                                        {{ $r['available'] ? (['🥇', '🥈', '🥉'][$i] ?? '👨‍🔧') : '⛔' }} {{ $r['user']->name }}
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $r['score'] >= 75 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : ($r['score'] >= 50 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800') }}">{{ $r['score'] }}/100</span>
                                </div>
                                <ul class="mt-2 space-y-0.5 text-[11px] text-slate-600 dark:text-slate-300">
                                    @foreach ($r['reasons'] as $reason)
                                        <li class="flex justify-between gap-2">
                                            <span>{{ $reason['ok'] === true ? '✓' : ($reason['ok'] === 'partial' ? '◐' : '✗') }} {{ $reason['text'] }}</span>
                                            <span class="whitespace-nowrap font-mono text-slate-400">{{ $reason['points'] }}/{{ $reason['max'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </label>
                        @endforeach
                    </div>
                    <label class="mt-2 flex cursor-pointer items-center gap-2 text-[11px] text-slate-500">
                        <input type="radio" wire:model.live="technician_id" value=""> دواتر دیاری دەکرێت
                    </label>
                @endif
            </div>

            <div class="{{ $card }} space-y-3">
                <div>
                    <label class="mb-1 block text-[11px] text-slate-500">سەرچاوە</label>
                    <select wire:model="source" class="{{ $input }}">
                        @foreach (\App\Models\Appointment::SOURCES as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                    </select>
                </div>
                <textarea wire:model="customer_notes" rows="2" placeholder="قسەی کڕیار: دەنگێک لە بریک دێت..." class="{{ $input }}"></textarea>
                <textarea wire:model="internal_notes" rows="2" placeholder="تێبینی ناوخۆیی (کڕیار نایبینێت)" class="{{ $input }}"></textarea>
                <button type="button" wire:click="save" wire:loading.attr="disabled" class="w-full rounded-xl bg-blue-600 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">✓ دانانی نۆرە {{ $time ? '· '.$time : '' }}</span>
                    <span wire:loading wire:target="save">چاوەڕێ بکە...</span>
                </button>
            </div>
        </div>
    </div>
</div>
