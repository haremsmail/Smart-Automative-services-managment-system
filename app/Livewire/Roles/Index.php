<?php

namespace App\Livewire\Roles;

use App\Models\Role;
use App\Models\User;
use App\Services\AccessService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('ڕۆڵ و دەسەڵات')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('roles.manage');
    }

    public function delete(int $id, AccessService $access): void
    {
        try {
            /** @var User $actor */
            $actor = Auth::user();
            $access->deleteRole($actor, Role::findOrFail($id));
            $this->dispatch('flash', message: 'ڕۆڵ سڕایەوە.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.roles.index', [
            'roles' => Role::withCount(['users', 'permissions'])->orderBy('display_name')->get(),
        ]);
    }
}
