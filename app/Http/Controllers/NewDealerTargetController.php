<?php

namespace App\Http\Controllers;

use App\Exports\NewDealerTargetExport;
use App\Imports\NewDealerTargetImport;
use App\Models\DealerAppointment;
use App\Models\Division;
use App\Models\NewDealerTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class NewDealerTargetController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'first_name', 'last_name', 'employee_codes']);

        $zones = Division::query()
            ->where('active', 'Y')
            ->orderBy('division_name')
            ->get(['id', 'division_name']);

        if (! Schema::hasTable('new_dealer_targets')) {
            return view('sales.new_dealer_targets', [
                'dealerTargets' => collect(),
                'users' => $users,
                'zones' => $zones,
                'setupRequired' => true,
            ]);
        }

        $dealerTargets = NewDealerTarget::query()
            ->with(['user.getdivision'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where(function ($nameQuery) use ($search) {
                        $nameQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_codes', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->filled('zone_id'), function ($query) use ($request) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('division_id', (int) $request->input('zone_id'));
                });
            })
            ->when(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->input('month')), function ($query) use ($request) {
                $month = Carbon::createFromFormat('Y-m', $request->input('month'));
                $query->whereDate('target_month', $month->startOfMonth()->toDateString());
            })
            ->latest('target_month')
            ->latest('id')
            ->get()
            ->map(function (NewDealerTarget $target) {
                if ($target->achievement === null) {
                    $month = Carbon::parse($target->target_month);
                    $target->achievement = DealerAppointment::query()
                        ->where('created_by', $target->user_id)
                        ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                        ->count();
                }

                return $target;
            });

        return view('sales.new_dealer_targets', compact('dealerTargets', 'users', 'zones'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('new_dealer_targets') || ! Schema::hasColumn('new_dealer_targets', 'achievement')) {
            return redirect()->route('new-dealer-targets')
                ->with('setup_error', 'Dealer targets achievement setup is pending. Please run the latest database migration.');
        }

        $validated = $request->validate([
            'target_id' => ['nullable', 'integer', Rule::exists('new_dealer_targets', 'id')],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'target_month' => ['required', 'date_format:Y-m'],
            'target' => ['required', 'integer', 'min:1'],
            'achievement' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $validated['target_month'])->startOfMonth();
        $targetId = $validated['target_id'] ?? null;

        if ($targetId) {
            abort_unless(auth()->user()->can('new_dealer_target_edit'), 403, '403 Forbidden');
        }

        $duplicate = NewDealerTarget::query()
            ->where('user_id', $validated['user_id'])
            ->whereDate('target_month', $month->toDateString())
            ->when($targetId, function ($query) use ($targetId) {
                $query->where('id', '!=', $targetId);
            })
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'target_month' => 'A target already exists for this user and month.',
            ]);
        }

        $values = [
            'user_id' => $validated['user_id'],
            'target_month' => $month->toDateString(),
            'target' => $validated['target'],
            'achievement' => $targetId && array_key_exists('achievement', $validated) ? $validated['achievement'] : null,
            'note' => $validated['note'] ?? null,
            'created_by' => auth()->id(),
        ];

        if ($targetId) {
            NewDealerTarget::query()->findOrFail($targetId)->update($values);
        } else {
            NewDealerTarget::query()->create($values);
        }

        return redirect()->route('new-dealer-targets')->with('success', $targetId
            ? 'New dealer target updated successfully.'
            : 'New dealer target saved successfully.');
    }

    public function export(Request $request)
    {
        abort_unless(Schema::hasTable('new_dealer_targets'), 404);

        return Excel::download(
            new NewDealerTargetExport($request),
            'new_dealer_appointment_targets_' . now()->format('Y_m_d') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        if (! Schema::hasTable('new_dealer_targets') || ! Schema::hasColumn('new_dealer_targets', 'achievement')) {
            return redirect()->route('new-dealer-targets')
                ->with('setup_error', 'Dealer targets achievement setup is pending. Please run the latest database migration.');
        }

        $request->validateWithBag('import', [
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new NewDealerTargetImport(auth()->id());

        try {
            Excel::import($import, $request->file('import_file'));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('new-dealer-targets')
                ->with('import_error', 'The file could not be imported. Please check the column headings and data format.');
        }

        $summary = "Import completed: {$import->added} added, {$import->updated} updated, {$import->unchanged} unchanged";
        if ($import->skipped > 0) {
            $summary .= ", {$import->skipped} skipped";
        }

        return redirect()->route('new-dealer-targets')->with('success', $summary . '.');
    }

    public function destroy(NewDealerTarget $newDealerTarget)
    {
        abort_unless(auth()->user()->can('new_dealer_target_delete'), 403, '403 Forbidden');

        $newDealerTarget->delete();

        return redirect()->route('new-dealer-targets')->with('success', 'New dealer target deleted successfully.');
    }
}
