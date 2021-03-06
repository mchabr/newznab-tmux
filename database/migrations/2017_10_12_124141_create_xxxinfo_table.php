<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXxxinfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xxxinfo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 1024)->unique('ix_xxxinfo_title');
            $table->string('tagline', 1024);
            $table->binary('plot', 65535)->nullable()->default(null);
            $table->string('genre');
            $table->string('director')->nullable()->default(null);
            $table->string('actors', 2500);
            $table->text('extras', 65535)->nullable()->default(null);
            $table->text('productinfo', 65535)->nullable()->default(null);
            $table->text('trailers', 65535)->nullable()->default(null);
            $table->string('directurl', 2000);
            $table->string('classused', 20)->default('\'\'');
            $table->boolean('cover')->default(0);
            $table->boolean('backdrop')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('xxxinfo');
    }
}
