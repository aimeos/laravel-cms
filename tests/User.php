<?php

namespace App\Models;


class User extends \Illuminate\Foundation\Auth\User
{
    protected $attributes = [
        'name' => '',
        'email' => '',
        'password' => '',
        'cmseditor' => 0,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'cmseditor',
    ];
}
