<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotionalGift;

class PromotionalGiftController extends Controller
{
    public function index()
    {
        $gifts = PromotionalGift::query()
            ->where('active', 'Y')
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'quantity']);

        return response()->json([
            'success' => true,
            'data' => $gifts,
        ]);
    }
}
