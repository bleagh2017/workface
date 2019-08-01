<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_id');
            $table->string('item_name',20);
            $table->integer('price');
            $table->text('picture');
            $table->text('description');
            $table->integer('num');
            $table->dateTime('created_at');
            $table->date('invalid_time');
            $table->tinyInteger('state');
            $table->dateTime('updated_at');
            $table->text('code');

            $table->index('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('item_info');
    }
}
