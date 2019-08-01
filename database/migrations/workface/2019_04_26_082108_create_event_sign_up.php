<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventSignUp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_sign_up', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->integer('user_id'); 
            $table->text('sign_detail')->nullable();
            $table->tinyInteger('result');
            $table->text('result_reason')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->string('channel',20);

            $table->index('event_id');
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
        Schema::drop('event_sign_up');
    }
}
