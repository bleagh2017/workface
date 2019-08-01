<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role_name',10);
            $table->string('role_power',100);
            $table->tinyInteger('role_state');
            $table->dateTime('create_date');
            $table->dateTime('update_date');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('admin_role');
    }
}
