<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	$this->down();
    	
    	Schema::create('user_settings', function (Blueprint $table) {
    		$table->increments('id');
    		$table->unsignedInteger('user_id');
            $table->string('group', 512);
            $table->string('name', 128);
            $table->text('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	Schema::dropIfExists('user_settings');
    }
}
