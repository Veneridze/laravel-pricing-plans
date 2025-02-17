<?php

namespace Veneridze\PricingPlans\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Veneridze\PricingPlans\Period;
use Veneridze\PricingPlans\Traits\HasCode;
use Veneridze\PricingPlans\Traits\Resettable;

/**
 * Class Plan
 * @package Veneridze\PricingPlans\Models
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property string $interval_unit
 * @property int $interval_count
 * @property int $trial_period_days
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Plan extends Model
{
    use Resettable;
    use HasCode;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'interval_unit',
        'interval_count',
        'trial_period_days',
        'sort_order',
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
     * Boot function for using with User Events.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Default interval is 1 month
        static::saving(function ($model) {
            if (!$model->interval_unit) {
                $model->interval_unit = 'month';
            }

            if (!$model->interval_count) {
                $model->interval_count = 1;
            }
        });
    }

    /**
     * Plan constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::get('plans.tables.plans'));
    }

    /**
     * Get plan features.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function features(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Config::get('plans.models.Feature'),
                Config::get('plans.tables.plan_features'),
                'plan_id',
                'feature_id'
            )
            ->using(Config::get('plans.models.PlanFeature'))
            ->withPivot(['value', 'note'])
            ->orderBy('sort_order');
    }

    /**
     * Get plan subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Config::get('plans.models.PlanSubscription'),
            'plan_id',
            'id'
        );
    }

    /**
     * Check if plan is free.
     *
     * @return bool
     */
    public function isFree(): bool
    {
        return ((float)$this->price <= 0.00);
    }

    /**
     * Check if plan has trial.
     *
     * @return bool
     */
    public function hasTrial(): bool
    {
        return (is_numeric($this->trial_period_days) and $this->trial_period_days > 0);
    }
}
