<?php

namespace App\Services;

use App\Models\Customers;
use Illuminate\Validation\ValidationException;

class OrderPartyResolver
{
    public function resolve(int $customerId, ?int $parentCustomerId = null, ?int $customerTypeId = null): array
    {
        $customer = Customers::with('customertypes')->where('active', 'Y')->find($customerId);

        if (!$customer || !$customer->customertypes || $customer->customertypes->active !== 'Y') {
            throw ValidationException::withMessages([
                'buyer_id' => 'Please select an active customer with an active customer type.',
            ]);
        }

        if ($customerTypeId && (int) $customer->customertype !== $customerTypeId) {
            throw ValidationException::withMessages([
                'customer_type_id' => 'The selected customer does not belong to this customer type.',
            ]);
        }

        $isRetailer = strtolower(trim((string) $customer->customertypes->type_name)) === 'retailer';
        $seller = $customer;

        if ($isRetailer) {
            if (!$parentCustomerId) {
                throw ValidationException::withMessages([
                    'seller_id' => 'Please select a parent customer for the retailer.',
                ]);
            }

            $seller = Customers::with('customertypes')->where('active', 'Y')->find($parentCustomerId);

            if (
                !$seller ||
                !$seller->customertypes ||
                $seller->customertypes->active !== 'Y' ||
                $seller->customertypes->isRetailer()
            ) {
                throw ValidationException::withMessages([
                    'seller_id' => 'Please select an active Dealer or Distributor parent customer.',
                ]);
            }
        }

        return [
            'buyer_id' => $customer->id,
            'seller_id' => $seller->id,
            'order_type' => strtoupper((string) ($customer->customertypes->type_name ?: $customer->customertypes->customertype_name)),
            'customer' => $customer,
            'seller' => $seller,
            'is_retailer' => $isRetailer,
        ];
    }
}
