<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UtilizadorFarmacia;
use Faker\Generator as Faker;

$factory->define(UtilizadorFarmacia::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'farmacia_id' => $faker->randomDigitNotNull,
        'contacto' => $faker->word,
        'numero_caderneta' => $faker->randomDigitNotNull,
        'categoria_profissional' => $faker->word,
        'Nacionalidade' => $faker->word,
        'observacoes' => $faker->word
    ];
});
