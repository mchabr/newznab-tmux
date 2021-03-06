<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('section', 25)->default('\'\'');
            $table->string('subsection', 25)->default('\'\'');
            $table->string('name', 25)->default('\'\'');
            $table->string('value', 1000)->default('\'\'');
            $table->text('hint', 65535);
            $table->string('setting', 64)->default('\'\'')->unique('ui_settings_setting');
            $table->primary(['section', 'subsection', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('settings');
    }
}
