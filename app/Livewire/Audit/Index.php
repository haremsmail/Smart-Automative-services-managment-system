<?php

namespace App\Livewire\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('تۆماری گۆڕانکاری و چالاکییەکان')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $action = '';

    #[Url(as: 'user', except: '')]
    public string $user_id = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public ?int $selectedLogId = null;

    public function mount(): void
    {
        $this->authorize('audit.view');
    }

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['action', 'user_id', 'from', 'to']);
        $this->resetPage();
    }

    public function viewDetails(?int $id = null): void
    {
        $this->selectedLogId = $id;
    }

    public function getActionMeta(string $action): array
    {
        return match ($action) {
            'login.success' => ['label' => 'چوونەژوورەوەی سەرکەوتوو', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'login.failed' => ['label' => 'چوونەژوورەوەی شکستخواردوو', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200'],
            'user.created' => ['label' => 'دروستکردنی کارمەند', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'user.updated' => ['label' => 'دەستکاریی کارمەند', 'color' => 'blue', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'user.status' => ['label' => 'گۆڕینی دۆخی کارمەند', 'color' => 'amber', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'role.assigned' => ['label' => 'دیاریکردنی ڕۆڵ', 'color' => 'indigo', 'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
            'permission.granted' => ['label' => 'پێدانی دەسەڵات', 'color' => 'teal', 'bg' => 'bg-teal-50 text-teal-700 border-teal-200'],
            'permission.revoked' => ['label' => 'لێسەندنەوەی دەسەڵات', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200'],
            'role.created' => ['label' => 'دروستکردنی ڕۆڵ', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'role.updated' => ['label' => 'دەستکاریی ڕۆڵ', 'color' => 'blue', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'role.permissions' => ['label' => 'دەسەڵاتی ڕۆڵ', 'color' => 'purple', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200'],
            'role.deleted' => ['label' => 'سڕینەوەی ڕۆڵ', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200'],
            'branch.created' => ['label' => 'دروستکردنی لق', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'branch.updated' => ['label' => 'دەستکاریی لق', 'color' => 'blue', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'branch.status' => ['label' => 'گۆڕینی دۆخی لق', 'color' => 'amber', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
            default => ['label' => $action, 'color' => 'slate', 'bg' => 'bg-slate-50 text-slate-700 border-slate-200'],
        };
    }

    public function getModelLabel(?string $model): string
    {
        return match ($model) {
            'User' => 'کارمەند',
            'Role' => 'ڕۆڵ',
            'Branch' => 'لق',
            'Permission' => 'دەسەڵات',
            default => $model ?? '—',
        };
    }

    public function getFieldLabel(string $field): string
    {
        return match ($field) {
            'role' => 'ڕۆڵ',
            'permissions' => 'دەسەڵاتەکان',
            'name' => 'ناو',
            'email' => 'ئیمەیڵ',
            'phone' => 'تەلەفۆن',
            'hourly_rate' => 'نرخی کاتژمێر',
            'status' => 'دۆخ',
            'branch_id' => 'لق',
            'code' => 'کۆد',
            'is_active' => 'چالاکی',
            'display_name' => 'ناوی نیشاندان',
            'description' => 'ڕوونکردنەوە',
            'granted_by' => 'پێدراوە لەلایەن',
            default => $field,
        };
    }

    public function render()
    {
        $logs = AuditLog::with('user')
            ->when($this->action !== '', fn ($q) => $q->where('action', 'like', $this->action.'%'))
            ->when($this->user_id !== '', fn ($q) => $q->where('user_id', $this->user_id))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest('created_at')
            ->latest('id')
            ->paginate(50);

        $selectedLog = $this->selectedLogId ? AuditLog::with('user')->find($this->selectedLogId) : null;

        return view('livewire.audit.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'selectedLog' => $selectedLog,
        ]);
    }
}
