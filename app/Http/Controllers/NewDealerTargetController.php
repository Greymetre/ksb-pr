<?php

namespace App\Http\Controllers;

use App\Models\DealerAppointment;
use App\Models\NewDealerTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewDealerTargetController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->where(function ($query) {
                $query->whereNull('isDeleted')->orWhere('isDeleted', '!=', 1);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'first_name', 'last_name', 'employee_codes']);

        $dealerTargets = NewDealerTarget::query()
            ->with(['user.getdivision'])
            ->latest('target_month')
            ->latest('id')
            ->get()
            ->map(function (NewDealerTarget $target) {
                $month = Carbon::parse($target->target_month);
                $target->achievement = DealerAppointment::query()
                    ->where('created_by', $target->user_id)
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count();

                return $target;
            });

        return view('sales.new_dealer_targets', compact('dealerTargets', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'target_month' => ['required', 'date_format:Y-m'],
            'target' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $validated['target_month'])->startOfMonth();

        NewDealerTarget::updateOrCreate(
            ['user_id' => $validated['user_id'], 'target_month' => $month->toDateString()],
            ['target' => $validated['target'], 'note' => $validated['note'] ?? null, 'created_by' => auth()->id()]
        );

        return redirect()->route('new-dealer-targets')->with('success', 'New dealer target saved successfully.');
    }
}
