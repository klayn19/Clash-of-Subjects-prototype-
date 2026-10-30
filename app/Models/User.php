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
        'parent_email',
        'password',
        'role',
        'section',
        'subject',
        'is_verified',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Ensure LRN is cleanly stored without whitespace or empty strings.
     */
    public function setLrnAttribute($value)
    {
        $this->attributes['lrn'] = ($value !== null && trim($value) !== '') ? trim($value) : null;
    }
}