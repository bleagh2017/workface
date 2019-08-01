<?php

namespace App\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User;

class Admins extends User
{
    const UPDATED_AT = null;
    protected $table = 'admins';
    protected $fillable = [
        'admin_name', 'phone', 'password','create_date','state','admin_role','remember_token'
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
}
