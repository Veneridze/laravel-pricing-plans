<?php

namespace Veneridze\PricingPlans\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

trait BelongsToPlanModel
{
    /**
     * Get plan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            Config::get('plans.models.Plan'),
            'plan_id',
            'id'
        );
    }

    /**
     * Scope by plan id.
     *
     * @param  \Illuminate\Database\Eloquent\Builder
     * @param  int $planId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPlan($query, $planId): Builder
    {
        return $query->where('plan_id', $planId);
    }
}
