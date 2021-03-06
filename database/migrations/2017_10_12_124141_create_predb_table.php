<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePredbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('predb', function (Blueprint $table) {
            $table->increments('id')->comment('Primary key');
            $table->string('title')->default('\'\'')->unique('ix_predb_title');
            $table->string('nfo')->nullable()->default(null)->index('ix_predb_nfo');
            $table->string('size', 50)->nullable()->default(null);
            $table->string('category')->nullable()->default(null);
            $table->dateTime('predate')->nullable()->default(null)->index('ix_predb_predate');
            $table->string('source', 50)->default('\'\'')->index('ix_predb_source');
            $table->integer('requestid')->unsigned()->default(0);
            $table->integer('groups_id')->unsigned()->default(0)->comment('FK to groups');
            $table->boolean('nuked')->default(0)->comment('Is this pre nuked? 0 no 2 yes 1 un nuked 3 mod nuked');
            $table->string('nukereason')->nullable()->default(null)->comment('If this pre is nuked, what is the reason?');
            $table->string('files', 50)->nullable()->default(null)->comment('How many files does this pre have ?');
            $table->string('filename')->default('\'\'')->index('ft_predb_filename');
            $table->boolean('searched')->default(0)->index('ix_predb_searched');
            $table->index(['requestid', 'groups_id'], 'ix_predb_requestid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('predb');
    }
}
