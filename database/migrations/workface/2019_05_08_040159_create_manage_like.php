<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageLike extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manage_like', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('event_id');
            $table->dateTime('created_at');
            $table->integer('num');
            $table->tinyInteger('state');

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
        Schema::drop('manage_like');
    }
}
