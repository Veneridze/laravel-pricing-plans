<?php

use Faker\Generator;
use Veneridze\PricingPlans\Models\Plan;
use Veneridze\PricingPlans\Models\PlanSubscription;
use Veneridze\PricingPlans\Tests\Models\User;

$factory->define(PlanSubscription::class, function (Generator $faker) {
    return [
        'subscriber_type' => User::class,
        'subscriber_id' => User::factory()->create()->id,
        'plan_id' => Plan::factory()->create()->id,
        'name' => $faker->word,
        'canceled_immediately' => false,
    ];
});
