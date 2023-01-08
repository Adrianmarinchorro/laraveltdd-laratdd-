<?php

use Faker\Generator as Faker;

$factory->define(App\UserProfile::class, function (Faker $faker) {
    return [
        'user_id' => factory(\App\User::class)->create(),
        'bio' => $faker->paragraph,
    ];
});
