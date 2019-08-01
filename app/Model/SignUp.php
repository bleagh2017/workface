<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class SignUp extends Model
{
    protected $table = 'event_sign_up';
    protected $fillable = [
        'event_id', 'user_id','head','content',"result","result_reason","channel"
    ];

    public function user(){
        return $this->belongsTo('App\Model\User',"user_id");
    }
    public function event(){
        return $this->belongsTo('App\Model\Event',"event_id");
    }
}
