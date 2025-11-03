<?php

namespace App\Observers;

use App\Models\Deal;
use App\Traits\DealHistoryTrait;

class DealLabelObserver
{
    use DealHistoryTrait;

    public function synced(Deal $deal, $relation, $changes)
    {
        if ($relation === 'dealLabels' && !isRunningInConsoleOrSeeding() && user()) {
            foreach ($changes['attached'] ?? [] as $labelId) {
                self::createDealHistory($deal->id, 'label-added', labelId: $labelId);
            }

            foreach ($changes['detached'] ?? [] as $labelId) {
                self::createDealHistory($deal->id, 'label-removed', labelId: $labelId);
            }
        }
    }
}
