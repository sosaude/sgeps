<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\GrupoTrabalhador;
use Faker\Generator as Faker;

$factory->define(GrupoTrabalhador::class, function (Faker $faker) {

    return [
        'created_at' => $faker->date('Y-m-d H:i:s'),
        'updated_at' => $faker->date('Y-m-d H:i:s')
    ];
});
