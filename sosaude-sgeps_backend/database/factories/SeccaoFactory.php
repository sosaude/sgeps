<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Seccao;
use Faker\Generator as Faker;

$factory->define(Seccao::class, function (Faker $faker) {

    return [
        'nome' => $faker->word
    ];
});
