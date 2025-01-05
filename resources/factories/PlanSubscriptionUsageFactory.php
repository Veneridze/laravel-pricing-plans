<?php

use Faker\Generator;
use Veneridze\PricingPlans\Models\Feature;
use Veneridze\PricingPlans\Models\PlanSubscription;
use Veneridze\PricingPlans\Models\PlanSubscriptionUsage;

$factory->define(PlanSubscriptionUsage::class, function (Generator $faker) {
    return [
        'subscription_id' => PlanSubscription::factory()->create()->id,
        'feature_id' => Feature::factory()->create()->id,
        'used' => rand(1, 50),
        'valid_until' => $faker->dateTime(),
    ];
});
