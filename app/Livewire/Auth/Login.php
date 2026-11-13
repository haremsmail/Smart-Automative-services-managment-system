<?php

namespace App\Livewire\Auth;

use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('چوونەژوورەوە')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(AuditService $audit): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // ڕێگری لە هەوڵی زۆر: ٥ هەوڵ لە خولەکێکدا
        $key = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "زۆر هەوڵدرا. دوای {$seconds} چرکە هەوڵ بدەرەوە.");

            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $audit->record(null, 'login.failed', null, [], ['email' => $this->email]);

            $this->reset('password');
            $this->addError('email', 'ئیمەیڵ یان پاسوۆرد هەڵەیە.');

            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            $this->addError('email', 'ئەم هەژمارە ناچالاکە. تکایە بەڕێوەبەر پەیوەندی پێوە بکە.');

            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();
        $audit->record($user, 'login.success');

        $this->redirect(session()->pull('url.intended', route('users.index')));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
