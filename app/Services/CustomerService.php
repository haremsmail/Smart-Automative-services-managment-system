<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Support\Normalize;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * هەموو گۆڕانکارییەکانی کڕیار لێرەدایە: مۆڵەت، یەکسانکردنی داتا، transaction و Audit.
 */
class CustomerService
{
    private const AUDITED = [
        'customer_type', 'full_name', 'phone', 'whatsapp', 'email',
        'address', 'tax_number', 'credit_limit', 'notes',
    ];

    public function __construct(private AuditService $audit) {}

    public function create(User $actor, array $data): Customer
    {
        $this->authorize($actor, 'customers.create');

        return DB::transaction(function () use ($actor, $data) {
            $customer = Customer::create($this->prepare($data));
            $this->audit->record($actor, 'customer.created', $customer, [], $customer->only(self::AUDITED));

            return $customer;
        });
    }

    public function update(User $actor, Customer $customer, array $data): Customer
    {
        $this->authorize($actor, 'customers.update');

        if ($customer->trashed()) {
            throw new DomainException('ئەم کڕیارە ئەرشیف کراوە. پێشتر بیگەڕێنەوە.');
        }

        return DB::transaction(function () use ($actor, $customer, $data) {
            $old = $customer->only(self::AUDITED);
            $customer->update($this->prepare($data));
            $changes = array_diff_assoc(array_map('strval', $customer->only(self::AUDITED)), array_map('strval', $old));

            if ($changes) {
                $this->audit->record($actor, 'customer.updated', $customer, array_intersect_key($old, $changes), $changes);
            }

            return $customer;
        });
    }

    /**
     * کڕیار ئەرشیف دەکرێت، نەک بسڕدرێتەوە. ئەگەر ئۆتۆمبێلی چالاکی هەبێت ڕێگە نادرێت.
     */
    public function archive(User $actor, Customer $customer): void
    {
        $this->authorize($actor, 'customers.delete');

        if ($customer->vehicles()->exists()) {
            throw new DomainException('ئەم کڕیارە ئۆتۆمبێلی چالاکی هەیە. پێشتر ئۆتۆمبێلەکان بگوازەرەوە یان ئەرشیفیان بکە.');
        }

        DB::transaction(function () use ($actor, $customer) {
            $customer->delete();
            $this->audit->record($actor, 'customer.archived', $customer, ['full_name' => $customer->full_name], []);
        });
    }

    public function restore(User $actor, Customer $customer): void
    {
        $this->authorize($actor, 'customers.delete');

        DB::transaction(function () use ($actor, $customer) {
            $customer->restore();
            $this->audit->record($actor, 'customer.restored', $customer, [], ['full_name' => $customer->full_name]);
        });
    }

    /**
     * کڕیارێکی تر (جگە لەم کڕیارە) کە هەمان ژمارەی تەلەفۆنی هەیە. بۆ ئاگادارکردنەوە.
     */
    public function findDuplicatePhone(?string $phone, ?int $ignoreId = null): ?Customer
    {
        $phone = Normalize::phone($phone);

        if (! $phone) {
            return null;
        }

        return Customer::withTrashed()
            ->where(fn ($q) => $q->where('phone', $phone)->orWhere('whatsapp', $phone))
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->first();
    }

    private function prepare(array $data): array
    {
        $data['phone'] = Normalize::phone($data['phone'] ?? null);
        $data['whatsapp'] = Normalize::phone($data['whatsapp'] ?? null);
        $data['full_name'] = trim($data['full_name']);

        foreach (['email', 'address', 'notes', 'tax_number'] as $field) {
            $data[$field] = ($data[$field] ?? '') === '' ? null : trim($data[$field]);
        }

        // تەنها کۆمپانیا سنووری قەرز و ژمارەی باجی هەیە
        if (($data['customer_type'] ?? 'retail') !== 'wholesale') {
            $data['credit_limit'] = 0;
            $data['tax_number'] = null;
        } else {
            $data['credit_limit'] = ($data['credit_limit'] ?? '') === '' ? 0 : $data['credit_limit'];
        }

        return $data;
    }

    private function authorize(User $actor, string $permission): void
    {
        if (! $actor->hasPermission($permission)) {
            throw new AuthorizationException('مۆڵەتت نییە.');
        }
    }
}
