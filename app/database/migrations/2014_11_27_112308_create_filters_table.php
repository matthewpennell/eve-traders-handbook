<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiltersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('filters', function($table)
		{
			$table->integer('categoryID')->primary();
			$table->string('categoryName');
			$table->integer('iconID');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('filters');
	}

}
