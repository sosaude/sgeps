<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\MarcaMedicamento;
use Faker\Generator as Faker;

$factory->define(MarcaMedicamento::class, function (Faker $faker) {

    return [
        'marca' => $faker->word,
        'medicamento_id' => $faker->randomDigitNotNull,
        'codigo' => $faker->word,
        'forma' => $faker->word,
        'dosagem' => $faker->word,
        'pais_origem' => $faker->word
    ];
});
