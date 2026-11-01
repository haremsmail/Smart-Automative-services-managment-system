<div class="space-y-6">
    <div class="rounded bg-white p-6 shadow">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                <p dir="ltr" class="text-slate-500">{{ $user->email }}</p>
            </div>
            @can('users.update')
                <a href="{{ route('users.edit', $user) }}" class="text-blue-700">دەستکاری</a>
            @endcan
        </div>
        <dl class="mt-4 grid gap-3 text-sm md:grid-cols-3">
            <div><dt class="text-slate-500">لق</dt><dd>{{ $user->branch?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">ڕۆڵ</dt><dd>{{ $user->role?->display_name ?? 'بێ ڕۆڵ' }}</dd></div>
            <div><dt class="text-slate-500">دۆخ</dt><dd>{{ $user->isActive() ? 'چالاک' : 'ناچالاک' }}</dd></div>
        </dl>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded bg-white p-6 shadow">
            <h2 class="mb-3 font-bold">دەسەڵاتی ڕۆڵەکە</h2>
            <ul class="list-disc pr-5 text-sm">
                @forelse ($rolePermissions as $permission)
                    <li>{{ $permission->display_name }}</li>
                @empty
                    <li class="list-none text-slate-500">هیچ دەسەڵاتێکی ڕۆڵ نییە.</li>
                @endforelse
            </ul>

            <h2 class="mb-3 mt-6 font-bold">دەسەڵاتی تاکەکەسی</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($user->extraPermissions as $permission)
                    <li class="flex items-center justify-between">
                        <span>{{ $permission->display_name }}</span>
                        @can('users.grant_permission')
                            <button type="button" wire:click="revoke({{ $permission->id }})" class="text-red-600">لابردن</button>
                        @endcan
                    </li>
                @empty
                    <li class="text-slate-500">هیچ دەسەڵاتێکی تاکەکەسی نییە.</li>
                @endforelse
            </ul>

            @can('users.grant_permission')
                <div class="mt-4 flex gap-2">
                    <select wire:model="grant_permission_id" class="flex-1 rounded border px-2 py-2 text-sm">
                        <option value="">دەسەڵاتێک هەڵبژێرە</option>
                        @foreach ($grantable as $permission)
                            <option value="{{ $permission->id }}">{{ $permission->display_name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="grantSelected" class="rounded bg-blue-700 px-4 text-white">زیادکردن</button>
                </div>
                @error('grant_permission_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @endcan
        </div>

        <div class="rounded bg-white p-6 shadow">
            <h2 class="mb-3 font-bold">تۆماری گۆڕانکاری</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($history as $log)
                    <li class="border-b pb-2">
                        <span dir="ltr" class="font-medium">{{ $log->action }}</span>
                        — {{ $log->user?->name ?? 'سیستەم' }}
                        — <span dir="ltr">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">هیچ تۆمارێک نییە.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
