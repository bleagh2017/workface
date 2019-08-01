<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'user_detail';
    protected $fillable = [
        'real_name', 'user_id','age','job'
    ];
}
