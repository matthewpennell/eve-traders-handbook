<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
	    Schema::create('settings', function($table)
	    {
	        $table->increments('id');
	        $table->string('key')->unique();
	        $table->string('value');
	        $table->timestamps();
	    });
	}

	public function down()
	{
	    Schema::drop('settings');
	}
}
