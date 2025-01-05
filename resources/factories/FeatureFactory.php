<?php

use Faker\Generator;
use Veneridze\PricingPlans\Models\Feature;
use Veneridze\PricingPlans\Period;

$factory->define(Feature::class, function (Generator $faker) {
    return [
        'name' => $faker->word,
        'code' => $faker->unique()->slug,
        'description' => $faker->sentence,
        'interval_unit' => $faker->randomElement([null, Period::DAY, Period::WEEK, Period::MONTH, Period::YEAR]),
        'interval_count' => $faker->numberBetween(0, 2),
    ];
});
