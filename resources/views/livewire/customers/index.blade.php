<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">کڕیاران</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">گەڕان بە ناو، ژمارەی تەلەفۆن، ژمارەی تابلۆ یان VIN</p>
        </div>
        @can('customers.create')
            <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                کڕیاری نوێ
            </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">گەڕانی خێرا</label>
                <input type="search" wire:model.live.debounce.300ms="search" autofocus placeholder="ناو، 0750...، تابلۆ یان VIN"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">جۆری کڕیار</label>
                <select wire:model.live="type" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">هەموو جۆرەکان</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @can('customers.delete')
                <div class="flex items-end">
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model.live="archived" class="rounded border-slate-300 text-blue-600">
                        پیشاندانی ئەرشیفکراوەکان
                    </label>
                </div>
            @endcan
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">کڕیار</th>
                        <th class="px-4 py-3.5 font-semibold">تەلەفۆن</th>
                        <th class="px-4 py-3.5 font-semibold">جۆر</th>
                        <th class="px-4 py-3.5 font-semibold">ئۆتۆمبێلەکان</th>
                        <th class="px-4 py-3.5 text-left font-semibold">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}" class="transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-xl {{ $customer->isCompany() ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/60 dark:text-violet-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' }} text-xs font-bold">
                                        {{ mb_substr($customer->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('customers.show', $customer->id) }}" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">{{ $customer->full_name }}</a>
                                        @if ($customer->trashed())
                                            <span class="mr-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] text-slate-600 dark:bg-slate-700 dark:text-slate-300">ئەرشیف</span>
                                        @endif
                                        @if ($customer->email)
                                            <div class="font-mono text-[11px] text-slate-400" dir="ltr">{{ $customer->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-700 dark:text-slate-300" dir="ltr">{{ $customer->phone }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded-md border px-2 py-0.5 text-[11px] {{ $customer->isCompany() ? 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-800 dark:bg-violet-950/60 dark:text-violet-300' : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $customer->typeLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-1">
                                    @foreach ($customer->vehicles->take(3) as $v)
                                        <span class="rounded-md border border-slate-300 bg-white px-1.5 py-0.5 font-mono text-[11px] text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200" title="{{ $v->make }} {{ $v->model }}">{{ $v->plate_number }}</span>
                                    @endforeach
                                    @if ($customer->vehicles_count > 3)
                                        <span class="text-[11px] text-slate-400">+{{ $customer->vehicles_count - 3 }}</span>
                                    @elseif ($customer->vehicles_count === 0)
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($customer->trashed())
                                        @can('customers.delete')
                                            <button type="button" wire:click="restore({{ $customer->id }})" class="rounded-lg border border-emerald-200 px-2.5 py-1 text-[11px] font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800/60 dark:text-emerald-400">گەڕاندنەوە</button>
                                        @endcan
                                    @else
                                        @can('vehicles.create')
                                            <a href="{{ route('vehicles.create', ['customer' => $customer->id]) }}" class="rounded-lg border border-blue-200 px-2.5 py-1 text-[11px] font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-800/60 dark:text-blue-400">+ ئۆتۆمبێل</a>
                                        @endcan
                                        @can('customers.update')
                                            <a href="{{ route('customers.edit', $customer) }}" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">دەستکاری</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                @if ($search !== '')
                                    هیچ کڕیارێک بۆ «{{ $search }}» نەدۆزرایەوە.
                                    @can('customers.create')
                                        <a href="{{ route('customers.create') }}" class="mr-1 font-semibold text-blue-600 hover:underline">کڕیاری نوێ زیاد بکە</a>
                                    @endcan
                                @else
                                    هێشتا هیچ کڕیارێک تۆمار نەکراوە.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $customers->links() }}</div>
</div>
