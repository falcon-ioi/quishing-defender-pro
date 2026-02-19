<?php

namespace App\Http\Controllers;

use App\Models\ScanHistory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalScans = ScanHistory::count();
        $safeCount = ScanHistory::where('risk_level', 'SAFE')->count();
        $suspiciousCount = ScanHistory::where('risk_level', 'SUSPICIOUS')->count();
        $dangerousCount = ScanHistory::where('risk_level', 'DANGEROUS')->count();

        $recentScans = ScanHistory::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $dailyStats = ScanHistory::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN risk_level = "DANGEROUS" THEN 1 ELSE 0 END) as dangerous')
        )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        return view('dashboard.index', compact(
            'totalScans',
            'safeCount',
            'suspiciousCount',
            'dangerousCount',
            'recentScans',
            'dailyStats'
        ));
    }
}
