<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CategoriaEmpresa;
use Faker\Generator as Faker;

$factory->define(CategoriaEmpresa::class, function (Faker $faker) {

    return [
        'codigo' => $faker->word,
        'categoria' => $faker->word
    ];
});
