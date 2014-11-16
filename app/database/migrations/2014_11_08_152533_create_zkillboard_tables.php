<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZkillboardTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create basic zkillboard table to hold all kill information.
		Schema::create('kills', function ($table)
		{
			$table->integer('killID');
			$table->integer('solarSystemID');
			$table->integer('characterID');
			$table->string('characterName');
			$table->integer('corporationID');
			$table->integer('allianceID');
			$table->integer('shipTypeID');
			$table->dateTime('killTime');
			$table->timestamps();
		});

		// Table of solar system names.
		Schema::create('systems', function ($table)
		{
			$table->integer('id');
			$table->string('solarSystemName');
			$table->timestamps();
		});

		// Table of corporation names.
		Schema::create('corporations', function ($table)
		{
			$table->integer('id');
			$table->string('corporationName');
			$table->timestamps();
		});

		// Table of alliance names.
		Schema::create('alliances', function ($table)
		{
			$table->integer('id');
			$table->string('allianceName');
			$table->timestamps();
		});

		// Table of items lost or dropped per kill.
		Schema::create('items', function ($table)
		{
			$table->increments('id');
			$table->integer('killID');
			$table->integer('typeID');
			$table->integer('qty');
			$table->timestamps();
		});

		// Table of items.
		Schema::create('types', function ($table)
		{
			$table->integer('id');
			$table->string('typeName');
			$table->timestamps();
		});

		// Table of ship names.
		Schema::create('ships', function ($table)
		{
			$table->integer('id');
			$table->string('shipName');
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
		// Drop all tables.
		Schema::drop('kills');
		Schema::drop('systems');
		Schema::drop('corporations');
		Schema::drop('alliances');
		Schema::drop('items');
		Schema::drop('types');
		Schema::drop('ships');
	}

}
