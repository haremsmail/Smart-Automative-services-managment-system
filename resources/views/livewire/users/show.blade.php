<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->name }}</h1>
                <p dir="ltr" class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $user->email }}</p>
            </div>
            @can('users.update')
                <a href="{{ route('users.edit', $user) }}" class="rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition">
                    دەستکاری
                </a>
            @endcan
        </div>
        <dl class="mt-6 grid gap-4 text-xs sm:grid-cols-3 border-t border-slate-100 pt-4 dark:border-slate-800">
            <div>
                <dt class="text-slate-400 dark:text-slate-500 font-medium">لق</dt>
                <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-200">{{ $user->branch?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-400 dark:text-slate-500 font-medium">ڕۆڵ</dt>
                <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-200">{{ $user->role?->display_name ?? 'بێ ڕۆڵ' }}</dd>
            </div>
            <div>
                <dt class="text-slate-400 dark:text-slate-500 font-medium">دۆخ</dt>
                <dd class="mt-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $user->isActive() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $user->isActive() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $user->isActive() ? 'چالاک' : 'ناچالاک' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">دەسەڵاتی ڕۆڵەکە</h2>
            <ul class="list-disc pr-5 text-xs text-slate-600 dark:text-slate-300 space-y-1">
                @forelse ($rolePermissions as $permission)
                    <li>{{ $permission->display_name }}</li>
                @empty
                    <li class="list-none text-slate-400 dark:text-slate-500">هیچ دەسەڵاتێکی ڕۆڵ نییە.</li>
                @endforelse
            </ul>

            <h2 class="mb-3 mt-6 text-sm font-bold text-slate-900 dark:text-white">دەسەڵاتی تاکەکەسی (Extra)</h2>
            <ul class="space-y-2 text-xs">
                @forelse ($user->extraPermissions as $permission)
                    <li class="flex items-center justify-between rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <span class="text-slate-700 dark:text-slate-200 font-medium">{{ $permission->display_name }}</span>
                        @can('users.grant_permission')
                            <button type="button" wire:click="revoke({{ $permission->id }})" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400">
                                لابردن
                            </button>
                        @endcan
                    </li>
                @empty
                    <li class="text-xs text-slate-400 dark:text-slate-500">هیچ دەسەڵاتێکی تاکەکەسی دیاری نەکراوە.</li>
                @endforelse
            </ul>

            @can('users.grant_permission')
                <div class="mt-4 flex gap-2">
                    <select wire:model="grant_permission_id" class="flex-1 rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="">دەسەڵاتێک هەڵبژێرە</option>
                        @foreach ($grantable as $permission)
                            <option value="{{ $permission->id }}">{{ $permission->display_name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="grantSelected" class="rounded-xl bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                        زیادکردن
                    </button>
                </div>
                @error('grant_permission_id') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            @endcan
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">تۆماری گۆڕانکاری (Audit Trail)</h2>
            <ul class="space-y-2 text-xs">
                @forelse ($history as $log)
                    <li class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
                        <span class="font-mono text-slate-700 dark:text-slate-300">{{ $log->action }}</span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ $log->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="text-slate-400 dark:text-slate-500">هیچ تۆمارێک بۆ ئەم کارمەندە نییە.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
