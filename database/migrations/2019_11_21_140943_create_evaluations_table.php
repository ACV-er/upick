<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('publisher')->comment("发布者id");
            $table->json("tag")->comment("tag_id数组");
            $table->integer("views")->comment("浏览量");
            $table->integer("collections")->comment("被收藏次数");
            $table->integer("like")->comment("赞数");
            $table->integer("unlike")->comment("踩数");
            $table->json("img")->comment("数组");
            $table->string("title");
            $table->string("content");
            $table->string("location");
            $table->string("shop_name")->comment("店名");
            $table->string("nickname")->comment("昵称");
            $table->double("score")->index()->default(0)->comment("排序分值");
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
        Schema::dropIfExists('evaluations');
    }
}
