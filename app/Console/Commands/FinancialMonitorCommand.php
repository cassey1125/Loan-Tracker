<?php

namespace App\Console\Commands;

use App\Services\Monitoring\FinancialHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FinancialMonitorCommand extends Command
{
    protected $signature = 'financial:monitor';

    protected $description = 'Check financial data integrity and emit alerts.';

    public function handle(FinancialHealthService $healthService): int
    {
        $summary = $healthService->healthSummary();

        Log::info('Financial health summary', $summary);

        if (!$summary['has_critical_issues']) {
            $this->info('Financial monitor: no critical issues detected.');
            return self::SUCCESS;
        }

        $this->error('Financial monitor detected issues.');

        $alertEmail = env('FINANCIAL_ALERT_EMAIL');
        if ($alertEmail) {
            Mail::raw("Financial monitor detected issues:\n" . json_encode($summary, JSON_PRETTY_PRINT), function ($message) use ($alertEmail) {
                $message->to($alertEmail)->subject('Financial Integrity Alert');
            });
        }

        return self::FAILURE;
    }
}
