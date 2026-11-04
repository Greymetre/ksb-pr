<?php

namespace App\Services;

use App\Models\PromotionalActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/*
| After approval the creator submits what happened (participants, photos, actual expense, bills)
| and the activity waits in closure_pending until a manager closes it or returns it for correction.
| Shared by the app API and the CRM. Callers check who may submit or review.
*/
class PromotionalActivityExecution
{
    public const MAX_PHOTOS = 3;

    public const MAX_BILLS = 5;

    public const REVIEW_DECISIONS = ['closed', 'returned'];

    /**
     * Throws a ValidationException for bad input, a missing bill or short gift stock.
     * keep_photos[] / keep_bills[] keep files of a submission the manager returned.
     */
    public function submit(Request $request, PromotionalActivity $activity, User $user): void
    {
        $participants = json_decode((string) $request->input('participants', '[]'), true);
        $request->merge(['participants_data' => $participants]);
        Validator::make($request->all(), [
            'distributor_id' => 'required|integer|exists:customers,id',
            'photos' => 'nullable|array|max:'.self::MAX_PHOTOS,
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'keep_photos' => 'nullable|array',
            'keep_photos.*' => 'string',
            'participants_data' => 'required|array|min:1|max:50',
            'participants_data.*.name' => 'required|string|max:150',
            'participants_data.*.mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'participants_data.*.address' => 'required|string|max:255',
            'execution_remark' => 'nullable|string|max:1000',
            'actual_expense' => 'required|numeric|min:0',
            'bills' => 'nullable|array|max:'.self::MAX_BILLS,
            'bills.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'keep_bills' => 'nullable|array',
            'keep_bills.*' => 'string',
        ])->validate();

        $oldPhotos = $activity->activity_photos ?: [];
        $oldBills = $activity->bills ?: [];
        $keptPhotos = array_values(array_intersect($oldPhotos, (array) $request->input('keep_photos', [])));
        $keptBills = array_values(array_intersect($oldBills, (array) $request->input('keep_bills', [])));
        $photoCount = count($keptPhotos) + count($request->file('photos', []));
        $billCount = count($keptBills) + count($request->file('bills', []));

        if ($photoCount < 1 || $photoCount > self::MAX_PHOTOS) {
            throw ValidationException::withMessages(['photos' => 'Upload 1 to '.self::MAX_PHOTOS.' activity photos.']);
        }
        if ($billCount > self::MAX_BILLS) {
            throw ValidationException::withMessages(['bills' => 'Only '.self::MAX_BILLS.' bills can be uploaded.']);
        }
        if ((float) $request->actual_expense > 0 && $billCount === 0) {
            throw ValidationException::withMessages(['bills' => 'Upload at least one bill for the actual expense.']);
        }

        $newPhotos = array_map(fn ($file) => $file->store('promotional-activities', 'public'), $request->file('photos', []));
        $newBills = array_map(fn ($file) => $file->store('promotional-activities/bills', 'public'), $request->file('bills', []));

        try {
            DB::transaction(function () use ($request, $activity, $user, $participants, $keptPhotos, $keptBills, $newPhotos, $newBills) {
                $locked = PromotionalActivity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
                abort_if($locked->approval_status !== 'approved', 422, 'Only an approved activity can be submitted.');

                // Gifts given in the activity are deducted from gift stock (once, even if the manager returns it)
                app(PromotionalGiftStock::class)->issueForActivity($locked, $user->id);

                $locked->update([
                    'distributor_id' => $request->distributor_id,
                    'activity_photos' => array_merge($keptPhotos, $newPhotos),
                    'participants' => array_values($participants),
                    'execution_remark' => $request->input('execution_remark'),
                    'actual_expense' => $request->actual_expense,
                    'bills' => array_merge($keptBills, $newBills),
                    'approval_status' => 'closure_pending',
                    'execution_submitted_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete(array_merge($newPhotos, $newBills));
            throw $e;
        }
        Storage::disk('public')->delete(array_merge(array_diff($oldPhotos, $keptPhotos), array_diff($oldBills, $keptBills)));

        $activity->refresh();
        $message = $user->name.' has submitted the activity details and expenses of '.$activity->request_no.' for review.';
        if ($activity->isOverBudget()) {
            $message .= ' Actual expense ₹'.number_format((float) $activity->actual_expense, 2)
                .' is over the estimated budget ₹'.number_format((float) $activity->estimated_budget, 2).'.';
        }
        $activity->notifyManagers($message, 'Promotional activity review');
    }

    // closed: the activity is completed. returned: back to approved so the creator corrects and submits again.
    public function review(PromotionalActivity $activity, string $decision, ?string $remark, User $user): void
    {
        DB::transaction(function () use ($activity, $decision, $remark, $user) {
            $locked = PromotionalActivity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->approval_status !== 'closure_pending', 422, 'Activity is not waiting for review.');

            $locked->update([
                'approval_status' => $decision === 'closed' ? 'completed' : 'approved',
                'completed_at' => $decision === 'closed' ? now() : null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'review_remark' => $remark,
            ]);
        });

        $activity->refresh();
        [$title, $message] = $decision === 'closed'
            ? ['Promotional activity closed', 'Your promotional activity '.$activity->request_no.' has been reviewed and closed.']
            : ['Promotional activity returned', 'Your promotional activity '.$activity->request_no.' has been returned to correct the activity details.'];
        if ($remark) $message .= ' Remark: '.$remark;

        SendPushNotification($activity->created_by, $message, 'promotional_activity', $activity->id, $title);
    }
}
