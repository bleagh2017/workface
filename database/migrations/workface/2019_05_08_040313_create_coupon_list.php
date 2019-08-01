<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_list', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('shop_id');
            $table->integer('item_id');
            $table->dateTime('created_at');
            $table->dateTime('expired_time');
            $table->dateTime('use_time')->nullable();
            $table->string('code')->nullable();
            $table->integer('price');
            $table->tinyInteger('state');

            $table->index('user_id');
            $table->index('shop_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
