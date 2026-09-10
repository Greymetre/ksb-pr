<?php

namespace App\Http\Controllers;

use App\Models\PromotionalGift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PromotionalGiftController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('promotional_gift_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            return DataTables::of(PromotionalGift::with('creator')->latest())
                ->addIndexColumn()
                ->addColumn('status_toggle', function (PromotionalGift $gift) {
                    if (auth()->user()->cannot('promotional_gift_active')) {
                        return $gift->active === 'Y'
                            ? '<span class="badge badge-success">Active</span>'
                            : '<span class="badge badge-secondary">Inactive</span>';
                    }
                    $checked = $gift->active === 'Y' ? 'checked' : '';

                    return '<div class="togglebutton"><label>'
                        .'<input type="checkbox" '.$checked.' data-id="'.$gift->id.'" class="activeRecord">'
                        .'<span class="toggle"></span></label></div>';
                })
                ->addColumn('action', function (PromotionalGift $gift) {
                    $buttons = '';
                    if (auth()->user()->can('promotional_gift_edit')) {
                        $buttons .= '<button type="button" class="btn btn-theme btn-just-icon btn-sm editGift" data-id="'.$gift->id.'" title="Edit Gift"><i class="material-icons">edit</i></button>';
                    }
                    if (auth()->user()->can('promotional_gift_delete')) {
                        $buttons .= '<button type="button" class="btn btn-danger btn-just-icon btn-sm deleteGift" data-id="'.$gift->id.'" title="Delete Gift"><i class="material-icons">clear</i></button>';
                    }
                    return '<div class="btn-group btn-group-sm" role="group">'.$buttons.'</div>';
                })
                ->editColumn('created_at', fn (PromotionalGift $gift) => showdatetimeformat($gift->created_at))
                ->rawColumns(['status_toggle', 'action'])
                ->make(true);
        }

        return view('promotional_gifts.index');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('promotional_gift_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('promotional_gifts', 'name')],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        PromotionalGift::create($validated + [
            'active' => 'Y',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('promotional-gifts.index')->with('message_success', 'Gift created successfully.');
    }

    public function edit(PromotionalGift $promotionalGift)
    {
        abort_if(Gate::denies('promotional_gift_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->json($promotionalGift);
    }

    public function update(Request $request, PromotionalGift $promotionalGift)
    {
        abort_if(Gate::denies('promotional_gift_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('promotional_gifts', 'name')->ignore($promotionalGift->id)],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $promotionalGift->update($validated + ['updated_by' => auth()->id()]);

        return redirect()->route('promotional-gifts.index')->with('message_success', 'Gift updated successfully.');
    }

    public function destroy(PromotionalGift $promotionalGift)
    {
        abort_if(Gate::denies('promotional_gift_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $promotionalGift->delete();

        return response()->json(['status' => 'success', 'message' => 'Gift deleted successfully.']);
    }

    public function active(Request $request, PromotionalGift $promotionalGift)
    {
        abort_if(Gate::denies('promotional_gift_active'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $promotionalGift->update([
            'active' => $promotionalGift->active === 'Y' ? 'N' : 'Y',
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Gift status updated successfully.']);
    }
}
