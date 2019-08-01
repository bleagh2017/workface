<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $table = "check_in";

	public $timestamps = false;
	protected $fillable = [
		'user_id', 'event_id','check_time','if_sign','if_ontime'
	];
}
