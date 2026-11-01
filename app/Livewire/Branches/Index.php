<?php

namespace App\Livewire\Branches;

use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('لقەکان')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $code = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('branches.manage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $branch = Branch::findOrFail($id);

        $this->editingId = $branch->id;
        $this->name = $branch->name;
        $this->code = $branch->code;
        $this->phone = (string) $branch->phone;
        $this->email = (string) $branch->email;
        $this->address = (string) $branch->address;
        $this->is_active = $branch->is_active;
        $this->showForm = true;
    }

    public function save(AuditService $audit): void
    {
        $this->authorize('branches.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->ignore($this->editingId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        // خانەی خاڵی وەک null هەڵدەگرین
        $data = array_map(fn ($value) => $value === '' ? null : $value, $data);

        if ($this->editingId) {
            $branch = Branch::findOrFail($this->editingId);
            $old = $branch->only(array_keys($data));
            $branch->update($data);
            $audit->record(auth()->user(), 'branch.updated', $branch, $old, $branch->only(array_keys($data)));
            $message = 'لق نوێکرایەوە.';
        } else {
            $branch = Branch::create($data);
            $audit->record(auth()->user(), 'branch.created', $branch, [], $branch->only(array_keys($data)));
            $message = 'لق زیادکرا.';
        }

        $this->resetForm();
        $this->dispatch('flash', message: $message, type: 'ok');
    }

    public function toggleActive(int $id, AuditService $audit): void
    {
        $this->authorize('branches.manage');

        $branch = Branch::findOrFail($id);
        $old = $branch->is_active;
        $branch->update(['is_active' => ! $old]);

        $audit->record(auth()->user(), 'branch.status', $branch, ['is_active' => $old], ['is_active' => ! $old]);
        $this->dispatch('flash', message: 'دۆخی لق گۆڕدرا.', type: 'ok');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('showForm', 'editingId', 'name', 'code', 'phone', 'email', 'address');
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $branches = Branch::query()
            ->when($this->search !== '', function ($query) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%"));
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.branches.index', ['branches' => $branches]);
    }
}
