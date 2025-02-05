<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedemptionVoucher extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redemption_voucher', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->dateTime('created_at');
            $table->dateTime('expired_time');
            $table->dateTime('use_time')->nullable();
            $table->tinyInteger('state')->default(0);

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redemption_voucher');
    }
}
