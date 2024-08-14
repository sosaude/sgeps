<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Empresa;
use Faker\Generator as Faker;

$factory->define(Empresa::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'categoria_empresa_id' => $faker->randomDigitNotNull,
        'endereco' => $faker->word,
        'email' => $faker->word,
        'nuit' => $faker->word,
        'contactos' => $faker->word,
        'delegacao' => $faker->word,
        'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
