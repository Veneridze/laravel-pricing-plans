<?php

namespace Veneridze\PricingPlans\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Veneridze\PricingPlans\Events\SubscriptionRenewed;
use Veneridze\PricingPlans\Period;
use Veneridze\PricingPlans\SubscriptionAbility;
use Veneridze\PricingPlans\SubscriptionUsageManager;
use Veneridze\PricingPlans\Models\Concerns\BelongsToPlanModel;
use LogicException;

/**
 * Class Group
 * @package Veneridze\PricingPlans\Models
 * @property int $group_id
 * @property int $plan_id
 * @property \Illuminate\Support\Carbon $created_at
 */
class PlanGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];
    public const UPDATED_AT = null;

    /**
     * Plan constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::get('plans.tables.plan_groups'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(
            Config::get('plans.models.Group'),
            'group_id',
            'id'
        );
    }
}
