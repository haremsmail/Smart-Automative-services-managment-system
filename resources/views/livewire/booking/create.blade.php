@php
    $input = 'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
    $card = 'rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none';
    $wd = \App\Models\TechnicianSchedule::WEEKDAYS;
@endphp

<div class="space-y-5">
    @if ($step < 4)
        <div class="flex items-center justify-center gap-2 text-[11px] font-semibold">
            @foreach ([1 => 'خزمەتگوزاری', 2 => 'کات', 3 => 'زانیاری'] as $n => $label)
                <span class="flex items-center gap-1.5 {{ $step >= $n ? 'text-blue-600' : 'text-slate-400' }}">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $step >= $n ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700' }}">{{ $n }}</span> {{ $label }}
                </span>
                @if ($n < 3) <span class="h-px w-6 bg-slate-300"></span> @endif
            @endforeach
        </div>
    @endif

    {{-- ١. خزمەتگوزاری --}}
    @if ($step === 1)
        <div class="{{ $card }}">
            <h1 class="mb-1 text-xl font-bold">چیت پێویستە؟ 🔧</h1>
            <p class="mb-4 text-xs text-slate-500">یەک یان چەند خزمەتگوزارییەک هەڵبژێرە</p>
            @if ($branches->count() > 1)
                <select wire:model.live="branch_id" class="{{ $input }} mb-4">
                    @foreach ($branches as $b) <option value="{{ $b->id }}">📍 {{ $b->name }}</option> @endforeach
                </select>
            @endif
            @foreach ($services as $category => $items)
                <div class="mb-4">
                    <div class="mb-2 text-xs font-bold text-slate-500">{{ $category }}</div>
                    <div class="space-y-2">
                        @foreach ($items as $s)
                            <label wire:key="bs-{{ $s->id }}" class="flex cursor-pointer items-center justify-between rounded-2xl border-2 px-4 py-3 transition {{ in_array((string) $s->id, $service_ids, true) ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700' }}">
                                <span class="flex items-center gap-3 text-sm font-semibold"><input type="checkbox" wire:model.live="service_ids" value="{{ $s->id }}" class="h-4 w-4"> {{ $s->name }}</span>
                                <span class="text-left text-xs text-slate-500">{{ number_format($s->base_price) }} د.ع<br><span class="text-[10px]">~{{ (float) $s->standard_hours }} کاتژمێر</span></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @error('service_ids') <p class="mb-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            @if ($selected->isNotEmpty())
                <div class="mb-4 rounded-2xl bg-slate-50 p-3 text-sm dark:bg-slate-800">کۆی خەمڵێنراو: <b>{{ number_format($selected->sum('base_price')) }} د.ع</b></div>
            @endif
            <button type="button" wire:click="next" class="w-full rounded-2xl bg-blue-600 py-3.5 text-sm font-bold text-white shadow-md hover:bg-blue-700">دواتر ←</button>
        </div>
    @endif

    {{-- ٢. کات --}}
    @if ($step === 2)
        <div class="{{ $card }}">
            <h1 class="mb-1 text-xl font-bold">کەی دێیت؟ 📅</h1>
            <p class="mb-4 text-xs text-slate-500">تەنها کاتە بەتاڵەکان پیشان دەدرێن ({{ $onlineStart }} – {{ $onlineEnd }})</p>
            <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
                @for ($i = 0; $i < 7; $i++)
                    @php $d = today()->addDays($i); @endphp
                    <button type="button" wire:click="$set('date', '{{ $d->toDateString() }}')" class="min-w-[64px] rounded-2xl border-2 px-2 py-2 text-center {{ $date === $d->toDateString() ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700' }}">
                        <div class="text-[10px]">{{ $i === 0 ? 'ئەمڕۆ' : ($i === 1 ? 'سبەینێ' : $wd[$d->dayOfWeek]) }}</div>
                        <div class="text-sm font-bold">{{ $d->format('d') }}</div>
                    </button>
                @endfor
            </div>
            <input type="date" wire:model.live="date" min="{{ today()->toDateString() }}" max="{{ $maxDate }}" class="{{ $input }} mb-4">

            @if ($error) <p class="mb-3 rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ $error }}</p> @endif
            <div class="grid grid-cols-4 gap-2">
                @forelse ($slots as $sl)
                    <button type="button" wire:click="$set('time', '{{ $sl['time'] }}')" class="rounded-xl border-2 py-2.5 font-mono text-sm font-semibold {{ $time === $sl['time'] ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 hover:border-blue-400 dark:border-slate-700' }}">{{ $sl['time'] }}</button>
                @empty
                    <p class="col-span-full rounded-xl bg-slate-50 p-4 text-center text-sm text-slate-500 dark:bg-slate-800">ئەم ڕۆژە پڕە 😔 ڕۆژێکی تر هەڵبژێرە.</p>
                @endforelse
            </div>
            @error('time') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            <div class="mt-5 flex gap-2">
                <button type="button" wire:click="back" class="rounded-2xl border border-slate-300 px-5 py-3.5 text-sm dark:border-slate-700">→</button>
                <button type="button" wire:click="next" class="flex-1 rounded-2xl bg-blue-600 py-3.5 text-sm font-bold text-white shadow-md hover:bg-blue-700">دواتر ←</button>
            </div>
        </div>
    @endif

    {{-- ٣. زانیاری --}}
    @if ($step === 3)
        <div class="{{ $card }}">
            <h1 class="mb-1 text-xl font-bold">زانیارییەکانت 📝</h1>
            <p class="mb-4 text-xs text-slate-500">📅 {{ $date }} · ⏰ {{ $time }} · {{ $selected->pluck('name')->implode('، ') }}</p>
            <div class="space-y-3">
                <div>
                    <input wire:model="full_name" placeholder="ناوی تەواو" class="{{ $input }}">
                    @error('full_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <input wire:model="phone" dir="ltr" inputmode="tel" placeholder="0750 123 4567" class="{{ $input }} text-left">
                    @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <input wire:model="plate_number" placeholder="ژمارەی تابلۆ: 22 أ 12345" class="{{ $input }} font-mono">
                    @error('plate_number') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input wire:model="make" placeholder="جۆر: Toyota" dir="ltr" class="{{ $input }}">
                        @error('make') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <input wire:model="model" placeholder="مۆدێل: Camry" dir="ltr" class="{{ $input }}">
                        @error('model') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <textarea wire:model="customer_notes" rows="2" placeholder="کێشەکە چییە؟ (ئارەزوومەندانە)" class="{{ $input }}"></textarea>
            </div>
            <div class="mt-5 flex gap-2">
                <button type="button" wire:click="back" class="rounded-2xl border border-slate-300 px-5 py-3.5 text-sm dark:border-slate-700">→</button>
                <button type="button" wire:click="book" wire:loading.attr="disabled" class="flex-1 rounded-2xl bg-emerald-600 py-3.5 text-sm font-bold text-white shadow-md hover:bg-emerald-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="book">✓ نۆرە بگرە</span>
                    <span wire:loading wire:target="book">چاوەڕێ بکە...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- ٤. سەرکەوتوو --}}
    @if ($step === 4)
        <div class="{{ $card }} text-center">
            <div class="text-5xl">🎉</div>
            <h1 class="mt-3 text-xl font-bold">داواکارییەکەت گەیشت!</h1>
            <p class="mt-1 text-sm text-slate-500">ژمارەی نۆرەکەت:</p>
            <div class="my-3 font-mono text-3xl font-black text-blue-600">{{ $reference }}</div>
            <p class="rounded-2xl bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">⏳ گەراج بەم زووانە پەیوەندیت پێوە دەکات بۆ پشتڕاستکردنەوە.</p>
            <a href="{{ route('booking.manage', $token) }}" class="mt-4 inline-block rounded-2xl border-2 border-blue-600 px-5 py-3 text-sm font-bold text-blue-600">بینینی نۆرەکەم</a>
            <p class="mt-2 text-[11px] text-slate-400">ئەم لینکە هەڵبگرە بۆ بینین یان هەڵوەشاندنەوەی نۆرەکەت.</p>
        </div>
    @endif
</div>
