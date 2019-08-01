<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventRelease extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_release', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_name',30);
            $table->dateTime('event_time');
            $table->date('sign_up_time');
            $table->string('event_place',50)->nullable();
            $table->integer('sharer_id');
            $table->text('picture');
            $table->string('link',150);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->string('longitude',10)->nullable();
            $table->string('latitude',10)->nullable();
            $table->integer('hot')->default(0);
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('state');
            $table->integer('max_num');
            $table->integer('price')->default(0);

            $table->index('event_name');
            $table->index('sharer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('event_release');
    }
}
