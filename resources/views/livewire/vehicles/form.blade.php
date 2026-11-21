@php
    $input = 'w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
    $label = 'mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300';
    $error = 'mt-1 text-xs text-rose-600 dark:text-rose-400';
    $card = 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900';
@endphp

<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $vehicleId ? 'دەستکاریکردنی ئۆتۆمبێل' : 'ئۆتۆمبێلی نوێ' }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">هەر ئۆتۆمبێلێک دەبێت خاوەنێکی هەبێت</p>
        </div>
        <a href="{{ $vehicleId ? route('vehicles.show', $vehicleId) : ($owner ? route('customers.show', $owner->id) : route('vehicles.index')) }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">گەڕانەوە</a>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- خاوەن --}}
        <div class="{{ $card }}">
            <label class="{{ $label }}">خاوەن <span class="text-red-500">*</span></label>

            @if ($owner)
                <div class="mt-1 flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50/60 px-4 py-3 dark:border-blue-900 dark:bg-blue-950/30">
                    <div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $owner->full_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500" dir="ltr">{{ $owner->phone }}</div>
                    </div>
                    @if ($vehicleId)
                        <span class="text-[11px] text-slate-500">بۆ گۆڕینی خاوەن، «گواستنەوە» لە پرۆفایلی ئۆتۆمبێل بەکاربهێنە.</span>
                    @else
                        <button type="button" wire:click="clearOwner" class="text-xs font-semibold text-rose-600 hover:underline">گۆڕین</button>
                    @endif
                </div>
            @else
                <input wire:model.live.debounce.300ms="ownerSearch" placeholder="ناو یان ژمارەی تەلەفۆنی کڕیار بنووسە..." class="{{ $input }}">
                @if ($ownerResults->isNotEmpty())
                    <ul class="mt-2 divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 dark:divide-slate-800 dark:border-slate-700">
                        @foreach ($ownerResults as $c)
                            <li wire:key="owner-{{ $c->id }}">
                                <button type="button" wire:click="selectOwner({{ $c->id }})" class="flex w-full items-center justify-between px-4 py-2.5 text-right text-xs hover:bg-blue-50 dark:hover:bg-slate-800">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $c->full_name }}</span>
                                    <span class="font-mono text-slate-500" dir="ltr">{{ $c->phone }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @elseif (mb_strlen(trim($ownerSearch)) >= 2)
                    <p class="mt-2 text-xs text-slate-500">
                        کڕیار نەدۆزرایەوە.
                        @can('customers.create')
                            <a href="{{ route('customers.create') }}" class="font-semibold text-blue-600 hover:underline">کڕیاری نوێ دروست بکە</a>
                        @endcan
                    </p>
                @endif
            @endif
            @error('customer_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        {{-- ناسنامەی ئۆتۆمبێل --}}
        <div class="grid gap-4 {{ $card }} md:grid-cols-2">
            <div class="md:col-span-2 text-xs font-bold text-slate-900 dark:text-white">ناسنامە</div>

            <div>
                <label class="{{ $label }}">ژمارەی تابلۆ <span class="text-red-500">*</span></label>
                <input wire:model.live.debounce.500ms="plate_number" placeholder="22 أ 12345" class="{{ $input }} font-mono">
                @error('plate_number') <p class="{{ $error }}">{{ $message }}</p> @enderror
                @if ($duplicatePlate)
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-2 text-[11px] text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                        ⚠️ ئەم تابلۆیە پێشتر بۆ
                        <a href="{{ route('vehicles.show', $duplicatePlate['id']) }}" class="font-bold underline">{{ $duplicatePlate['title'] }}</a>
                        ({{ $duplicatePlate['owner'] }}){{ $duplicatePlate['archived'] ? ' - ئەرشیف' : '' }} تۆمارکراوە.
                        ئەگەر ئۆتۆمبێلەکە فرۆشراوە، لە پرۆفایلەکەیەوە «گواستنەوە» بەکاربهێنە.
                    </div>
                @endif
            </div>

            <div>
                <label class="{{ $label }}">VIN (ژمارەی شاسی)</label>
                <input wire:model.blur="vin" dir="ltr" maxlength="20" placeholder="17 پیت" class="{{ $input }} font-mono uppercase tracking-wider">
                <div class="mt-1 flex justify-between text-[11px] text-slate-400">
                    <span>بەبێ I و O و Q</span>
                    <span dir="ltr">{{ strlen($vin) }}/17</span>
                </div>
                @error('vin') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- تایبەتمەندی --}}
        <div class="grid gap-4 {{ $card }} md:grid-cols-3">
            <div class="md:col-span-3 text-xs font-bold text-slate-900 dark:text-white">تایبەتمەندی</div>

            <div>
                <label class="{{ $label }}">جۆر (Make) <span class="text-red-500">*</span></label>
                <input wire:model="make" list="makes-list" placeholder="Toyota" dir="ltr" class="{{ $input }}">
                <datalist id="makes-list">
                    @foreach ($makes->merge(['Toyota', 'Hyundai', 'Kia', 'Nissan', 'Chevrolet', 'Ford', 'Mercedes-Benz', 'BMW', 'Lexus', 'Mitsubishi', 'Honda', 'Land Rover'])->unique()->sort() as $m)
                        <option value="{{ $m }}">
                    @endforeach
                </datalist>
                @error('make') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">مۆدێل <span class="text-red-500">*</span></label>
                <input wire:model="model" placeholder="Land Cruiser" dir="ltr" class="{{ $input }}">
                @error('model') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">ساڵ</label>
                <input type="number" wire:model="year" min="1950" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}" dir="ltr" class="{{ $input }}">
                @error('year') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">ڕەنگ</label>
                <input wire:model="color" placeholder="سپی" class="{{ $input }}">
                @error('color') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">سووتەمەنی / بزوێنەر</label>
                <select wire:model="engine_type" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach ($engineTypes as $key => $text)
                        <option value="{{ $key }}">{{ $text }}</option>
                    @endforeach
                </select>
                @error('engine_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">گێڕ</label>
                <select wire:model="transmission" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach ($transmissions as $key => $text)
                        <option value="{{ $key }}">{{ $text }}</option>
                    @endforeach
                </select>
                @error('transmission') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">کیلۆمەتری ئێستا</label>
                <input type="number" wire:model="current_mileage" min="{{ $minMileage }}" placeholder="0" dir="ltr" class="{{ $input }}">
                @if ($minMileage > 0)
                    <p class="mt-1 text-[11px] text-slate-400">کەمترین: {{ number_format($minMileage) }} کم</p>
                @endif
                @error('current_mileage') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-3">
                <label class="{{ $label }}">تێبینی</label>
                <textarea wire:model="notes" rows="2" placeholder="بۆ نموونە: پێشتر ڕووداوی هەبووە، ئاسنی پشتەوە گۆڕاوە..." class="{{ $input }}"></textarea>
                @error('notes') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="save">پاشەکەوتکردن</span>
                <span wire:loading wire:target="save">چاوەڕێ بکە...</span>
            </button>
            <a href="{{ route('vehicles.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">پاشگەزبوونەوە</a>
        </div>
    </form>
</div>
