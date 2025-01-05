<?php

namespace Veneridze\PricingPlans\Models;

use Illuminate\Support\Carbon;
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
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array
     */
    protected $with = ['plans'];


    /**
     * Plan constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::get('plans.tables.groups'));
    }

    /**
     * Get plans
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('plans.models.Plan'),
            Config::get('plans.models.PlanGroup'),
            'group_id',
            'plan_id'
        );
    }
}
