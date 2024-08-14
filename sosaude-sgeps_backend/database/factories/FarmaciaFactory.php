<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Farmacia;
use Faker\Generator as Faker;

$factory->define(Farmacia::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'endereco' => $faker->word,
        'horario_funcionamento' => $faker->text,
        'activa' => $faker->word,
        'contactos' => $faker->word,
        'latitude' => $faker->word,
        'longitude' => $faker->word,
        'deleted_at' => $faker->date('Y-m-d H:i:s'),
        'created_at' => $faker->date('Y-m-d H:i:s'),
        'updated_at' => $faker->date('Y-m-d H:i:s'),
        'numero_alvara' => $faker->word,
        'data_alvara_emissao' => $faker->word,
        'observacoes' => $faker->word
    ];
});
