<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {

    return [
        'nome' => $faker->word,
        'email' => $faker->word,
        'email_verified_at' => $faker->date('Y-m-d H:i:s'),
        'codigo_login' => $faker->word,
        'password' => $faker->word,
        'remember_token' => $faker->word,
        'active' => $faker->word,
        'role_id' => $faker->randomDigitNotNull,
        'utilizador_farmacia_id' => $faker->randomDigitNotNull,
        'utilizador_empresa_id' => $faker->randomDigitNotNull,
        'created_at' => $faker->date('Y-m-d H:i:s'),
        'updated_at' => $faker->date('Y-m-d H:i:s'),
        'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
