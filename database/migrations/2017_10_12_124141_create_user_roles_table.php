<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 32);
            $table->integer('apirequests')->unsigned();
            $table->integer('downloadrequests')->unsigned();
            $table->integer('defaultinvites')->unsigned();
            $table->boolean('isdefault')->default(0);
            $table->boolean('canpreview')->default(0);
            $table->boolean('hideads')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_roles');
    }
}
