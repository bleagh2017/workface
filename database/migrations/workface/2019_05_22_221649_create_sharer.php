<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSharer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sharer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sharer_name',20);
            $table->tinyInteger('sex');
            $table->text('head');
            $table->text('brand_logo')->nullable();
            $table->string('brand_name',20)->nullable();
            $table->text('content');
            $table->integer('hot')->default(0);
            $table->string('title',200);
            $table->dateTime('created_at');
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
        Schema::drop('sharer');
    }
}
