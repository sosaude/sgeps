<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FormaMarcaMedicamento;
use Faker\Generator as Faker;

$factory->define(FormaMarcaMedicamento::class, function (Faker $faker) {

    return [
        'forma' => $faker->word
    ];
});
