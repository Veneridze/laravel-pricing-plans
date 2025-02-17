<?php

namespace Veneridze\PricingPlans\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;
use Veneridze\PricingPlans\Models\Plan;
use Veneridze\PricingPlans\SubscriptionBuilder;
use Veneridze\PricingPlans\SubscriptionUsageManager;

trait Subscribable
{
    /**
     * Get user plan subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(
            Config::get('plans.models.PlanSubscription'),
            'subscriber'
        );
    }

    /**
     * Get a subscription by name.
     *
     * @param  string $name Subscription name
     * @return \Veneridze\PricingPlans\Models\PlanSubscription|null
     */
    public function subscription(string $name = 'default'): \Veneridze\PricingPlans\Models\PlanSubscription|null
    {
        if ($this->relationLoaded('subscriptions')) {
            return $this->subscriptions
                ->orderByDesc(function ($subscription) {
                    return $subscription->created_at->getTimestamp();
                })
                ->first(function ($subscription) use ($name) {
                    return $subscription->name === $name;
                });
        }

        return $this->subscriptions()
            ->where('name', $name)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Check if the user has a given subscription.
     *
     * @param  string $subscription Subscription name
     * @param  string|null $planCode
     * @return bool
     */
    public function subscribed(Plan $plan = null, string $subscription = 'default'): bool
    {
        $planSubscription = $this->subscription($subscription);

        if (is_null($planSubscription)) {
            return false;
        }

        if (is_null($plan->code) || $plan->code == $planSubscription->plan->code) {
            return $planSubscription->isActive();
        }

        return false;
    }

    /**
     * Subscribe user to a new plan.
     *
     * @param string $subscription Subscription name
     * @param \Veneridze\PricingPlans\Models\Plan $plan
     * @return \Veneridze\PricingPlans\SubscriptionBuilder
     */
    public function newSubscription(Plan $plan, string $subscription = 'default')
    {
        return new SubscriptionBuilder($this, $subscription, $plan);
    }

    /**
     * Get subscription usage manager instance.
     *
     * @param  string $subscription Subscription name
     * @return \Veneridze\PricingPlans\SubscriptionUsageManager
     */
    public function subscriptionUsage(string $subscription = 'default')
    {
        return new SubscriptionUsageManager($this->subscription($subscription));
    }
}
