<?php

namespace Veneridze\PricingPlans\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Config;
use Veneridze\PricingPlans\Traits\BelongsToPlanModel;

/**
 * Class PlanFeature
 * @package Veneridze\PricingPlans\Models
 * @property int $id
 * @property int $plan_id
 * @property int $feature_id
 * @property int|string $value
 * @property string $note
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PlanFeature extends Pivot
{
    use BelongsToPlanModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'value',
        'note',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feature()
    {
        return $this->belongsTo(
            Config::get('plans.models.Feature'),
            'feature_id',
            'id'
        );
    }
}
