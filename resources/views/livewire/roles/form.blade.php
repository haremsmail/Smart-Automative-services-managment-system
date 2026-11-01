<div class="mx-auto max-w-3xl">
    <h1 class="mb-4 text-2xl font-bold">{{ $roleId ? 'دەستکاریکردنی ڕۆڵ' : 'ڕۆڵی نوێ' }}</h1>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 rounded bg-white p-6 shadow md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm">ناوی تەکنیکی (پیتی بچووک و _)</label>
                <input wire:model="name" dir="ltr" @disabled($is_system) class="w-full rounded border px-3 py-2 disabled:bg-slate-100">
                @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm">ناوی پیشاندان</label>
                <input wire:model="display_name" class="w-full rounded border px-3 py-2">
                @error('display_name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm">وەسف</label>
                <textarea wire:model="description" rows="2" class="w-full rounded border px-3 py-2"></textarea>
            </div>
        </div>

        <div>
            <h2 class="mb-2 font-bold">دەسەڵاتەکان</h2>
            @foreach ($groups as $module => $permissions)
                <div class="mb-4 rounded bg-white p-4 shadow" x-data="permissionGroup">
                    <div class="mb-2 flex items-center justify-between">
                        <strong dir="ltr">{{ $module }}</strong>
                        <button type="button" x-on:click="toggleAll()" class="text-sm text-blue-700">
                            هەڵبژاردن / لابردنی هەموو
                        </button>
                    </div>
                    <div class="grid gap-1 md:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" value="{{ $permission->id }}" wire:model="permission_ids">
                                <span>{{ $permission->display_name }}</span>
                                <span dir="ltr" class="text-xs text-slate-400">{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @error('permission_ids.*') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" wire:loading.attr="disabled" class="rounded bg-blue-700 px-4 py-2 text-white">پاشەکەوتکردن</button>
            <a href="{{ route('roles.index') }}" class="rounded border px-4 py-2">پاشگەزبوونەوە</a>
        </div>
    </form>
</div>
