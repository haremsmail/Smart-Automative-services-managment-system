<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('فۆڕمی کڕیار')]
class Form extends Component
{
    public ?int $customerId = null;

    public string $customer_type = 'retail';
    public string $full_name = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $address = '';
    public string $tax_number = '';
    public string $credit_limit = '';
    public string $notes = '';

    /** ئاگادارکردنەوە ئەگەر ژمارەکە پێشتر بۆ کڕیارێکی تر تۆمارکرابێت */
    public ?array $duplicate = null;

    public function mount(?Customer $customer = null): void
    {
        $this->authorize($customer?->exists ? 'customers.update' : 'customers.create');

        if ($customer?->exists) {
            $this->customerId = $customer->id;
            $this->customer_type = $customer->customer_type;
            $this->full_name = $customer->full_name;
            $this->phone = (string) $customer->phone;
            $this->whatsapp = (string) $customer->whatsapp;
            $this->email = (string) $customer->email;
            $this->address = (string) $customer->address;
            $this->tax_number = (string) $customer->tax_number;
            $this->credit_limit = $customer->credit_limit > 0 ? (string) $customer->credit_limit : '';
            $this->notes = (string) $customer->notes;
        }
    }

    public function updatedPhone(CustomerService $service): void
    {
        $match = $service->findDuplicatePhone($this->phone, $this->customerId);

        $this->duplicate = $match ? [
            'id' => $match->id,
            'name' => $match->full_name,
            'archived' => $match->trashed(),
        ] : null;
    }

    public function save(CustomerService $service): void
    {
        $isNew = $this->customerId === null;
        $this->authorize($isNew ? 'customers.create' : 'customers.update');

        $data = $this->validate([
            'customer_type' => ['required', Rule::in(array_keys(Customer::TYPES))],
            'full_name' => ['required', 'string', 'min:2', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[\d\s\+\-\(\)]{7,30}$/'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s\+\-\(\)]{7,30}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'ژمارەی تەلەفۆن دروست نییە.',
            'whatsapp.regex' => 'ژمارەی واتسئاپ دروست نییە.',
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        try {
            $customer = $isNew
                ? $service->create($actor, $data)
                : $service->update($actor, Customer::findOrFail($this->customerId), $data);
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');

            return;
        }

        session()->flash('success', $isNew ? 'کڕیار زیادکرا. ئێستا دەتوانیت ئۆتۆمبێلی بۆ زیاد بکەیت.' : 'زانیارییەکانی کڕیار نوێکرانەوە.');
        $this->redirectRoute('customers.show', $customer);
    }

    public function render()
    {
        return view('livewire.customers.form', ['types' => Customer::TYPES]);
    }
}
