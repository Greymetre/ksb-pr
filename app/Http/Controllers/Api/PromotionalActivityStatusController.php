<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;

class PromotionalActivityStatusController extends Controller
{
    public function index()
    {
        $statuses = Status::query()
            ->where('module', 'Promotional Activity')
            ->where('active', 'Y')
            ->orderBy('display_name')
            ->get(['id', 'status_name', 'display_name', 'status_message']);

        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }
}
