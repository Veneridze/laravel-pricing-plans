<?php

use Faker\Generator;
use Veneridze\PricingPlans\Models\Feature;
use Veneridze\PricingPlans\Models\Plan;
use Veneridze\PricingPlans\Models\PlanFeature;

$factory->define(PlanFeature::class, function (Generator $faker) {
    return [
        'plan_id' => Plan::factory()->create()->id,
        'feature_id' => Feature::factory()->create()->id,
        'value' => $faker->randomElement(['10', '20', '30', '50', 'Y', 'N', 'UNLIMITED', null]),
    ];
});
