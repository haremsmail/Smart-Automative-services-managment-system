<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Support\Normalize;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * نۆرەی ئۆنلاینی کڕیار (بەبێ چوونەژوورەوە).
 * کڕیار بە ژمارەی تەلەفۆن دەناسرێتەوە، ئۆتۆمبێل بە تابلۆ. نۆرە «چاوەڕێ» دەبێت تا ڕاوێژکار پشتڕاستی دەکاتەوە.
 */
class BookingService
{
    public function __construct(
        private AppointmentService $appointments,
        private SchedulingService $scheduling,
        private AuditService $audit,
    ) {}

    /**
     * $data: branch_id, service_ids[], scheduled_at, full_name, phone, plate_number, make, model, customer_notes?
     */
    public function book(array $data): Appointment
    {
        $start = Carbon::parse($data['scheduled_at']);
        [$onlineStart, $onlineEnd] = $this->scheduling->onlineWindow();

        if ($start->format('H:i') < $onlineStart || $start->format('H:i') >= $onlineEnd) {
            throw new DomainException("نۆرەی ئۆنلاین تەنها لە {$onlineStart} تا {$onlineEnd} ە.");
        }
        if ($start->gt(now()->addDays((int) config('scheduling.online_days_ahead', 14)))) {
            throw new DomainException('ئەم بەروارە زۆر دوورە.');
        }

        $phone = Normalize::phone($data['phone']);

        return DB::transaction(function () use ($data, $phone) {
            $customer = Customer::where('phone', $phone)->first();

            if ($customer && Appointment::where('customer_id', $customer->id)->where('status', 'pending')->count() >= 2) {
                throw new DomainException('پێشتر نۆرەی چاوەڕێکراوت هەیە. تکایە چاوەڕێی پەیوەندی گەراج بکە.');
            }

            if (! $customer) {
                $customer = Customer::create([
                    'customer_type' => 'retail',
                    'full_name' => trim($data['full_name']),
                    'phone' => $phone,
                    'notes' => 'لە ڕێگەی نۆرەی ئۆنلاینەوە تۆمارکرا',
                ]);
                $this->audit->record(null, 'customer.created', $customer, [], ['full_name' => $customer->full_name, 'source' => 'online']);
            }

            $plateKey = Normalize::plateKey($data['plate_number']);
            $vehicle = $customer->vehicles()
                ->whereRaw("UPPER(REPLACE(REPLACE(plate_number, ' ', ''), '-', '')) = ?", [$plateKey])
                ->first();

            if (! $vehicle) {
                $vehicle = $customer->vehicles()->create([
                    'plate_number' => Normalize::plate($data['plate_number']),
                    'make' => trim($data['make']),
                    'model' => trim($data['model']),
                ]);
                $this->audit->record(null, 'vehicle.created', $vehicle, [], ['plate_number' => $vehicle->plate_number, 'source' => 'online']);
            }

            return $this->appointments->create(null, [
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'branch_id' => $data['branch_id'],
                'service_ids' => $data['service_ids'],
                'scheduled_at' => $data['scheduled_at'],
                'source' => 'online',
                'customer_notes' => $data['customer_notes'] ?? null,
            ]);
        });
    }
}
