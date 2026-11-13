@php
    $input = 'w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
    $label = 'mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300';
    $error = 'mt-1 text-xs text-rose-600 dark:text-rose-400';
@endphp

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">خزمەتگوزارییەکان و نرخ</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">هەر خزمەتگوزارییەک: کاتی خەمڵێنراو، نرخ، شارەزایی پێویست و جۆری شوێنی کار</p>
        </div>
        @unless ($showForm)
            <button type="button" wire:click="create" class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">+ خزمەتگوزاری نوێ</button>
        @endunless
    </div>

    @if ($showForm)
        <form wire:submit="save" class="grid gap-4 rounded-2xl border-2 border-blue-200 bg-white p-6 shadow-sm dark:border-blue-900 dark:bg-slate-900 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="{{ $label }}">ناو <span class="text-red-500">*</span></label>
                <input wire:model="name" placeholder="گۆڕینی ڕۆن و فلتەر" class="{{ $input }}">
                @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}">پۆل <span class="text-red-500">*</span></label>
                <input wire:model="category" list="cats" placeholder="چاککردنەوەی خولی" class="{{ $input }}">
                <datalist id="cats">@foreach ($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                @error('category') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}">شارەزایی پێویست (بۆ AI)</label>
                <select wire:model="skill" class="{{ $input }}">
                    <option value="">— هیچ —</option>
                    @foreach ($skills as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
                @error('skill') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}">جۆری شوێنی کار</label>
                <select wire:model="required_bay_type" class="{{ $input }}">
                    @foreach ($bayTypes as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
                @error('required_bay_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="{{ $label }}">کات (کاتژمێر)</label>
                    <input type="number" step="0.25" min="0.25" wire:model="standard_hours" dir="ltr" class="{{ $input }}">
                    @error('standard_hours') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">نرخ (د.ع)</label>
                    <input type="number" min="0" step="250" wire:model="base_price" dir="ltr" class="{{ $input }}">
                    @error('base_price') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">وەسف</label>
                <input wire:model="description" class="{{ $input }}">
            </div>
            <label class="flex items-center gap-2 self-end pb-2 text-xs text-slate-700 dark:text-slate-300">
                <input type="checkbox" wire:model="is_active" class="rounded text-blue-600"> چالاک (لە نۆرەدا پیشان بدرێت)
            </label>
            <div class="flex gap-2 md:col-span-3">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2 text-xs font-semibold text-white hover:bg-blue-700">پاشەکەوتکردن</button>
                <button type="button" wire:click="cancel" class="rounded-xl border border-slate-300 px-4 py-2 text-xs dark:border-slate-700">پاشگەزبوونەوە</button>
            </div>
        </form>
    @endif

    @forelse ($services as $category => $items)
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-200">{{ $category }}</div>
            <table class="w-full text-right text-xs">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($items as $s)
                        <tr wire:key="svc-{{ $s->id }}" class="{{ $s->is_active ? '' : 'opacity-50' }}">
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $s->skillLabel() ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] dark:bg-slate-800">{{ $s->bayTypeLabel() }}</span></td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ (float) $s->standard_hours }} کاتژمێر</td>
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ number_format($s->base_price) }} د.ع</td>
                            <td class="px-4 py-3 text-left">
                                <button type="button" wire:click="edit({{ $s->id }})" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">دەستکاری</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-xs text-slate-400 dark:border-slate-700">هێشتا هیچ خزمەتگوزارییەک نییە.</div>
    @endforelse
</div>
