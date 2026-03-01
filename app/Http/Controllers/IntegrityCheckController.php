<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ReconciliationReport;
use App\Services\Monitoring\FinancialHealthService;

class IntegrityCheckController extends Controller
{
    public function index(FinancialHealthService $healthService)
    {
        $summary = $healthService->healthSummary();

        return view('admin.integrity-check', [
            'summary' => $summary,
            'reports' => ReconciliationReport::latest('period_start')->limit(6)->get(),
            'recentAuditLogs' => AuditLog::with('user')->latest()->limit(20)->get(),
        ]);
    }
}
