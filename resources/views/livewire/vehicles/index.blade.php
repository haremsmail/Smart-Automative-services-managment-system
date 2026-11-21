<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">ئۆتۆمبێلەکان</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">گەڕان بە تابلۆ، VIN، جۆر یان ناوی خاوەن</p>
        </div>
        @can('vehicles.create')
            <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                ئۆتۆمبێلی نوێ
            </a>
        @endcan
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">گەڕانی خێرا</label>
                <input type="search" wire:model.live.debounce.300ms="search" autofocus placeholder="تابلۆ، VIN، Toyota، ناوی خاوەن..."
                       class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">جۆر (Make)</label>
                <select wire:model.live="make" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">هەموو جۆرەکان</option>
                    @foreach ($makes as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            @can('vehicles.delete')
                <div class="flex items-end">
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model.live="archived" class="rounded border-slate-300 text-blue-600">
                        پیشاندانی ئەرشیفکراوەکان
                    </label>
                </div>
            @endcan
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">تابلۆ</th>
                        <th class="px-4 py-3.5 font-semibold">ئۆتۆمبێل</th>
                        <th class="px-4 py-3.5 font-semibold">خاوەن</th>
                        <th class="px-4 py-3.5 font-semibold">کیلۆمەتر</th>
                        <th class="px-4 py-3.5 font-semibold">VIN</th>
                        <th class="px-4 py-3.5 text-left font-semibold">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($vehicles as $vehicle)
                        <tr wire:key="vehicle-{{ $vehicle->id }}" class="transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('vehicles.show', $vehicle->id) }}" class="rounded-md border-2 border-slate-800 bg-white px-2 py-0.5 font-mono text-xs font-bold text-slate-900 hover:border-blue-600 dark:border-slate-300 dark:bg-slate-100">{{ $vehicle->plate_number }}</a>
                                @if ($vehicle->trashed())
                                    <span class="mr-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] text-slate-600 dark:bg-slate-700 dark:text-slate-300">ئەرشیف</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('vehicles.show', $vehicle->id) }}" class="font-semibold text-slate-800 hover:text-blue-600 dark:text-slate-100">{{ $vehicle->title() }}</a>
                                <div class="text-[11px] text-slate-400">{{ $vehicle->color }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($vehicle->customer)
                                    @can('customers.view')
                                        <a href="{{ route('customers.show', $vehicle->customer->id) }}" class="text-blue-600 hover:underline dark:text-blue-400">{{ $vehicle->customer->full_name }}</a>
                                    @else
                                        {{ $vehicle->customer->full_name }}
                                    @endcan
                                    <div class="font-mono text-[11px] text-slate-400" dir="ltr">{{ $vehicle->customer->phone }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-300">{{ number_format($vehicle->current_mileage) }} کم</td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-[11px] text-slate-500" dir="ltr">{{ $vehicle->vin ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($vehicle->trashed())
                                        @can('vehicles.delete')
                                            <button type="button" wire:click="restore({{ $vehicle->id }})" class="rounded-lg border border-emerald-200 px-2.5 py-1 text-[11px] font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800/60 dark:text-emerald-400">گەڕاندنەوە</button>
                                        @endcan
                                    @else
                                        @can('vehicles.update')
                                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">دەستکاری</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                {{ $search !== '' ? 'هیچ ئۆتۆمبێلێک بۆ «'.$search.'» نەدۆزرایەوە.' : 'هێشتا هیچ ئۆتۆمبێلێک تۆمار نەکراوە.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $vehicles->links() }}</div>
</div>
