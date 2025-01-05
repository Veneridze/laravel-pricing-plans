<?php

use Faker\Generator;
use Veneridze\PricingPlans\Models\Plan;
use Veneridze\PricingPlans\Period;

$factory->define(Plan::class, function (Generator $faker) {
    return [
        'name' => $faker->word,
        'code' => $faker->unique()->slug,
        'description' => $faker->sentence,
        'price' => rand(0, 9),
        'interval_unit' => $faker->randomElement([Period::MONTH, Period::YEAR]),
        'interval_count' => 1,
        'trial_period_days' => $faker->numberBetween(0, 10),
    ];
});
