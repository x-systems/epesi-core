<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoreTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	$this->down();
    	
    	Schema::create('variables', function (Blueprint $table) {
            $table->string('name', 128)->primary();
            $table->text('value');
        });
    	
    	Schema::create('modules', function (Blueprint $table) {
            $table->string('class', 512);
            $table->string('alias', 128)->unique();
            $table->smallInteger('priority')->default(0);
            $table->smallInteger('state')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	Schema::dropIfExists('variables');
    	
    	Schema::dropIfExists('modules');
    }
}
