<?php

namespace App\Services\Odoo;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls primary customers / dealers from Odoo into odoo_customers.
|--------------------------------------------------------------------------
| Odoo: res.partner / get_fieldkonnect_customer_list (Scenario A, paginated).
| Each record: odoo_parent_id, firm_name, contact_person_name, mobile, phone, email,
| street, street2, city, district, state, pin_code, country, gstin, salesperson,
| customer_type, customer_type_id, active, is_deleted, created_at, updated_at.
| Run by `php artisan odoo:sync-customers` (cron, twice a day) and by
| "Sync now" on Odoo Sync -> Primary Customer Odoo.
|
| - Upsert key is odoo_parent_id (stored as customer_odoo_id); re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - Customers Odoo no longer returns are removed.
| - customer_id links to FieldKonnect `customers` by GSTIN (customer_details.gstin_no),
|   else by 10-digit mobile. `customers` is never changed.
*/
class CustomerSync extends OdooPullSync
{
    public const ENTITY = 'customers';

    private array $gstinCache = [];
    private array $mobileCache = [];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['res.partner', 'get_fieldkonnect_customer_list'];
    }

    /**
     * Rows Odoo no longer returns are removed (only after Odoo was read fully).
     */
    protected function prepare(array $records): void
    {
        $ids = array_values(array_filter(array_map(fn ($r) => $this->odooId($r), $records)));

        DB::table('odoo_customers')->whereNotIn('customer_odoo_id', $ids ?: [0])->delete();
    }

    protected function upsert(array $record, string $correlationId): array
    {
        $odooId = $this->odooId($record);
        $firmName = trim((string) ($record['firm_name'] ?? ''));

        if (!$odooId || $firmName === '') {
            return ['status' => 'failed', 'errors' => ['record' => ['odoo_parent_id and firm_name are required.']]];
        }

        $gstin = strtoupper(trim((string) ($record['gstin'] ?? ''))) ?: null;
        $customerId = $this->resolveCustomerId($gstin, $record['mobile'] ?? null);
        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);

        $existing = DB::table('odoo_customers')
            ->where('customer_odoo_id', $odooId)
            ->first(['id', 'odoo_updated_at', 'customer_id']);

        // Unchanged in Odoo: nothing to do, unless the FieldKonnect link changed
        if ($existing && $this->isUnchanged($existing, $odooUpdatedAt) && (int) $existing->customer_id === (int) $customerId) {
            return ['status' => 'skipped'];
        }

        $row = [
            'firm_name' => $firmName,
            'contact_person_name' => $record['contact_person_name'] ?? null,
            'mobile' => $record['mobile'] ?? null,
            'phone' => $record['phone'] ?? null,
            'email' => $record['email'] ?? null,
            'street' => isset($record['street']) ? trim($record['street'], " ,") : null,
            'street2' => $record['street2'] ?? null,
            'city' => $record['city'] ?? null,
            'district' => $record['district'] ?? null,
            'state' => $record['state'] ?? null,
            'pin_code' => $record['pin_code'] ?? null,
            'country' => $record['country'] ?? null,
            'gstin' => $gstin,
            'salesperson' => is_scalar($record['salesperson'] ?? null) ? $record['salesperson'] : null,
            'customer_type' => $record['customer_type'] ?? null,
            'customer_type_id' => !empty($record['customer_type_id']) ? (int) $record['customer_type_id'] : null,
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_deleted' => filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'customer_id' => $customerId,
            'odoo_created_at' => $this->toLocal($record['created_at'] ?? null),
            'odoo_updated_at' => $odooUpdatedAt,
            'last_correlation_id' => $correlationId,
            'raw_payload' => json_encode($record),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('odoo_customers')->where('id', $existing->id)->update($row);
            return ['status' => 'updated'];
        }

        DB::table('odoo_customers')->insert($row + [
            'customer_odoo_id' => $odooId,
            'created_at' => now(),
        ]);

        return ['status' => 'created'];
    }

    /** Odoo sends the partner id as odoo_parent_id (party_odoo_id in Scenario B) */
    private function odooId(array $record): int
    {
        return (int) ($record['odoo_parent_id'] ?? $record['party_odoo_id'] ?? 0);
    }

    /**
     * GSTIN against customer_details.gstin_no, then the last 10 digits of mobile
     * against customers.mobile (only when Odoo's mobile is a real 10-digit number).
     */
    private function resolveCustomerId(?string $gstin, $mobile): ?int
    {
        if ($gstin) {
            if (!array_key_exists($gstin, $this->gstinCache)) {
                $id = DB::table('customer_details as cd')
                    ->join('customers as c', 'c.id', '=', 'cd.customer_id')
                    ->whereNull('c.deleted_at')
                    ->where('cd.gstin_no', $gstin)
                    ->value('c.id');
                $this->gstinCache[$gstin] = $id ? (int) $id : null;
            }
            if ($this->gstinCache[$gstin]) {
                return $this->gstinCache[$gstin];
            }
        }

        $digits = preg_replace('/\D/', '', (string) $mobile);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) !== 10) {
            return null;
        }

        if (!array_key_exists($digits, $this->mobileCache)) {
            $id = DB::table('customers')->whereNull('deleted_at')->where('mobile', $digits)->value('id');
            $this->mobileCache[$digits] = $id ? (int) $id : null;
        }

        return $this->mobileCache[$digits];
    }
}
