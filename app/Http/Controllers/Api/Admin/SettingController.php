<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $commission = \App\Models\Setting::where('key', 'commission')->first();
        return response()->json([
            'commission' => $commission ? $commission->value : 0
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0'
        ]);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'commission'],
            ['value' => $request->commission]
        );

        // Sync all existing verified stock opnames to the new commission rate
        \App\Models\StockOpname::where('status', 'verified')
            ->update(['earned_commission' => $request->commission]);

        return response()->json(['message' => 'Setting updated successfully']);
    }
}
