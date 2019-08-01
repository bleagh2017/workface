<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RedemptionVoucher extends Model
{
	protected $table = 'redemption_voucher';
	const UPDATED_AT = null;
	protected $fillable = [
		'user_id', 'expired_time'
	];
}
