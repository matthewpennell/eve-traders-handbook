<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfitTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profits', function($table)
		{
			$table->integer('typeID')->primary();
			$table->float('profitIndustry');
			$table->float('profitImport');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('profits');
	}

}
