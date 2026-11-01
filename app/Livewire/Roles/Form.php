<?php

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AccessService;
use DomainException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('فۆڕمی ڕۆڵ')]
class Form extends Component
{
    public ?int $roleId = null;
    public string $name = '';
    public string $display_name = '';
    public string $description = '';
    public bool $is_system = false;

    /** @var array<int, string> ژمارەی دەسەڵاتە هەڵبژێردراوەکان (وەک string لە Livewire) */
    public array $permission_ids = [];

    public function mount(?Role $role = null): void
    {
        $this->authorize('roles.manage');

        if ($role) {
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->display_name = $role->display_name;
            $this->description = (string) $role->description;
            $this->is_system = $role->is_system;
            $this->permission_ids = $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
        }
    }

    public function save(AccessService $access): void
    {
        $this->authorize('roles.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z_]+$/', Rule::unique('roles', 'name')->ignore($this->roleId)],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        try {
            $role = $this->roleId ? Role::findOrFail($this->roleId) : null;

            $access->saveRole(
                auth()->user(),
                $role,
                [
                    'name' => $data['name'],
                    'display_name' => $data['display_name'],
                    'description' => $data['description'] ?: null,
                ],
                array_map('intval', $data['permission_ids'] ?? []),
            );
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');

            return;
        }

        session()->flash('success', 'ڕۆڵ پاشەکەوتکرا.');
        $this->redirectRoute('roles.index');
    }

    public function render()
    {
        return view('livewire.roles.form', [
            'groups' => Permission::orderBy('module')->orderBy('name')->get()->groupBy('module'),
        ]);
    }
}
