@php
    $input = 'w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100';
@endphp

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">شوێنەکانی کار (Service Bays)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">لیفت و شوێنەکانی هەر لقێک. شوێنی «لە چاککردنەوەدا» نۆرەی پێ نادرێت.</p>
        </div>
        @unless ($showForm)
            <button type="button" wire:click="create" class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">+ شوێنی نوێ</button>
        @endunless
    </div>

    @if ($showForm)
        <form wire:submit="save" class="grid gap-4 rounded-2xl border-2 border-blue-200 bg-white p-6 shadow-sm dark:border-blue-900 dark:bg-slate-900 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium">لق</label>
                <select wire:model="branch_id" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach ($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                </select>
                @error('branch_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium">ناو</label>
                <input wire:model="name" placeholder="لیفتی ١" class="{{ $input }}">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium">جۆر</label>
                <select wire:model="bay_type" class="{{ $input }}">
                    @foreach ($bayTypes as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2 text-xs font-semibold text-white hover:bg-blue-700">پاشەکەوتکردن</button>
                <button type="button" wire:click="$set('showForm', false)" class="rounded-xl border border-slate-300 px-4 py-2 text-xs dark:border-slate-700">پاشگەزبوونەوە</button>
            </div>
        </form>
    @endif

    @forelse ($bays as $branchName => $items)
        <div>
            <h2 class="mb-3 text-sm font-bold text-slate-700 dark:text-slate-200">{{ $branchName }}</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($items as $bay)
                    <div wire:key="bay-{{ $bay->id }}" class="rounded-2xl border p-4 shadow-sm {{ $bay->isUsable() ? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' : 'border-amber-300 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/30' }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $bay->name }}</div>
                                <div class="mt-0.5 text-[11px] text-slate-500">{{ $bay->typeLabel() }}</div>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $bay->isUsable() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-amber-200 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                                {{ \App\Models\ServiceBay::STATUSES[$bay->status] ?? $bay->status }}
                            </span>
                        </div>
                        <div class="mt-3 text-[11px] text-slate-400">{{ $bay->upcoming_count }} نۆرەی داهاتوو</div>
                        <div class="mt-3 flex gap-2">
                            <button type="button" wire:click="edit({{ $bay->id }})" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">دەستکاری</button>
                            <button type="button" wire:click="toggleMaintenance({{ $bay->id }})" class="rounded-lg border px-2.5 py-1 text-[11px] {{ $bay->isUsable() ? 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-400' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400' }}">
                                {{ $bay->isUsable() ? '🔧 چاککردنەوە' : '✓ بەردەست' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-xs text-slate-400 dark:border-slate-700">هێشتا هیچ شوێنێکی کار نییە. یەکەم لیفت زیاد بکە.</div>
    @endforelse
</div>
