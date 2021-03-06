<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_movies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_id')->unsigned();
            $table->integer('imdbid')->unsigned()->nullable()->default(null);
            $table->string('categories', 64)->nullable()->default(null)->comment('List of categories for user movies');
            $table->timestamps();
            $table->index(['users_id', 'imdbid'], 'ix_usermovies_userid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_movies');
    }
}
