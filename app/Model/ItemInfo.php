<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemInfo extends Model
{
    protected $table = 'item_info';
    protected $fillable = [
        'admin_id', 'item_name','price','picture',"description","state","code","invalid_time","num"
    ];
}
