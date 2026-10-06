<?php

namespace App\Http\Controllers;

use App\Models\PromotionalGift;
use App\Models\PromotionalGiftStockMovement;
use App\Services\PromotionalGiftStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PromotionalGiftController extends Controller
{
    private function canManage(string $permission): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('superadmin') || $user->can($permission));
    }

    public function index(Request $request)
    {
        abort_if(!$this->canManage('promotional_gift_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $movementSum = fn (string $type) => '(SELECT COALESCE(SUM(ABS(m.quantity)), 0) FROM promotional_gift_stock_movements m'
                ." WHERE m.promotional_gift_id = promotional_gifts.id AND m.type = '{$type}')";
            $query = PromotionalGift::with('creator')
                ->select('promotional_gifts.*')
                ->selectRaw($movementSum('add').' as added_stock')
                ->selectRaw($movementSum('activity').' as issued_stock')
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_toggle', function (PromotionalGift $gift) {
                    if (!$this->canManage('promotional_gift_active')) {
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
                    if ($this->canManage('promotional_gift_edit')) {
                        $buttons .= '<button type="button" class="btn btn-success btn-just-icon btn-sm addStock" data-id="'.$gift->id.'" data-name="'.e($gift->name).'" title="Add Stock"><i class="material-icons">add</i></button>';
                    }
                    $buttons .= '<button type="button" class="btn btn-info btn-just-icon btn-sm stockMovement" data-id="'.$gift->id.'" data-name="'.e($gift->name).'" title="Stock Movement"><i class="material-icons">swap_vert</i></button>';
                    if ($this->canManage('promotional_gift_edit')) {
                        $buttons .= '<button type="button" class="btn btn-theme btn-just-icon btn-sm editGift" data-id="'.$gift->id.'" title="Edit Gift"><i class="material-icons">edit</i></button>';
                    }
                    if ($this->canManage('promotional_gift_delete')) {
                        $buttons .= '<button type="button" class="btn btn-danger btn-just-icon btn-sm deleteGift" data-id="'.$gift->id.'" title="Delete Gift"><i class="material-icons">clear</i></button>';
                    }
                    return '<div class="btn-group btn-group-sm" role="group">'.$buttons.'</div>';
                })
                ->editColumn('added_stock', fn (PromotionalGift $gift) => (int) $gift->added_stock)
                ->editColumn('issued_stock', fn (PromotionalGift $gift) => (int) $gift->issued_stock)
                ->editColumn('created_at', fn (PromotionalGift $gift) => showdatetimeformat($gift->created_at))
                ->rawColumns(['status_toggle', 'action'])
                ->make(true);
        }

        return view('promotional_gifts.index');
    }

    public function store(Request $request)
    {
        abort_if(!$this->canManage('promotional_gift_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('promotional_gifts', 'name')],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $gift = PromotionalGift::create($validated + [
                'opening_stock' => $validated['quantity'],
                'active' => 'Y',
                'created_by' => auth()->id(),
            ]);
            app(PromotionalGiftStock::class)->opening($gift, auth()->id());
        });

        return redirect()->route('promotional-gifts.index')->with('message_success', 'Gift created successfully.');
    }

    public function edit(PromotionalGift $promotionalGift)
    {
        abort_if(!$this->canManage('promotional_gift_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->json($promotionalGift);
    }

    public function update(Request $request, PromotionalGift $promotionalGift)
    {
        abort_if(!$this->canManage('promotional_gift_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('promotional_gifts', 'name')->ignore($promotionalGift->id)],
        ]);

        // Stock is not edited here: use Add Stock so every change is in the stock movement

        $promotionalGift->update($validated + ['updated_by' => auth()->id()]);

        return redirect()->route('promotional-gifts.index')->with('message_success', 'Gift updated successfully.');
    }

    public function destroy(PromotionalGift $promotionalGift)
    {
        abort_if(!$this->canManage('promotional_gift_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::transaction(function () use ($promotionalGift) {
            $promotionalGift->stockMovements()->delete();
            $promotionalGift->delete();
        });

        return response()->json(['status' => 'success', 'message' => 'Gift deleted successfully.']);
    }

    public function addStock(Request $request, PromotionalGift $promotionalGift, PromotionalGiftStock $stock)
    {
        abort_if(!$this->canManage('promotional_gift_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'remark' => ['nullable', 'string', 'max:255'],
        ]);

        $stock->add($promotionalGift, (int) $validated['quantity'], $validated['remark'] ?? null, auth()->id());

        return response()->json(['status' => 'success', 'message' => $validated['quantity'].' added to '.$promotionalGift->name.'.']);
    }

    public function movements(PromotionalGift $promotionalGift)
    {
        abort_if(!$this->canManage('promotional_gift_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $movements = $promotionalGift->stockMovements()->with('creator')->orderByDesc('id')->get()
            ->map(fn (PromotionalGiftStockMovement $movement) => [
                'date' => showdatetimeformat($movement->created_at),
                'type' => PromotionalGiftStockMovement::TYPES[$movement->type] ?? $movement->type,
                'in' => $movement->quantity > 0 ? $movement->quantity : '',
                'out' => $movement->quantity < 0 ? abs($movement->quantity) : '',
                'balance' => $movement->balance_after,
                'remark' => $movement->remark,
                'by' => $movement->creator->name ?? '-',
            ]);

        return response()->json([
            'name' => $promotionalGift->name,
            'opening_stock' => (int) $promotionalGift->opening_stock,
            'current_stock' => (int) $promotionalGift->quantity,
            'movements' => $movements,
        ]);
    }

    public function active(Request $request, PromotionalGift $promotionalGift)
    {
        abort_if(!$this->canManage('promotional_gift_active'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $promotionalGift->update([
            'active' => $promotionalGift->active === 'Y' ? 'N' : 'Y',
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Gift status updated successfully.']);
    }
}
