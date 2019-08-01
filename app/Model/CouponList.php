<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CouponList extends Model
{
    protected $table = "coupon_list";

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'shop_id','item_id','expired_time','use_time','price','state'
    ];

    public function itemInfo(){
        return $this->belongsTo('App\Model\ItemInfo',"item_id");
    }
}
