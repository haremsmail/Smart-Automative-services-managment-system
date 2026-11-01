<?php
namespace App\Services;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * هەموو گۆڕانکارییەکانی ڕۆڵ، دەسەڵات و دۆخ لێرەدایە.
 * هەر کام لەمانە: پشکنینی مۆڵەت، پارێزەری یاسا، transaction، و تۆماری audit.
 */
class AccessService
{
    public function __construct(private AuditService $audit) {}

    public function createUser(User $actor, array $data): User
    {
        $this->authorizeAction($actor, 'users.create');

        return DB::transaction(function () use ($actor, $data) {
            $roleId = $data['role_id'] ?? null;
            unset($data['role_id']);

            $user = User::create($data);
            $this->audit->record($actor, 'user.created', $user, [], $user->only(['name', 'email', 'branch_id', 'status']));

            if ($roleId) {
                $this->assignRole($actor, $user, Role::findOrFail($roleId));
            }

            return $user;
        });
    }

    public function updateUser(User $actor, User $target, array $data): User
    {
        $this->authorizeAction($actor, 'users.update');

        return DB::transaction(function () use ($actor, $target, $data) {
            $old = $target->only(['name', 'email', 'phone', 'branch_id', 'hourly_rate']);
            $target->update($data);
            $new = $target->only(['name', 'email', 'phone', 'branch_id', 'hourly_rate']);
            $this->audit->record($actor, 'user.updated', $target, $old, $new);

            return $target;
        });
    }

    public function assignRole(User $actor, User $target, ?Role $role): void
    {
        $this->authorizeAction($actor, 'users.assign_role');

        if ($actor->is($target)) {
            throw new DomainException('ناتوانیت ڕۆڵی خۆت بگۆڕیت.');
        }

        DB::transaction(function () use ($actor, $target, $role) {
            // قوفڵی ڕیز: دوو کەس بە هەمان کات نەتوانن کۆتا بەڕێوەبەر بگۆڕن
            $target = User::whereKey($target->id)->lockForUpdate()->firstOrFail();
            $oldName = $target->role?->name;
            $newName = $role?->name;

            if ($oldName === $newName) {
                return;
            }

            if ($oldName === 'admin' && $newName !== 'admin' && $this->activeAdminCount() <= 1) {
                throw new DomainException('ئەمە کۆتا بەڕێوەبەرە، ناتوانرێت ڕۆڵەکەی بگۆڕدرێت.');
            }

            $target->forceFill(['role_id' => $role?->id])->save();
            $this->audit->record($actor, 'role.assigned', $target, ['role' => $oldName], ['role' => $newName]);
        });
    }

    public function setStatus(User $actor, User $target, string $status): void
    {
        $this->authorizeAction($actor, 'users.update');

        if ($actor->is($target) && $status !== 'active') {
            throw new DomainException('ناتوانیت خۆت ناچالاک بکەیت.');
        }

        DB::transaction(function () use ($actor, $target, $status) {
            $target = User::whereKey($target->id)->lockForUpdate()->firstOrFail();

            if ($status === 'inactive' && $target->role?->name === 'admin' && $this->activeAdminCount() <= 1) {
                throw new DomainException('ئەمە کۆتا بەڕێوەبەرە، ناتوانرێت ناچالاک بکرێت.');
            }

            $old = $target->status;
            $target->update(['status' => $status]);
            $this->audit->record($actor, 'user.status', $target, ['status' => $old], ['status' => $status]);
        });
    }

    public function grantPermission(User $actor, User $target, Permission $permission): void
    {
        $this->authorizeAction($actor, 'users.grant_permission');

        $target->extraPermissions()->syncWithoutDetaching([
            $permission->id => ['granted_by' => $actor->id],
        ]);

        $this->audit->record($actor, 'permission.granted', $target, [], ['permission' => $permission->name]);
    }

    public function revokePermission(User $actor, User $target, Permission $permission): void
    {
        $this->authorizeAction($actor, 'users.grant_permission');

        $target->extraPermissions()->detach($permission->id);

        $this->audit->record($actor, 'permission.revoked', $target, ['permission' => $permission->name], []);
    }

    public function saveRole(User $actor, ?Role $role, array $data, array $permissionIds): Role
    {
        $this->authorizeAction($actor, 'roles.manage');

        return DB::transaction(function () use ($actor, $role, $data, $permissionIds) {
            if ($role) {
                if ($role->is_system && $data['name'] !== $role->name) {
                    throw new DomainException('ناوی ڕۆڵی سیستەم ناگۆڕدرێت.');
                }
                $old = $role->only(['name', 'display_name', 'description']);
                $role->update($data);
                $this->audit->record($actor, 'role.updated', $role, $old, $role->only(['name', 'display_name', 'description']));
            } else {
                $role = Role::create($data + ['is_system' => false]);
                $this->audit->record($actor, 'role.created', $role, [], ['name' => $role->name]);
            }

            $before = $role->permissions()->pluck('permissions.name')->all();
            $role->permissions()->sync($permissionIds);
            $after = Permission::whereIn('id', $permissionIds)->pluck('name')->all();
            $this->audit->record($actor, 'role.permissions', $role, ['permissions' => $before], ['permissions' => $after]);

            return $role;
        });
    }

    public function deleteRole(User $actor, Role $role): void
    {
        $this->authorizeAction($actor, 'roles.manage');

        if ($role->is_system) {
            throw new DomainException('ڕۆڵی سیستەم ناسڕدرێتەوە.');
        }
        if ($role->users()->exists()) {
            throw new DomainException('ئەم ڕۆڵە کارمەندی تێدایە، پێش سڕینەوە کارمەندەکان بگۆڕە.');
        }

        DB::transaction(function () use ($actor, $role) {
            $this->audit->record($actor, 'role.deleted', $role, ['name' => $role->name], []);
            $role->delete();
        });
    }

    private function authorizeAction(User $actor, string $permission): void
    {
        if (! $actor->hasPermission($permission)) {
            throw new AuthorizationException('مۆڵەتت نییە.');
        }
    }

    private function activeAdminCount(): int
    {
        return User::where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('name', 'admin'))
            ->count();
    }
}
