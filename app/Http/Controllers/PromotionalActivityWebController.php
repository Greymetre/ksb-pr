<?php

namespace App\Http\Controllers;

use App\Exports\PromotionalActivityExport;
use App\Models\PromotionalActivity;
use App\Models\Status;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PromotionalActivityWebController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_if(!$user || (!$user->hasRole('superadmin') && !$user->can('promotional_activity_access')), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function query(Request $request)
    {
        $query = PromotionalActivity::with(['activityType:id,display_name,status_name', 'creator:id,name', 'distributor:id,name,customer_code']);
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $visibleIds = array_map('intval', getUsersReportingToAuth($user->id));
            $query->whereIn('created_by', array_unique(array_merge($visibleIds, [(int) $user->id])));
        }
        if ($request->filled('activity_type_id')) $query->where('activity_status_id', $request->activity_type_id);
        if ($request->filled('date_from')) $query->whereDate('activity_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('activity_date', '<=', $request->date_to);
        return $query;
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();
        if ($request->ajax()) {
            return DataTables::of($this->query($request)->latest('activity_date'))
                ->addIndexColumn()
                ->addColumn('activity_type_name', fn ($row) => $row->activityType->display_name ?? $row->activityType->status_name ?? '-')
                ->addColumn('creator_name', fn ($row) => $row->creator->name ?? '-')
                ->addColumn('distributor_name', fn ($row) => $row->distributor->name ?? '-')
                ->addColumn('total_amount', fn ($row) => number_format((float) $row->company_share + (float) $row->distributor_share, 2))
                ->addColumn('participants_count', fn ($row) => count($row->participants ?: []))
                ->editColumn('activity_date', fn ($row) => $row->activity_date ? $row->activity_date->format('d-M-Y') : '-')
                ->editColumn('approval_status', fn ($row) => '<span class="badge badge-'.($row->approval_status === 'completed' ? 'success' : ($row->approval_status === 'rejected' ? 'danger' : 'warning')).'">'.ucwords(str_replace('_', ' ', $row->approval_status)).'</span>')
                ->editColumn('created_at', fn ($row) => showdatetimeformat($row->created_at))
                ->rawColumns(['approval_status'])->make(true);
        }
        $activityTypes = Status::where('module', 'Promotional Activity')->where('active', 'Y')->orderBy('display_name')->get(['id', 'display_name', 'status_name']);
        return view('promotional_activities.index', compact('activityTypes'));
    }

    public function export(Request $request)
    {
        $this->authorizeAccess();
        abort_if(!auth()->user()->hasRole('superadmin') && !auth()->user()->can('promotional_activity_export'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return Excel::download(new PromotionalActivityExport($this->query($request)), 'Promotional Activities.xlsx');
    }
}
