<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 1:15 AM
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Entities\Role::class, function () {

    $roles = (new ReflectionClass(\App\Entities\Role::class))->getConstants();
    unset($roles['CREATED_AT']);
    unset($roles['UPDATED_AT']);

    $roles = array_values($roles);

    $role = $roles[array_rand($roles)];

    return [
        'name' => str_slug($role),
        'display_name' => $role
    ];

});