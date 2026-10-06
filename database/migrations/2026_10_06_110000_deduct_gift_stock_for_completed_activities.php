<?php

use App\Models\PromotionalActivity;
use App\Services\PromotionalGiftStock;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/*
| Promotional activities completed before the gift stock ledger existed never
| reduced gift stock. Deduct their gifts now, oldest first, dated at completed_at.
| Activities already in the ledger are skipped, so this is safe to re-run.
| An activity whose gift stock is short is skipped and logged.
*/
return new class extends Migration
{
    public function up()
    {
        $stock = app(PromotionalGiftStock::class);

        PromotionalActivity::where('approval_status', 'completed')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('promotional_gift_stock_movements as m')
                ->whereColumn('m.promotional_activity_id', 'promotional_activities.id'))
            ->orderBy('completed_at')->orderBy('id')
            ->get()
            ->each(function (PromotionalActivity $activity) use ($stock) {
                try {
                    DB::transaction(fn () => $stock->issueForActivity(
                        $activity,
                        $activity->created_by,
                        Carbon::parse($activity->completed_at ?? $activity->updated_at)
                    ));
                } catch (ValidationException $e) {
                    Log::warning('Gift stock backfill skipped activity #' . $activity->id . ': ' . collect($e->errors())->flatten()->first());
                    echo 'Skipped activity #' . $activity->id . ': ' . collect($e->errors())->flatten()->first() . PHP_EOL;
                }
            });
    }

    public function down()
    {
        // Ledger entries are kept; reverse them from Stock Movement if ever needed
    }
};
