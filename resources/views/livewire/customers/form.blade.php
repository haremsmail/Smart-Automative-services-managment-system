@php
    $input = 'w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
    $label = 'mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300';
    $error = 'mt-1 text-xs text-rose-600 dark:text-rose-400';
@endphp

<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $customerId ? 'دەستکاریکردنی کڕیار' : 'کڕیاری نوێ' }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">ژمارەی تەلەفۆن خۆکارانە یەکسان دەکرێت (+964 750... → 0750...)</p>
        </div>
        <a href="{{ $customerId ? route('customers.show', $customerId) : route('customers.index') }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">گەڕانەوە</a>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- جۆری کڕیار --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <label class="{{ $label }}">جۆری کڕیار</label>
            <div class="mt-2 grid grid-cols-2 gap-3">
                @foreach ($types as $key => $text)
                    <label wire:key="type-{{ $key }}" class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-xs font-semibold transition {{ $customer_type === $key ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-500 dark:bg-blue-950/50 dark:text-blue-300' : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <input type="radio" wire:model.live="customer_type" value="{{ $key }}" class="text-blue-600">
                        {{ $text }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- زانیاری سەرەکی --}}
        <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="{{ $label }}">{{ $customer_type === 'wholesale' ? 'ناوی کۆمپانیا' : 'ناوی تەواو' }} <span class="text-red-500">*</span></label>
                <input wire:model="full_name" class="{{ $input }}">
                @error('full_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">تەلەفۆن <span class="text-red-500">*</span></label>
                <input wire:model.live.debounce.500ms="phone" dir="ltr" inputmode="tel" placeholder="0750 123 4567" class="{{ $input }}">
                @error('phone') <p class="{{ $error }}">{{ $message }}</p> @enderror
                @if ($duplicate)
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-2 text-[11px] text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                        ⚠️ ئەم ژمارەیە پێشتر بۆ
                        <a href="{{ route('customers.show', $duplicate['id']) }}" class="font-bold underline">{{ $duplicate['name'] }}</a>
                        تۆمارکراوە{{ $duplicate['archived'] ? ' (ئەرشیف)' : '' }}. دڵنیابە کە کڕیارێکی دووبارە دروست ناکەیت.
                    </div>
                @endif
            </div>

            <div>
                <label class="{{ $label }}">واتسئاپ</label>
                <input wire:model="whatsapp" dir="ltr" inputmode="tel" placeholder="ئەگەر جیاوازە لە تەلەفۆن" class="{{ $input }}">
                @error('whatsapp') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">ئیمەیڵ</label>
                <input type="email" wire:model="email" dir="ltr" class="{{ $input }}">
                @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $label }}">ناونیشان</label>
                <input wire:model="address" placeholder="شار، گەڕەک..." class="{{ $input }}">
                @error('address') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- تەنها بۆ کۆمپانیا --}}
        @if ($customer_type === 'wholesale')
            <div class="grid gap-4 rounded-2xl border border-violet-200 bg-violet-50/40 p-6 shadow-sm dark:border-violet-900 dark:bg-violet-950/20 md:grid-cols-2">
                <div class="md:col-span-2 text-xs font-bold text-violet-800 dark:text-violet-300">زانیاری کۆمپانیا / فلیت</div>
                <div>
                    <label class="{{ $label }}">ژمارەی باج</label>
                    <input wire:model="tax_number" dir="ltr" class="{{ $input }}">
                    @error('tax_number') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">سنووری قەرز (دینار)</label>
                    <input type="number" min="0" step="1000" wire:model="credit_limit" dir="ltr" placeholder="0" class="{{ $input }}">
                    @error('credit_limit') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <label class="{{ $label }}">تێبینی</label>
            <textarea wire:model="notes" rows="3" placeholder="هەر زانیارییەکی تر دەربارەی کڕیار..." class="{{ $input }}"></textarea>
            @error('notes') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="save">پاشەکەوتکردن</span>
                <span wire:loading wire:target="save">چاوەڕێ بکە...</span>
            </button>
            <a href="{{ route('customers.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">پاشگەزبوونەوە</a>
        </div>
    </form>
</div>
