<?php

namespace Veneridze\PricingPlans\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Veneridze\PricingPlans\Events\SubscriptionRenewed;
use Veneridze\PricingPlans\Events\SubscriptionCanceled;
use Veneridze\PricingPlans\Events\SubscriptionPlanChanged;
use Veneridze\PricingPlans\Period;
use Veneridze\PricingPlans\SubscriptionAbility;
use Veneridze\PricingPlans\SubscriptionUsageManager;
use LogicException;
use Veneridze\PricingPlans\Traits\BelongsToPlanModel;

/**
 * Class PlanSubscription
 * @package Veneridze\PricingPlans\Models
 * @property int $id
 * @property string $subscriber_type
 * @property int $subscriber_id
 * @property int $plan_id
 * @property string $name
 * @property bool $canceled_immediately
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property \Illuminate\Support\Carbon $canceled_at
 * @property \Illuminate\Support\Carbon $trial_ends_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Veneridze\PricingPlans\Models\Plan $plan
 */
class PlanSubscription extends Model
{
    use BelongsToPlanModel;
    use HasFactory;

    /**
     * Subscription statuses
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_ENDED = 'ended';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
        'plan_id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'starts_at',
        'ends_at',
        'canceled_at',
        'trial_ends_at',
    ];

    /**
     * @var array
     */
    protected $with = ['plan'];

    /**
     * Subscription Ability Manager instance.
     *
     * @var \Veneridze\PricingPlans\SubscriptionAbility
     */
    protected $ability;

    /**
     * Boot function for using with User Events.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Set period if it wasn't set
            if (!$model->ends_at) {
                $model->setNewPeriod();
            }
        });

        static::saved(function ($model) {
            /** @var PlanSubscription $model */
            if ($model->getOriginal('plan_id') && $model->getOriginal('plan_id') !== $model->plan_id) {
                Event::dispatch(new SubscriptionPlanChanged($model));
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

        $this->setTable(Config::get('plans.tables.plan_subscriptions'));
    }

    public function limits(): array
    {
        return $this->usage->map(function (PlanSubscriptionUsage $usage) {
            return [
                'code' => $usage->feature_code,
                'name' => $usage->feature->name,
                'available' => $this->plan->features()->where('feature_id', Feature::where('code', $usage->feature_code)->firstOrFail())->first()->value, //$usage->feature->,
                'used' => $usage->used
            ];
        })->all();
    }

    /**
     * Get subscriber.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get subscription usage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usage(): HasMany
    {
        return $this->hasMany(
            Config::get('plans.models.PlanSubscriptionUsage'),
            'subscription_id',
            'id'
        );
    }

    /**
     * Get status attribute.
     *
     * @return string
     */
    public function getStatusAttribute(): string|null
    {
        if ($this->isActive()) {
            return self::STATUS_ACTIVE;
        }

        if ($this->isCanceled()) {
            return self::STATUS_CANCELED;
        }

        if ($this->isEnded()) {
            return self::STATUS_ENDED;
        }
        return null;
    }

    /**
     * Check if subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->isEnded() || $this->onTrial()) {
            return true;
        }

        return false;
    }

    /**
     * Check if subscription is trialling.
     *
     * @return bool
     */
    public function onTrial(): bool
    {
        if (!is_null($trialEndsAt = $this->trial_ends_at)) {
            return Carbon::now()->lt(Carbon::parse($trialEndsAt));
        }

        return false;
    }

    /**
     * Check if subscription is canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return !is_null($this->canceled_at);
    }

    /**
     * Check if subscription is canceled immediately.
     *
     * @return bool
     */
    public function isCanceledImmediately(): bool
    {
        return !is_null($this->canceled_at) && $this->canceled_immediately === true;
    }

    /**
     * Check if subscription period has ended.
     *
     * @return bool
     */
    public function isEnded(): bool
    {
        $endsAt = Carbon::parse($this->ends_at);

        return Carbon::now()->gte($endsAt);
    }

    /**
     * Cancel subscription.
     *
     * @param bool $immediately
     * @return PlanSubscription
     * @throws \Throwable
     */
    public function cancel($immediately = false)
    {
        $this->canceled_at = Carbon::now();

        if ($immediately) {
            $this->canceled_immediately = true;
            $this->ends_at = $this->canceled_at;
        }

        $this->saveOrFail();

        Event::dispatch(new SubscriptionCanceled($this));

        return $this;
    }

    /**
     * Change subscription plan.
     *
     * @param int|\Veneridze\PricingPlans\Models\Plan $plan Plan Id or Plan Model Instance
     * @return PlanSubscription|false
     * @throws InvalidArgumentException
     */
    public function changePlan($plan)
    {
        if (!($plan instanceof Plan)) {
            // Try find by Plan ID
            $plan = App::make(Config::get('plans.models.Plan'))->find($plan);

            if (!$plan) {
                // Try find by Plan Code
                $plan = App::make(Config::get('plans.models.Plan'))->findByCode($plan);
            }
        }

        if (is_null($plan) || !($plan instanceof Plan)) {
            throw new InvalidArgumentException('Invalid plan instance');
        }

        // If plans doesn't have the same billing frequency (e.g., interval
        // and interval_count) we will update the billing dates starting
        // today... and since we are basically creating a new billing cycle,
        // the usage data will be cleared.
        if (
            is_null($this->plan) ||
            $this->plan->interval_unit !== $plan->interval_unit ||
            $this->plan->interval_count !== $plan->interval_count
        ) {
            // Set period
            $this->setNewPeriod($plan->interval_unit, $plan->interval_count);

            // Clear usage data
            $usageManager = new SubscriptionUsageManager($this);
            $usageManager->clear();
        }

        // Attach new plan to subscription
        $this->plan_id = $plan->id;

        return $this;
    }

    /**
     * Renew subscription period.
     *
     * @return self
     * @throws LogicException
     */
    public function renew()
    {
        if ($this->isEnded() and $this->isCanceled()) {
            throw new LogicException(
                'Unable to renew canceled ended subscription.'
            );
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription) {
            // Clear usage data
            $usageManager = new SubscriptionUsageManager($subscription);
            $usageManager->clear();

            // Renew period
            $subscription->setNewPeriod();
            $subscription->canceled_at = null;
            $subscription->save();
        });

        Event::dispatch(new SubscriptionRenewed($this));

        return $this;
    }

    /**
     * Get Subscription Ability instance.
     *
     * @return \Veneridze\PricingPlans\SubscriptionAbility
     */
    public function ability()
    {
        if (is_null($this->ability)) {
            return new SubscriptionAbility($this);
        }

        return $this->ability;
    }

    /**
     * Find by user id.
     *
     * @param  \Illuminate\Database\Eloquent\Builder
     * @param  \Veneridze\PricingPlans\Contracts\Subscriber $subscriber
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySubscriber($query, $subscriber)
    {
        return $query->where('subscriber_id', $subscriber->id)
            ->where('subscriber_type', get_class($subscriber));
    }

    /**
     * Find subscription with an ending trial.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dayRange
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndingTrial($query, $dayRange = 3)
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $query->whereBetween('trial_ends_at', [$from, $to]);
    }

    /**
     * Find subscription with an ended trial.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndedTrial($query)
    {
        return $query->where('trial_ends_at', '<=', Carbon::now());
    }

    /**
     * Find ending subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dayRange
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndingPeriod($query, $dayRange = 3)
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $query->whereBetween('ends_at', [$from, $to]);
    }

    /**
     * Find ended subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindEndedPeriod($query)
    {
        return $query->where('ends_at', '<=', Carbon::now());
    }

    /**
     * Set subscription period.
     *
     * @param  string $intervalUnit
     * @param  int $intervalCount
     * @param  null|int|string|\DateTime $startAt Start time
     * @return  PlanSubscription
     */
    protected function setNewPeriod(string $intervalUnit = '', int $intervalCount = 0, $startAt = null)
    {
        if (empty($intervalUnit)) {
            $intervalUnit = $this->plan->interval_unit;
        }

        if (empty($intervalCount)) {
            $intervalCount = $this->plan->interval_count;
        }

        $period = new Period($intervalUnit, $intervalCount, $startAt);

        $this->starts_at = $period->getStartAt();
        $this->ends_at = $period->getEndAt();

        return $this;
    }
}
