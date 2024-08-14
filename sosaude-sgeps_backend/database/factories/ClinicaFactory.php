<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Clinica;
use Faker\Generator as Faker;

$factory->define(Clinica::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'endereco' => $faker->word,
        'email' => $faker->word,
        'contactos' => $faker->word,
        'nuit' => $faker->randomDigitNotNull,
        'delegacao' => $faker->word,
        'created_at' => $faker->date('Y-m-d H:i:s'),
        'updated_at' => $faker->date('Y-m-d H:i:s'),
        'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
