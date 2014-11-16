<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('items', function ($table)
		{
			$table->string('typeName');
    		$table->string('categoryName');
			$table->string('metaGroupName');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('items', function ($table)
		{
    		$table->dropColumn(array('typeName', 'categoryName', 'metaGroupName'));
		});
	}

}
