<?php

namespace App\Livewire\Users;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\User;
use App\Services\AccessService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('پرۆفایلی کارمەند')]
class Show extends Component
{
    public int $userId;
    public string $grant_permission_id = '';

    public function mount(User $user): void
    {
        $this->authorize('users.view');
        $this->userId = $user->id;
    }

    public function grantSelected(AccessService $access): void
    {
        $this->validate([
            'grant_permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $permission = Permission::findOrFail((int) $this->grant_permission_id);
        $access->grantPermission(auth()->user(), User::findOrFail($this->userId), $permission);

        $this->grant_permission_id = '';
        $this->dispatch('flash', message: "دەسەڵات زیادکرا: {$permission->display_name}", type: 'ok');
    }

    public function revoke(int $permissionId, AccessService $access): void
    {
        $permission = Permission::findOrFail($permissionId);
        $access->revokePermission(auth()->user(), User::findOrFail($this->userId), $permission);

        $this->dispatch('flash', message: "دەسەڵات لابرا: {$permission->display_name}", type: 'ok');
    }

    public function render()
    {
        $user = User::with(['branch', 'role.permissions', 'extraPermissions'])->findOrFail($this->userId);

        $grantable = Permission::whereNotIn('id', $user->extraPermissions->pluck('id'))
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        $history = AuditLog::with('user')
            ->where('model_type', 'User')
            ->where('model_id', $user->id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('livewire.users.show', [
            'user' => $user,
            'rolePermissions' => $user->role?->permissions ?? collect(),
            'grantable' => $grantable,
            'history' => $history,
        ]);
    }
}
