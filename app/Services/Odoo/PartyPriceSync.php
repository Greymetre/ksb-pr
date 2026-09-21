<?php

namespace App\Services\Odoo;

use App\Models\Customers;
use App\Models\MasterDistributor;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| Upserts Odoo party-wise price records.
|--------------------------------------------------------------------------
| test mode -> odoo_stg_party_prices
| live mode -> party_product_prices
|
| Rules (from the Odoo integration contract, sections 6 and 14):
| - Upsert key is company_code + external_id; re-sending never duplicates.
| - A record with an older updated_at than the stored one is skipped.
| - One bad record never blocks the others in the same batch.
| - Unknown party_code / product_code is saved with a warning (ids stay null)
|   so prices can arrive before the party/product masters are synced.
*/
class PartyPriceSync
{
    public const TABLES = [
        'test' => 'odoo_stg_party_prices',
        'live' => 'party_product_prices',
    ];

    private array $partyCache = [];
    private array $productCache = [];

    public static function tableFor(string $mode): string
    {
        return self::TABLES[$mode];
    }

    public function rules(): array
    {
        return [
            'external_id' => 'required|string|max:100',
            'company_code' => 'nullable|string|max:50',
            'price_list_code' => 'required|string|max:50',
            'price_list_name' => 'nullable|string|max:150',
            'party_external_id' => 'required|string|max:100',
            'party_code' => 'required|string|max:100',
            'product_external_id' => 'required|string|max:100',
            'product_code' => 'required|string|max:100',
            'currency_code' => 'required|string|size:3',
            'base_price' => 'required|numeric|min:0',
            'party_price' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive' => 'required|boolean',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'maximum_quantity' => 'nullable|numeric|min:0',
            'uom_code' => 'required|string|max:50',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'priority' => 'nullable|integer',
            'active' => 'required|boolean',
            'is_deleted' => 'nullable|boolean',
            'updated_at' => 'required|date',
        ];
    }

    /**
     * @return array{status: string, errors?: array, warnings?: array}
     */
    public function upsert(array $record, string $mode, string $correlationId): array
    {
        $validator = Validator::make($record, $this->rules());

        $validator->after(function ($v) use ($record) {
            $min = $record['minimum_quantity'] ?? 1;
            $max = $record['maximum_quantity'] ?? null;
            if ($max !== null && is_numeric($min) && is_numeric($max) && $max < $min) {
                $v->errors()->add('maximum_quantity', 'maximum_quantity must be greater than or equal to minimum_quantity.');
            }
        });

        if ($validator->fails()) {
            return ['status' => 'failed', 'errors' => $validator->errors()->toArray()];
        }

        $warnings = [];
        $party = $this->resolveParty($record['party_code']);
        if (!$party) {
            $warnings[] = "party_code '{$record['party_code']}' not found in FieldKonnect; saved without party link.";
        }
        $productId = $this->resolveProductId($record['product_code']);
        if (!$productId) {
            $warnings[] = "product_code '{$record['product_code']}' not found in FieldKonnect; saved without product link.";
        }

        $table = self::tableFor($mode);
        $companyCode = (string) ($record['company_code'] ?? '');
        $odooUpdatedAt = $this->toLocal($record['updated_at']);

        $existing = DB::table($table)
            ->where('company_code', $companyCode)
            ->where('external_id', $record['external_id'])
            ->first(['id', 'odoo_updated_at']);

        if ($existing && Carbon::parse($existing->odoo_updated_at)->gt($odooUpdatedAt)) {
            return [
                'status' => 'skipped',
                'errors' => ['updated_at' => ['Older than the version already stored (' . $existing->odoo_updated_at . '); ignored.']],
            ];
        }

        $row = [
            'price_list_code' => $record['price_list_code'],
            'price_list_name' => $record['price_list_name'] ?? null,
            'party_external_id' => $record['party_external_id'],
            'party_code' => $record['party_code'],
            'party_type' => $party['type'] ?? null,
            'party_id' => $party['id'] ?? null,
            'product_external_id' => $record['product_external_id'],
            'product_code' => $record['product_code'],
            'product_id' => $productId,
            'currency_code' => strtoupper($record['currency_code']),
            'base_price' => $record['base_price'],
            'party_price' => $record['party_price'],
            'discount_percent' => $record['discount_percent'] ?? null,
            'tax_inclusive' => filter_var($record['tax_inclusive'], FILTER_VALIDATE_BOOLEAN),
            'minimum_quantity' => $record['minimum_quantity'] ?? 1,
            'maximum_quantity' => $record['maximum_quantity'] ?? null,
            'uom_code' => $record['uom_code'],
            'valid_from' => $this->toLocal($record['valid_from']),
            'valid_to' => isset($record['valid_to']) ? $this->toLocal($record['valid_to']) : null,
            'priority' => $record['priority'] ?? 0,
            'active' => filter_var($record['active'], FILTER_VALIDATE_BOOLEAN),
            'is_deleted' => filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'odoo_updated_at' => $odooUpdatedAt,
            'last_correlation_id' => $correlationId,
            'raw_payload' => json_encode($record),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table($table)->where('id', $existing->id)->update($row);
            $status = 'updated';
        } else {
            DB::table($table)->insert($row + [
                'company_code' => $companyCode,
                'external_id' => $record['external_id'],
                'created_at' => now(),
            ]);
            $status = 'created';
        }

        return ['status' => $status, 'warnings' => $warnings];
    }

    /**
     * party_code is matched against customers.sap_code, then customers.customer_code,
     * then master_distributors.distributor_code.
     */
    private function resolveParty(string $partyCode): ?array
    {
        if (array_key_exists($partyCode, $this->partyCache)) {
            return $this->partyCache[$partyCode];
        }

        $party = null;
        $customerId = Customers::where('sap_code', $partyCode)->value('id')
            ?? Customers::where('customer_code', $partyCode)->value('id');

        if ($customerId) {
            $party = ['type' => 'customer', 'id' => $customerId];
        } elseif ($distributorId = MasterDistributor::where('distributor_code', $partyCode)->value('id')) {
            $party = ['type' => 'master_distributor', 'id' => $distributorId];
        }

        return $this->partyCache[$partyCode] = $party;
    }

    /**
     * product_code is matched against products.product_code, then products.sap_code.
     */
    private function resolveProductId(string $productCode): ?int
    {
        if (array_key_exists($productCode, $this->productCache)) {
            return $this->productCache[$productCode];
        }

        $productId = Product::where('product_code', $productCode)->value('id')
            ?? Product::where('sap_code', $productCode)->value('id');

        return $this->productCache[$productCode] = $productId;
    }

    private function toLocal(string $value): Carbon
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }
}
