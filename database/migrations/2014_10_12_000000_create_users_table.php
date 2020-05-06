<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nickname');
            $table->string('stu_id', 20)->unique();
            $table->string('password', 40)->comment("密码md5");
            $table->json('collection');
            $table->json('publish');
            $table->string("remember")->unique();
            $table->dataTime("created_at")->date('Y-m-d H:i:s',time());
            $table->dataTime("updated_at")->date('Y-m-d H:i:s',time());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
