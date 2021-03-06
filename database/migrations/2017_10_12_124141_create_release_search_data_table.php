<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReleaseSearchDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('release_search_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('releases_id')->unsigned()->index('ix_releasesearch_releases_id')->comment('FK to releases.id');
            $table->string('guid', 50)->index('ix_releasesearch_guid');
            $table->string('name')->default('\'\'')->index('ix_releasesearch_name_ft');
            $table->string('searchname')->default('\'\'')->index('ix_releasesearch_searchname_ft');
            $table->string('fromname')->nullable()->default(null)->index('ix_releasesearch_fromname_ft');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('release_search_data');
    }
}
