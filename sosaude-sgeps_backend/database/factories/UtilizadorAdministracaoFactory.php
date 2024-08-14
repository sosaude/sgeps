<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UtilizadorAdministracao;
use Faker\Generator as Faker;

$factory->define(UtilizadorAdministracao::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'contacto' => $faker->word,
        'email' => $faker->word,
        'activo' => $faker->word,
        'role_id' => $faker->randomDigitNotNull,
        'created_at' => $faker->date('Y-m-d H:i:s'),
        'updated_at' => $faker->date('Y-m-d H:i:s'),
        'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
