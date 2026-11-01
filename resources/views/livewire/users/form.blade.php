<div class="mx-auto max-w-2xl">
    <h1 class="mb-4 text-2xl font-bold">{{ $userId ? 'دەستکاریکردنی کارمەند' : 'کارمەندی نوێ' }}</h1>

    <form wire:submit="save" class="grid gap-4 rounded bg-white p-6 shadow md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm">ناو</label>
            <input wire:model="name" class="w-full rounded border px-3 py-2">
            @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm">ئیمەیڵ</label>
            <input type="email" wire:model="email" dir="ltr" class="w-full rounded border px-3 py-2">
            @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm">تەلەفۆن</label>
            <input wire:model="phone" dir="ltr" class="w-full rounded border px-3 py-2">
            @error('phone') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm">لق</label>
            <select wire:model="branch_id" class="w-full rounded border px-3 py-2">
                <option value="">— هیچ —</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branch_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm">نرخی کاتژمێر</label>
            <input type="number" step="0.01" min="0" wire:model="hourly_rate" dir="ltr" class="w-full rounded border px-3 py-2">
            @error('hourly_rate') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        @can('users.assign_role')
            <div>
                <label class="mb-1 block text-sm">ڕۆڵ</label>
                <select wire:model="role_id" class="w-full rounded border px-3 py-2">
                    <option value="">بێ ڕۆڵ</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                    @endforeach
                </select>
                @error('role_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endcan

        <div>
            <label class="mb-1 block text-sm">دۆخ</label>
            <select wire:model="status" class="w-full rounded border px-3 py-2">
                <option value="active">چالاک</option>
                <option value="inactive">ناچالاک</option>
            </select>
            @error('status') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm">پاسوۆرد {{ $userId ? '(خاڵی بهێڵە بۆ نەگۆڕین)' : '' }}</label>
            <input type="password" wire:model="password" dir="ltr" class="w-full rounded border px-3 py-2">
            @error('password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm">دووبارەکردنەوەی پاسوۆرد</label>
            <input type="password" wire:model="password_confirmation" dir="ltr" class="w-full rounded border px-3 py-2">
        </div>

        <div class="flex gap-2 md:col-span-2">
            <button type="submit" wire:loading.attr="disabled" class="rounded bg-blue-700 px-4 py-2 text-white">پاشەکەوتکردن</button>
            <a href="{{ route('users.index') }}" class="rounded border px-4 py-2">پاشگەزبوونەوە</a>
        </div>
    </form>
</div>
