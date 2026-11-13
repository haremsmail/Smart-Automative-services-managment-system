<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">بەڕێوەبردنی کارمەندان</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">لیستی سەرجەم کارمەندەکان، ڕۆڵ و دەسەڵات، لق، و دۆخی هەژمارەکانیان</p>
        </div>
        @can('users.create')
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                کارمەندی نوێ
            </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">گەڕان</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="ناو، ئیمەیڵ یان تەلەفۆن..."
                       class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:bg-slate-800">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">دۆخ</label>
                <select wire:model.live="status" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">هەموو دۆخەکان</option>
                    <option value="active">چالاک</option>
                    <option value="inactive">ناچالاک</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">ڕۆڵ</label>
                <select wire:model.live="role_id" class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">هەموو ڕۆڵەکان</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">کارمەند</th>
                        <th class="px-4 py-3.5 font-semibold">ئیمەیڵ</th>
                        <th class="px-4 py-3.5 font-semibold">لق</th>
                        <th class="px-4 py-3.5 font-semibold">ڕۆڵ</th>
                        <th class="px-4 py-3.5 font-semibold">دۆخ</th>
                        <th class="px-4 py-3.5 font-semibold text-left">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-100 font-bold text-xs text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('users.show', $user) }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300">
                                            {{ $user->name }}
                                        </a>
                                        @if($user->phone)
                                            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-mono" dir="ltr">{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-600 dark:text-slate-300" dir="ltr">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-300">
                                @if($user->branch)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                        {{ $user->branch->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium {{ $user->role ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $user->role?->display_name ?? 'بێ ڕۆڵ' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $user->isActive() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $user->isActive() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $user->isActive() ? 'چالاک' : 'ناچالاک' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    @can('users.update')
                                        <a href="{{ route('users.edit', $user) }}" class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                                            دەستکاری
                                        </a>
                                        <button type="button"
                                                class="rounded-lg border px-2.5 py-1 text-[11px] font-medium transition {{ $user->isActive() ? 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-800/60 dark:text-amber-400 dark:hover:bg-amber-950/40' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800/60 dark:text-emerald-400 dark:hover:bg-emerald-950/40' }}"
                                                x-on:click="if (confirm('دڵنیایت لە گۆڕینی دۆخی ئەم هەژمارە؟')) $wire.toggleStatus({{ $user->id }})">
                                            {{ $user->isActive() ? 'ناچالاککردن' : 'چالاککردن' }}
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                هیچ کارمەندێک نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
