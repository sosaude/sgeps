<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Medicamento;
use Faker\Generator as Faker;

$factory->define(Medicamento::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'deleted_at' => $faker->date('Y-m-d H:i:s'),
        'codigo' => $faker->word
    ];
});
