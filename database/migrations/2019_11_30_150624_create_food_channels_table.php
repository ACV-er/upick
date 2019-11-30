<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('food_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("editor")->comment("最后编辑者");
            $table->string("title");
            $table->string("url");
            $table->boolean("top");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('food_channels');
    }
}
