<?php

use Faker\Generator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Veneridze\PricingPlans\Tests\Models\User;

$factory->define(User::class, function (Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => Hash::make(Str::random(10)),
        'remember_token' => Str::random(10),
    ];
});
