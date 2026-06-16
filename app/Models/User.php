<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'lrn',
        'first_name',
        'last_name',
        'age',
        'email',
        'password',
        'role',
        'section',
    ];

    protected $hidden = [
        'password',
    ];
}