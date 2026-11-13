<?php

namespace App\Livewire\Users;


use App\Models\Role;
use App\Models\User;
use App\Services\AccessService;
use DomainException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
#[Title('کارمەندان')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $role_id = '';

    public function mount(): void
    {
        $this->authorize('users.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedRoleId(): void
    {
        $this->resetPage();
    }

    /**
     * دۆخی کارمەند دەگۆڕێت (چالاک ↔ ناچالاک).
     * ئەم یاسایانە لە AccessService دان: خۆت ناچالاک ناکەیت، کۆتا بەڕێوەبەر ناچالاک ناکرێت.
     */
    public function toggleStatus(int $id, AccessService $access): void
    {
        $target = User::findOrFail($id);
        $newStatus = $target->isActive() ? 'inactive' : 'active';

        try {

            $access->setStatus(Auth::user(), $target, $newStatus);
            $this->dispatch('flash', message: $newStatus === 'active' ? 'هەژمار چالاککرا.' : 'هەژمار ناچالاککرا.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $users = User::with(['branch', 'role'])
            ->when($this->search !== '', function ($query) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->role_id !== '', fn ($q) => $q->where('role_id', $this->role_id))
            ->latest()
            ->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('display_name')->get(),
        ]);
    }
}
