<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckIn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_in', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('event_id');
            $table->dateTime('check_time');
            $table->tinyInteger('if_sign');
            $table->tinyInteger('if_ontime');
            $table->string('longitude',10)->nullable();
            $table->string('latitude',10)->nullable(); 

            $table->index('user_id');
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('check_in');
    }
}
