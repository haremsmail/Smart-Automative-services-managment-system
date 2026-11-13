<?php

namespace App\Livewire\Scheduling;

use App\Models\ServiceCatalog;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('خزمەتگوزارییەکان و نرخ')]
class Services extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $category = '';
    public string $skill = '';
    public string $required_bay_type = 'general';
    public string $standard_hours = '1';
    public string $base_price = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('services.manage');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $s = ServiceCatalog::findOrFail($id);
        $this->editingId = $s->id;
        $this->name = $s->name;
        $this->category = $s->category;
        $this->skill = (string) $s->skill;
        $this->required_bay_type = $s->required_bay_type;
        $this->standard_hours = (string) (float) $s->standard_hours;
        $this->base_price = (string) (float) $s->base_price;
        $this->description = (string) $s->description;
        $this->is_active = $s->is_active;
        $this->showForm = true;
    }

    public function save(AuditService $audit): void
    {
        $this->authorize('services.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'skill' => ['nullable', Rule::in(array_keys(config('scheduling.skills')))],
            'required_bay_type' => ['required', Rule::in(array_keys(config('scheduling.bay_types')))],
            'standard_hours' => ['required', 'numeric', 'min:0.25', 'max:40'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);
        $data['skill'] = $data['skill'] ?: null;
        $data['description'] = $data['description'] ?: null;

        if ($this->editingId) {
            $service = ServiceCatalog::findOrFail($this->editingId);
            $old = $service->only(array_keys($data));
            $service->update($data);
            $audit->record(Auth::user(), 'service.updated', $service, $old, $service->only(array_keys($data)));
        } else {
            $service = ServiceCatalog::create($data);
            $audit->record(Auth::user(), 'service.created', $service, [], $data);
        }

        $this->resetForm();
        $this->dispatch('flash', message: 'خزمەتگوزاری پاشەکەوتکرا.', type: 'ok');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('showForm', 'editingId', 'name', 'category', 'skill', 'description', 'base_price');
        $this->required_bay_type = 'general';
        $this->standard_hours = '1';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.scheduling.services', [
            'services' => ServiceCatalog::orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'categories' => ServiceCatalog::distinct()->orderBy('category')->pluck('category'),
            'skills' => config('scheduling.skills'),
            'bayTypes' => config('scheduling.bay_types'),
        ]);
    }
}
