<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UtilizadorEmpresa;
use Faker\Generator as Faker;

$factory->define(UtilizadorEmpresa::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'empresa_id' => $faker->randomDigitNotNull,
        'contacto' => $faker->word,
        'activo' => $faker->word,
        'role_id' => $faker->randomDigitNotNull,
        'nacionalidade' => $faker->word,
        'observacoes' => $faker->word,
        'created_at' => $faker->date('Y-m-d H:i:s'),
        'updated_at' => $faker->date('Y-m-d H:i:s'),
        'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
