<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Sharer extends Model
{
    protected $table = 'sharer';
    protected $fillable = [
        'sharer_name', 'sex','head','content',"title","brand_name","brand_logo"
    ];
}
