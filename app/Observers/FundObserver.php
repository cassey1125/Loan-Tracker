<?php

namespace App\Observers;

use App\Models\Fund;
use App\Services\Monitoring\AuditLogger;

class FundObserver
{
    public function created(Fund $fund): void
    {
        AuditLogger::log('created', $fund, null, $fund->attributesToArray());
    }

    public function updated(Fund $fund): void
    {
        $changes = AuditLogger::onlyDirtyAttributes($fund);
        if (!empty($changes['before']) || !empty($changes['after'])) {
            AuditLogger::log('updated', $fund, $changes['before'], $changes['after']);
        }
    }

    public function deleted(Fund $fund): void
    {
        AuditLogger::log('deleted', $fund, $fund->attributesToArray(), null);
    }
}
