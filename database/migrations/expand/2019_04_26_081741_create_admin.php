<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('admin_name',20);
            $table->integer('admin_role');
            $table->string('head',150)->nullable();
            $table->string('phone',50);
            $table->string('password',255);
            $table->string('remember_token',100)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('login_date')->nullable();
            $table->tinyInteger("state");

            $table->index('admin_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('admins');
    }
}
