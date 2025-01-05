<?php

namespace Veneridze\PricingPlans\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

/**
 * Class PlanSubscriptionUsage
 * @package Veneridze\PricingPlans\Models
 * @property int $id
 * @property int $subscription_id
 * @property string $feature_code
 * @property int $used
 * @property \Illuminate\Support\Carbon $valid_until
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PlanSubscriptionUsage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'valid_until',
        'used',
        'feature_code',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'valid_until',
    ];

    /**
     * Plan constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::get('plans.tables.plan_subsription_usages'));
    }

    /**
     * Get feature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(
            Config::get('plans.models.Feature'),
            'feature_code',
            'code'
        );
    }

    /**
     * Get subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            Config::get('plans.models.PlanSubscription'),
            'subscription_id',
            'id'
        );
    }

    /**
     * Scope by feature code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|\Veneridze\PricingPlans\Models\Feature $feature
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFeature($query, $feature)
    {
        return $query->where('feature_code', $feature instanceof Feature ? $feature->code : $feature);
    }

    /**
     * Check whether usage has been expired or not.
     *
     * @return bool
     */
    public function isExpired()
    {
        if (is_null($this->valid_until)) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
}
