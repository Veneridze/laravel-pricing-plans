<?php

namespace Veneridze\PricingPlans\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Veneridze\PricingPlans\Traits\HasCode;
use Veneridze\PricingPlans\Traits\Resettable;

/**
 * Class Feature
 * @package Veneridze\PricingPlans\Models
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Feature extends Model
{
    use Resettable;
    use HasCode;
    use HasFactory;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'interval_unit',
        'interval_count',
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
     * Plan constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::get('plans.tables.features'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function plans()
    {
        return $this->belongsToMany(
            Config::get('plans.models.Plan'),
            Config::get('plans.tables.plan_features'),
            'feature_id',
            'plan_id'
        )->using(Config::get('plans.models.PlanFeature'));
    }

    /**
     * Get feature usage.
     *
     * This will return all related subscriptions usages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usage()
    {
        return $this->hasMany(
            Config::get('plans.models.PlanSubscriptionUsage'),
            'feature_code',
            'code'
        );
    }
}
