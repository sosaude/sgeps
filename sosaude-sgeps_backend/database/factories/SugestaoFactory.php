<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Sugestao;
use Faker\Generator as Faker;

$factory->define(Sugestao::class, function (Faker $faker) {

    return [
        'conteudo' => $faker->text,
        'user_id' => $faker->randomDigitNotNull,
        'created_at' => $faker->date('Y-m-d H:i:s')
    ];
});
