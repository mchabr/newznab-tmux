<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePredbHashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('predb_hashes', function (Blueprint $table) {
            $table->integer('predb_id')->default(0)->comment('id, of the predb entry, this hash belongs to');
            $table->binary('hash', 40)->default('\'\'')->primary();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('predb_hashes');
    }
}
