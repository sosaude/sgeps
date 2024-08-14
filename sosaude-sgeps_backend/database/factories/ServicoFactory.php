<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Servico;
use Faker\Generator as Faker;

$factory->define(Servico::class, function (Faker $faker) {

    return [
        'nome' => $faker->word
    ];
});
