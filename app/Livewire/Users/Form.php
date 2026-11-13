<?php

namespace App\Livewire\Users;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\AccessService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('فۆڕمی کارمەند')]
class Form extends Component
{
    public ?int $userId = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $branch_id = '';
    public string $hourly_rate = '';
    public string $role_id = '';
    public string $status = 'active';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(?User $user = null): void
    {
        $this->authorize($user ? 'users.update' : 'users.create');

        if ($user) {
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = (string) $user->phone;
            $this->branch_id = (string) $user->branch_id;
            $this->hourly_rate = (string) $user->hourly_rate;
            $this->role_id = (string) $user->role_id;
            $this->status = $user->status;
        }
    }

    public function save(AccessService $access): void
    {
        $actor = auth()->user();
        $isNew = $this->userId === null;

        $this->authorize($isNew ? 'users.create' : 'users.update');

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => [$isNew ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // ڕۆڵ تەنها ئەگەر دەسەڵاتی users.assign_role هەبێت دەگۆڕدرێت
        $canAssignRole = $actor->hasPermission('users.assign_role');
        $roleId = $canAssignRole && $this->role_id !== '' ? (int) $this->role_id : null;

        $payload = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'branch_id' => $this->branch_id ?: null,
            'hourly_rate' => $this->hourly_rate ?: null,
        ];

        if ($this->password !== '') {
            $payload['password'] = $this->password;
        }

        try {
            DB::transaction(function () use ($actor, $access, $isNew, $payload, $canAssignRole, $roleId) {
                if ($isNew) {
                    $access->createUser($actor, $payload + [
                        'status' => $this->status,
                        'role_id' => $roleId,
                    ]);

                    return;
                }

                $target = User::findOrFail($this->userId);
                $access->updateUser($actor, $target, $payload);

                if ($canAssignRole && (int) $target->role_id !== (int) $roleId) {
                    $access->assignRole($actor, $target, $roleId ? Role::findOrFail($roleId) : null);
                }

                if ($this->status !== $target->status) {
                    $access->setStatus($actor, $target, $this->status);
                }
            });
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');

            return;
        }

        session()->flash('success', $isNew ? 'کارمەند زیادکرا.' : 'زانیارییەکان نوێکرانەوە.');
        $this->redirectRoute('users.index');
    }

    public function render()
    {
        return view('livewire.users.form', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'roles' => Role::orderBy('display_name')->get(),
        ]);
    }
}
