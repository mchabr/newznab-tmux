<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReleaseSubtitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('release_subtitles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('releases_id')->unsigned()->comment('FK to releases.id');
            $table->integer('subsid')->unsigned();
            $table->string('subslanguage', 50);
            $table->unique(['releases_id', 'subsid'], 'ix_releasesubs_releases_id_subsid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('release_subtitles');
    }
}
