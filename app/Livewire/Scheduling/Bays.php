<?php

namespace App\Livewire\Scheduling;

use App\Models\Branch;
use App\Models\ServiceBay;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('شوێنەکانی کار')]
class Bays extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $branch_id = '';
    public string $name = '';
    public string $bay_type = 'general';

    public function mount(): void
    {
        $this->authorize('bays.manage');
    }

    public function create(): void
    {
        $this->reset('editingId', 'name');
        $this->bay_type = 'general';
        $this->branch_id = (string) (Branch::where('is_active', true)->value('id') ?? '');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $bay = ServiceBay::findOrFail($id);
        $this->editingId = $bay->id;
        $this->branch_id = (string) $bay->branch_id;
        $this->name = $bay->name;
        $this->bay_type = $bay->bay_type;
        $this->showForm = true;
    }

    public function save(AuditService $audit): void
    {
        $this->authorize('bays.manage');

        $data = $this->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:60', Rule::unique('service_bays', 'name')->where('branch_id', $this->branch_id)->ignore($this->editingId)],
            'bay_type' => ['required', Rule::in(array_keys(config('scheduling.bay_types')))],
        ], ['name.unique' => 'ئەم ناوە لەم لقەدا پێشتر هەیە.']);

        if ($this->editingId) {
            $bay = ServiceBay::findOrFail($this->editingId);
            $old = $bay->only(array_keys($data));
            $bay->update($data);
            $audit->record(Auth::user(), 'bay.updated', $bay, $old, $data);
        } else {
            $bay = ServiceBay::create($data + ['status' => 'available']);
            $audit->record(Auth::user(), 'bay.created', $bay, [], $data);
        }

        $this->showForm = false;
        $this->dispatch('flash', message: 'شوێنی کار پاشەکەوتکرا.', type: 'ok');
    }

    /** لیفتێک تێکچوو؟ «لە چاککردنەوەدا» ← لە نۆرەکان دەردەهێنرێت */
    public function toggleMaintenance(int $id, AuditService $audit): void
    {
        $this->authorize('bays.manage');

        $bay = ServiceBay::findOrFail($id);
        $old = $bay->status;
        $bay->update(['status' => $bay->isUsable() ? 'maintenance' : 'available']);
        $audit->record(Auth::user(), 'bay.status', $bay, ['status' => $old], ['status' => $bay->status]);

        $this->dispatch('flash', message: $bay->isUsable() ? "«{$bay->name}» بەردەستە." : "«{$bay->name}» خرایە چاککردنەوە و لە نۆرەکان دەرهێنرا.", type: 'ok');
    }

    public function render()
    {
        return view('livewire.scheduling.bays', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'bays' => ServiceBay::with('branch')->withCount(['appointments as upcoming_count' => fn ($q) => $q->active()->where('scheduled_at', '>=', now())])
                ->orderBy('branch_id')->orderBy('name')->get()->groupBy(fn ($b) => $b->branch?->name ?? '—'),
            'bayTypes' => config('scheduling.bay_types'),
        ]);
    }
}
