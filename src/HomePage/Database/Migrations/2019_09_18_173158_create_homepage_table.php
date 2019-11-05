<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	$this->down();
    	
    	Schema::create('home_pages', function (Blueprint $table) {
    		$table->increments('id');
            $table->string('path', 64);
            $table->string('role', 64);
            $table->smallInteger('priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	Schema::dropIfExists('home_pages'); 	
    }
}
