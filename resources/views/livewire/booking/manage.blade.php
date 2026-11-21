@php
    $color = [
        'pending' => 'bg-amber-100 text-amber-800', 'scheduled' => 'bg-blue-100 text-blue-800',
        'arrived' => 'bg-violet-100 text-violet-800', 'completed' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-slate-200 text-slate-600', 'no_show' => 'bg-rose-100 text-rose-800',
    ];
    $wd = \App\Models\TechnicianSchedule::WEEKDAYS;
@endphp

<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between">
        <span class="font-mono text-2xl font-black text-blue-600">{{ $a->reference }}</span>
        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $color[$a->status] ?? '' }}">{{ $a->statusLabel() }}</span>
    </div>

    <div class="mt-5 space-y-3 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">📅 ڕۆژ</span><b>{{ $wd[$a->scheduled_at->dayOfWeek] }} {{ $a->scheduled_at->format('Y-m-d') }}</b></div>
        <div class="flex justify-between"><span class="text-slate-500">⏰ کات</span><b>{{ $a->scheduled_at->format('H:i') }}</b></div>
        <div class="flex justify-between"><span class="text-slate-500">📍 شوێن</span><b>{{ $a->branch?->name }}</b></div>
        <div class="flex justify-between"><span class="text-slate-500">🚗 ئۆتۆمبێل</span><b class="font-mono">{{ $a->vehicle?->plate_number }}</b></div>
        <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
            @foreach ($a->items as $item)
                <div class="flex justify-between text-xs"><span>{{ $item->name }}</span><span>{{ number_format($item->price) }} د.ع</span></div>
            @endforeach
            <div class="mt-2 flex justify-between font-bold"><span>کۆ</span><span>{{ number_format($a->totalPrice()) }} د.ع</span></div>
        </div>
    </div>

    @if ($a->status === 'pending')
        <p class="mt-5 rounded-2xl bg-amber-50 p-3 text-sm text-amber-800">⏳ چاوەڕێی پشتڕاستکردنەوەی گەراجە.</p>
    @elseif ($a->status === 'scheduled')
        <p class="mt-5 rounded-2xl bg-blue-50 p-3 text-sm text-blue-800">✓ نۆرەکەت پشتڕاستکرایەوە. لە کاتی خۆیدا وەرە.</p>
    @endif

    @if (in_array($a->status, ['pending', 'scheduled']))
        <button type="button" x-on:click="if (confirm('دڵنیایت لە هەڵوەشاندنەوەی نۆرەکەت؟')) $wire.cancel()" class="mt-4 w-full rounded-2xl border-2 border-rose-300 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50">هەڵوەشاندنەوەی نۆرە</button>
        @error('cancel') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
    @endif

    <a href="{{ route('booking.create') }}" class="mt-4 block text-center text-xs text-blue-600 hover:underline">نۆرەیەکی نوێ</a>
</div>
