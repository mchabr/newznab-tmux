<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpcomingReleasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upcoming_releases', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('source', 20);
            $table->integer('typeid');
            $table->text('info', 65535)->nullable()->default(null);
            $table->dateTime('updateddate')->default('current_timestamp()');
            $table->unique(['source', 'typeid'], 'ix_upcoming_source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('upcoming_releases');
    }
}
