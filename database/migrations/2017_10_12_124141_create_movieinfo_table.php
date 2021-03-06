<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovieinfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movieinfo', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('imdbid')->unsigned()->unique('ix_movieinfo_imdbid');
            $table->integer('tmdbid')->unsigned()->default(0);
            $table->string('title')->default('\'\'')->index('ix_movieinfo_title');
            $table->string('tagline', 1024)->default('\'\'');
            $table->string('rating', 4)->default('\'\'');
            $table->string('plot', 1024)->default('\'\'');
            $table->string('year', 4)->default('\'\'');
            $table->string('genre', 64)->default('\'\'');
            $table->string('type', 32)->default('\'\'');
            $table->string('director', 64)->default('\'\'');
            $table->string('actors', 2000)->default('\'\'');
            $table->string('language', 64)->default('\'\'');
            $table->boolean('cover')->default(0);
            $table->boolean('backdrop')->default(0);
            $table->timestamps();
            $table->string('trailer')->default('\'\'');
            $table->string('rtrating', 10)->default('\'\'')->comment('RottenTomatoes rating score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('movieinfo');
    }
}
