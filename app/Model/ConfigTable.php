<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ConfigTable extends Model
{
	protected $table = "config_table";

	protected $fillable = [
		'config_name', 'value'
	];
}
