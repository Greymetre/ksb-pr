<?php

namespace App\Services;

use App\Models\PromotionalActivity;
use App\Models\PromotionalGift;
use App\Models\PromotionalGiftStockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/*
| All gift stock changes go through here so promotional_gifts.quantity and the
| promotional_gift_stock_movements ledger stay in step. Callers own the transaction.
*/
class PromotionalGiftStock
{
    public function opening(PromotionalGift $gift, ?int $userId): void
    {
        $this->record($gift, 'opening', (int) $gift->quantity, $userId, 'Opening stock');
    }

    public function add(PromotionalGift $gift, int $quantity, ?string $remark, ?int $userId): void
    {
        DB::transaction(function () use ($gift, $quantity, $remark, $userId) {
            $gift = PromotionalGift::whereKey($gift->id)->lockForUpdate()->firstOrFail();
            $gift->increment('quantity', $quantity, ['updated_by' => $userId]);
            $this->record($gift, 'add', $quantity, $userId, $remark);
        });
    }

    /**
     * Deduct the activity's gifts from stock. Must run inside a transaction.
     * Throws a ValidationException (422) when a gift does not have enough stock.
     * $at dates the movement (backfill of activities completed earlier).
     */
    public function issueForActivity(PromotionalActivity $activity, ?int $userId, ?Carbon $at = null): void
    {
        if (PromotionalGiftStockMovement::where('promotional_activity_id', $activity->id)->exists()) {
            return;
        }

        $rows = DB::table('promotional_activity_gifts')
            ->where('promotional_activity_id', $activity->id)
            ->orderBy('promotional_gift_id')
            ->get(['promotional_gift_id', 'quantity']);

        $gifts = PromotionalGift::whereIn('id', $rows->pluck('promotional_gift_id'))
            ->lockForUpdate()->get()->keyBy('id');

        $short = $rows->filter(fn ($row) => !isset($gifts[$row->promotional_gift_id])
            || $gifts[$row->promotional_gift_id]->quantity < $row->quantity);

        if ($short->isNotEmpty()) {
            $names = $short->map(fn ($row) => isset($gifts[$row->promotional_gift_id])
                ? $gifts[$row->promotional_gift_id]->name . ' (available ' . $gifts[$row->promotional_gift_id]->quantity . ', needed ' . $row->quantity . ')'
                : 'Gift #' . $row->promotional_gift_id . ' (deleted)');

            throw ValidationException::withMessages([
                'gifts' => 'Not enough gift stock: ' . $names->implode(', ') . '. Add stock on the Gift List first.',
            ]);
        }

        foreach ($rows as $row) {
            $gift = $gifts[$row->promotional_gift_id];
            $gift->decrement('quantity', $row->quantity, ['updated_by' => $userId]);
            $this->record($gift, 'activity', -$row->quantity, $userId, 'Activity #' . $activity->id . ' - ' . $activity->location_name, $activity->id, $at);
        }
    }

    private function record(PromotionalGift $gift, string $type, int $quantity, ?int $userId, ?string $remark, ?int $activityId = null, ?Carbon $at = null): void
    {
        $movement = new PromotionalGiftStockMovement();
        if ($at) {
            $movement->created_at = $at;
        }
        $movement->fill([
            'promotional_gift_id' => $gift->id,
            'type' => $type,
            'quantity' => $quantity,
            'balance_after' => (int) $gift->quantity,
            'promotional_activity_id' => $activityId,
            'remark' => $remark,
            'created_by' => $userId,
        ])->save();
    }
}
