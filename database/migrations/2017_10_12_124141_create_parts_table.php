<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->bigInteger('binaries_id')->unsigned()->default(0);
            $table->string('messageid')->default('\'\'');
            $table->bigInteger('number')->unsigned()->default(0);
            $table->integer('partnumber')->unsigned()->default(0);
            $table->integer('size')->unsigned()->default(0);
            $table->primary(['binaries_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('parts');
    }
}
