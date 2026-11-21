@php
    $input = 'rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
    $card = 'rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900';
    $chip = [
        'amber' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700',
        'blue' => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700',
        'emerald' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-700',
        'violet' => 'bg-violet-100 text-violet-800 border-violet-300 dark:bg-violet-900/40 dark:text-violet-200 dark:border-violet-700',
        'rose' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/40 dark:text-rose-200 dark:border-rose-700',
        'slate' => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600',
    ];
    $tabs = ['roster' => '📅 خشتەی شەفت', 'skills' => '⭐ شارەزایی', 'timeoff' => '🏖️ مۆڵەت', 'settings' => '⚙️ جۆری شەفت و ئۆنلاین'];
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">وەستاکان و شەفت</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">بەڕێوەبەر دیاری دەکات کێ کەی کاردەکات (ڕۆژ، شەو، تەواو) و شارەزایی هەر وەستایەک چییە</p>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $key => $text)
            <button type="button" wire:click="$set('tab', '{{ $key }}')" class="rounded-xl px-4 py-2 text-xs font-semibold transition {{ $tab === $key ? 'bg-blue-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">{{ $text }}</button>
        @endforeach
    </div>

    @if ($technicians->isEmpty() && $tab !== 'settings')
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
            هیچ وەستایەک نییە. لە پەڕەی <a href="{{ route('users.index') }}" class="font-bold underline">کارمەندان</a> کارمەندێک بە ڕۆڵی «وەستا» دروست بکە.
        </div>
    @endif

    {{-- ===================== خشتەی شەفت ===================== --}}
    @if ($tab === 'roster' && $technicians->isNotEmpty())
        @if ($shifts->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                سەرەتا لە تابی «جۆری شەفت» شەفتێک دروست بکە (بۆ نموونە ☀️ ڕۆژ ٠٨:٠٠–١٦:٠٠).
            </div>
        @endif
        <div class="{{ $card }} overflow-x-auto">
            <table class="w-full min-w-[800px] text-right text-xs">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 font-semibold">وەستا</th>
                        @foreach ($weekdays as $wd => $name)
                            <th class="px-2 py-3 text-center font-semibold">{{ $name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($technicians as $tech)
                        <tr wire:key="roster-{{ $tech->id }}">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $tech->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $tech->branch?->name ?? 'هەموو لقەکان' }}</div>
                            </td>
                            @foreach ($weekdays as $wd => $name)
                                @php $current = $tech->schedules->firstWhere('weekday', $wd)?->shift_id; $cs = $shifts->firstWhere('id', $current); @endphp
                                <td class="px-1.5 py-2 text-center">
                                    <select wire:key="cell-{{ $tech->id }}-{{ $wd }}" wire:change="setSchedule({{ $tech->id }}, {{ $wd }}, $event.target.value)"
                                            class="w-full rounded-lg border px-1.5 py-1.5 text-[11px] font-semibold {{ $cs ? $chip[$cs->color] ?? $chip['slate'] : 'border-dashed border-slate-300 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-900' }}">
                                        <option value="" @selected(! $current)>— پشوو</option>
                                        @foreach ($shifts->where('is_active', true) as $shift)
                                            <option value="{{ $shift->id }}" @selected($current === $shift->id)>{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap gap-2 text-[11px]">
            @foreach ($shifts->where('is_active', true) as $shift)
                <span class="rounded-lg border px-2 py-1 {{ $chip[$shift->color] ?? $chip['slate'] }}">{{ $shift->label() }} {{ $shift->isOvernight() ? '🌙' : '' }}</span>
            @endforeach
        </div>
    @endif

    {{-- ===================== شارەزایی ===================== --}}
    @if ($tab === 'skills' && $technicians->isNotEmpty())
        <div class="{{ $card }} overflow-x-auto">
            <table class="w-full min-w-[900px] text-right text-xs">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 font-semibold">وەستا</th>
                        @foreach ($skills as $key => $label)
                            <th class="px-2 py-3 text-center text-[10px] font-semibold">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($technicians as $tech)
                        <tr wire:key="skills-{{ $tech->id }}">
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ $tech->name }}</td>
                            @foreach ($skills as $key => $label)
                                @php $level = (int) ($tech->skills->firstWhere('skill', $key)?->level ?? 0); @endphp
                                <td class="px-1.5 py-2 text-center">
                                    <select wire:key="sk-{{ $tech->id }}-{{ $key }}" wire:change="setSkill({{ $tech->id }}, '{{ $key }}', $event.target.value)"
                                            class="rounded-lg border px-1 py-1 text-[11px] {{ $level >= 4 ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' : ($level > 0 ? 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200' : 'border-slate-200 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-900') }}">
                                        @for ($i = 0; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected($level === $i)>{{ $i ? str_repeat('⭐', $i) : '—' }}</option>
                                        @endfor
                                    </select>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-[11px] text-slate-500">⭐ = سەرەتایی ... ⭐⭐⭐⭐⭐ = پسپۆڕ. AI Dispatch ئەم ئاستانە بەکاردەهێنێت بۆ هەڵبژاردنی باشترین وەستا.</p>
    @endif

    {{-- ===================== مۆڵەت ===================== --}}
    @if ($tab === 'timeoff' && $technicians->isNotEmpty())
        <form wire:submit="addTimeOff" class="{{ $card }} grid gap-3 p-6 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium">وەستا</label>
                <select wire:model="off_user_id" class="{{ $input }} w-full">
                    <option value="">—</option>
                    @foreach ($technicians as $t) <option value="{{ $t->id }}">{{ $t->name }}</option> @endforeach
                </select>
                @error('off_user_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium">لە</label>
                <input type="date" wire:model="off_from" class="{{ $input }} w-full">
                @error('off_from') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium">تا</label>
                <input type="date" wire:model="off_to" class="{{ $input }} w-full">
                @error('off_to') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium">جۆر</label>
                <select wire:model="off_shift_id" class="{{ $input }} w-full">
                    <option value="">🏖️ مۆڵەت / پشوو (کار ناکات)</option>
                    @foreach ($shifts->where('is_active', true) as $s) <option value="{{ $s->id }}">🔄 لە جیاتی ئەوە: {{ $s->label() }}</option> @endforeach
                </select>
            </div>
            <div class="md:col-span-5">
                <input wire:model="off_reason" placeholder="هۆکار (ئارەزوومەندانە): نەخۆشی، سەفەر..." class="{{ $input }} w-full">
            </div>
            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">تۆمارکردن</button>
        </form>

        <div class="{{ $card }} p-6">
            <h2 class="mb-3 text-sm font-bold">مۆڵەت و گۆڕانکارییە داهاتووەکان</h2>
            <ul class="divide-y divide-slate-100 text-xs dark:divide-slate-800">
                @forelse ($technicians->flatMap(fn ($t) => $t->timeOff->map(fn ($o) => [$t, $o]))->sortBy(fn ($p) => $p[1]->date) as [$t, $off])
                    <li wire:key="off-{{ $off->id }}" class="flex items-center justify-between py-2">
                        <span>
                            <b>{{ $off->date->format('Y-m-d') }}</b> · {{ $t->name }} ·
                            {{ $off->isDayOff() ? '🏖️ مۆڵەت' : '🔄 '.$off->shift?->label() }}
                            @if ($off->reason) <span class="text-slate-400">({{ $off->reason }})</span> @endif
                        </span>
                        <button type="button" wire:click="deleteTimeOff({{ $off->id }})" class="text-rose-600 hover:underline">سڕینەوە</button>
                    </li>
                @empty
                    <li class="py-2 text-slate-400">هیچ مۆڵەتێکی داهاتوو نییە.</li>
                @endforelse
            </ul>
        </div>
    @endif

    {{-- ===================== جۆری شەفت و ئۆنلاین ===================== --}}
    @if ($tab === 'settings')
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="{{ $card }} p-6">
                <h2 class="mb-4 text-sm font-bold">جۆرەکانی شەفت</h2>
                <ul class="mb-4 space-y-2 text-xs">
                    @forelse ($shifts as $shift)
                        <li wire:key="shift-{{ $shift->id }}" class="flex items-center justify-between rounded-xl border px-3 py-2 {{ $chip[$shift->color] ?? $chip['slate'] }} {{ $shift->is_active ? '' : 'opacity-40' }}">
                            <span class="font-semibold">{{ $shift->label() }} {{ $shift->isOvernight() ? '🌙 (دەگاتە ڕۆژی دواتر)' : '' }}</span>
                            <span class="flex gap-3">
                                <button type="button" wire:click="editShift({{ $shift->id }})" class="hover:underline">دەستکاری</button>
                                <button type="button" wire:click="toggleShift({{ $shift->id }})" class="hover:underline">{{ $shift->is_active ? 'ناچالاک' : 'چالاک' }}</button>
                            </span>
                        </li>
                    @empty
                        <li class="text-slate-400">هیچ شەفتێک نییە.</li>
                    @endforelse
                </ul>
                <form wire:submit="saveShift" class="grid grid-cols-2 gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <input wire:model="shift_name" placeholder="ناو: ☀️ ڕۆژ" class="{{ $input }} col-span-2">
                    @error('shift_name') <p class="col-span-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <label class="text-[11px]">دەستپێک <input type="time" wire:model="shift_start" class="{{ $input }} mt-1 w-full"></label>
                    <label class="text-[11px]">کۆتایی <input type="time" wire:model="shift_end" class="{{ $input }} mt-1 w-full"></label>
                    @error('shift_end') <p class="col-span-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <select wire:model="shift_color" class="{{ $input }}">
                        @foreach (\App\Models\Shift::COLORS as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">{{ $shiftId ? 'نوێکردنەوە' : '+ زیادکردن' }}</button>
                    <p class="col-span-2 text-[11px] text-slate-400">شەفتی شەو: ئەگەر کۆتایی لە دەستپێک کەمتر بێت (٢٠:٠٠ → ٠٤:٠٠)، سیستەمەکە دەزانێت دەگاتە ڕۆژی دواتر.</p>
                </form>
            </div>

            <div class="{{ $card }} p-6">
                <h2 class="mb-1 text-sm font-bold">کاتی نۆرەی ئۆنلاین بۆ کڕیار</h2>
                <p class="mb-4 text-[11px] text-slate-500">کڕیار تەنها لەم ماوەیەدا دەتوانێت لە ماڵپەڕەوە نۆرە بگرێت. ڕاوێژکار دەتوانێت هەر کاتێک کە وەستا لە شەفتدا بێت نۆرە دابنێت.</p>
                <form wire:submit="saveOnlineWindow" class="flex flex-wrap items-end gap-3">
                    <label class="text-[11px]">لە <input type="time" wire:model="online_start" class="{{ $input }} mt-1"></label>
                    <label class="text-[11px]">تا <input type="time" wire:model="online_end" class="{{ $input }} mt-1"></label>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">پاشەکەوتکردن</button>
                </form>
                @error('online_end') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-[11px] dark:bg-slate-800/60">
                    لینکی نۆرەی ئۆنلاین بۆ کڕیاران:
                    <a href="{{ route('booking.create') }}" target="_blank" class="font-mono font-bold text-blue-600" dir="ltr">{{ route('booking.create') }}</a>
                </div>
            </div>
        </div>
    @endif
</div>
