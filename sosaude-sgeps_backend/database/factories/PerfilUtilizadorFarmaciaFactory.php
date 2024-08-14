<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\PerfilUtilizadorFarmacia;
use Faker\Generator as Faker;

$factory->define(PerfilUtilizadorFarmacia::class, function (Faker $faker) {

    return [
        'perfil' => $faker->word,
        'codigo' => $faker->randomDigitNotNull
    ];
});
