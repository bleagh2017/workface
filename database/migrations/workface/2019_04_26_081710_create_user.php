<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */    
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('wx_name',20);
            $table->string('phone',50)->nullable();
            $table->string('wx_openid',100)->unique();
            $table->string('wx_unionid',100)->unique();
            $table->string('form_id',100)->nullable();
            $table->tinyInteger('sex');
            $table->text('head');
            $table->dateTime('created_at');
            $table->tinyInteger("state")->default(1);
            $table->integer("awesome_num")->default(0);
            $table->integer("redemption_num")->default(0);
            $table->string("api_token");
            $table->tinyInteger("user_type")->default(0);
            $table->dateTime("updated_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
