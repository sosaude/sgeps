<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Role;
use Faker\Generator as Faker;

$factory->define(Role::class, function (Faker $faker) {

    return [
        'codigo' => $faker->randomDigitNotNull,
        'role' => $faker->word,
        'seccao_id' => $faker->randomDigitNotNull
    ];
});
