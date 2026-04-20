<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        $totalBooks = \App\Models\Book::count();
        $totalItems = \App\Models\Book::sum('total_items') ?? 0;
        $verifiedBooks = \App\Models\StockOpname::where('status', 'verified')->count();
        $conditions = \App\Models\StockOpname::select('condition', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('condition')
            ->groupBy('condition')
            ->get();
        
        $contributors = \App\Models\User::withCount(['stockOpnames' => function($q) {
                $q->where('status', 'verified');
            }])
            ->orderByDesc('stock_opnames_count')
            ->get();

        $totalCommissionConfig = \App\Models\Setting::where('key', 'commission')->first();
        $currentCommissionValue = $totalCommissionConfig ? (float)$totalCommissionConfig->value : 0;

        // Calculate commissions on the fly based on current rate
        $contributors->map(function($user) use ($currentCommissionValue) {
            $user->total_commission = $user->stock_opnames_count * $currentCommissionValue;
            return $user;
        });

        $totalVerifiedCount = \App\Models\StockOpname::where('status', 'verified')->count();
        $totalCommission = $totalVerifiedCount * $currentCommissionValue;
        $currentCommission = $totalCommissionConfig;

        return response()->json([
            'overview' => [
                'total_books' => $totalBooks,
                'total_items' => $totalItems,
                'verified' => $verifiedBooks,
                'total_commission' => (float)$totalCommission,
                'current_commission' => $currentCommission ? (float)$currentCommission->value : 0,
                'progress_percentage' => $totalItems > 0 ? round(($verifiedBooks / $totalItems) * 100, 1) : 0,
            ],
            'conditions' => $conditions,
            'contributors' => $contributors,
        ]);
    }
}
