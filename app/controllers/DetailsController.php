<?php

use ETH\API;
use ETH\TechII;

class DetailsController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Details Controller
	|--------------------------------------------------------------------------
	|
	| Displays information about a specific item (Type), including
	| potential profit for importing or manufacturing.
	|
	*/

	/**
	 * Display detailed information about a specific item, including the icon,
	 * description, price to buy in trade hubs and locally, and manufacturing costs
	 * based on mineral prices in the local area.
	 */
	public function item($id = NULL)
	{

		// Retrieve the basic information about this item.
		$type = Type::where('typeID', $id)->firstOrFail();

		// If material efficiency was updated, save to the database and set a local variable.
		$material_efficiency = $type->materialEfficiency;

		if (!isset($material_efficiency))
		{
			$material_efficiency = new MaterialEfficiency;
			$material_efficiency->typeID = $id;
			$material_efficiency->materialEfficiency = 0;
		}

		if (Input::has('me'))
		{
			$material_efficiency->materialEfficiency = (int) Input::get('me');
		}

		// Save the updated material efficiency figure.
		$type->materialEfficiency()->save($material_efficiency);

		// Calculate volume if we need to use shipping data.
		$shipping_cost = Setting::where('key', 'shipping_cost')->first();
		$shipping_cost_to_include = 0;
		if ($shipping_cost->value > 0)
		{
			// If volume is below 1000, we assume it's not a ship hull and use the base volume.
			if ($type->volume < 1000)
			{
				$shipping_cost_to_include = $shipping_cost->value * $type->volume;
			}
			else
			{
				// For larger volumes, we need to use static values for ship types.
				switch ($type->groupID) {
					case 31:
						$shipping_cost_to_include = $shipping_cost->value * 500;
						break;
					case 25:
					case 237:
					case 324:
					case 830:
					case 831:
					case 834:
					case 893:
						$shipping_cost_to_include = $shipping_cost->value * 2500;
						break;
					case 463:
					case 543:
						$shipping_cost_to_include = $shipping_cost->value * 3750;
						break;
					case 420:
					case 541:
						$shipping_cost_to_include = $shipping_cost->value * 5000;
						break;
					case 26:
					case 358:
					case 832:
					case 833:
					case 894:
						$shipping_cost_to_include = $shipping_cost->value * 10000;
						break;
					case 28:
						$shipping_cost_to_include = $shipping_cost->value * 20000;
						break;
					case 27:
					case 381:
						$shipping_cost_to_include = $shipping_cost->value * 50000;
						break;
				}
			}
		}

		// Load the 64x64 icon to display.
		$icon = '';
		$ship = Ship::where('id', $id)->get();
		if (count($ship))
		{
			$icon = 'https://image.eveonline.com/Render/' . $id . '_128.png';
		}
		elseif ($type->Group->Icon)
		{
			$icon = 'https://image.eveonline.com/Type/' . $id . '_64.png';
		}

		// Retrieve the current price ranges this item sells for.
		$response = API::CREST(array($id), Setting::where('key', 'home_region_id')->pluck('value'));
		$local_price = $response[$id];

		// Tech II items need to be treated differently.
		$t2_options = array();
		if ($type->metaType && $type->metaType->metaGroup && $type->metaType->metaGroup['metaGroupName'] == 'Tech II')
		{

			// Retrieve an array of different possibilities for decryptors.
			$tech_two = TechII::getInventionFigures($type);

			// For each decryptor, show the potential profit.
			$total_price = 1000000000;

			foreach ($tech_two as $decryptor)
			{
				// Default max runs is 10, add any modifier.
				$max_runs = 10;
				if (isset($decryptor['max_run_modifier']))
				{
					$max_runs += $decryptor['max_run_modifier'];
				}

				// Based on the chance of success, how many T2 items on average will be produced per run?
				$chance_of_success = $decryptor['chance_of_success'] / 100;
				$t2_items_per_blueprint = $max_runs * $chance_of_success;

				// Calculate the cost of manufacturing that many T2 items.
				$manufacturing_cost_per_blueprint = $t2_items_per_blueprint * ($decryptor['t2_manufacture_price'] * (100 - $decryptor['me_modifier']) / 100);
				$total_cost = $decryptor['invention_price'] + $manufacturing_cost_per_blueprint;
				$cost_per_unit = $total_cost / $t2_items_per_blueprint;

				$t2_options[] = array(
					"typeName"	=> $decryptor['typeName'],
					"cost"		=> $cost_per_unit,
				);

				if ($cost_per_unit < $total_price)
				{
					$total_price = $cost_per_unit;
				}

			}

			$manufacturing = NULL;

		}
		else
		{

			// If the meta level of this item is not Meta 0, we don't need to pull the manufacture price.
			$item = Item::where('typeID', $id)->first();
			if ($item->allowManufacture == 1)
			{

				// Get a list of what is needed to manufacture the item.
				$manufacturing = array();
				$total_price = 0;
				$type_materials = DB::table('invTypeMaterials')->where('typeID', $id)->get();
				$types = array();
				$jita_types = array();

				// Loop through all the materials. For each one, add it to an array we will use to show manufacturing details.
				foreach ($type_materials as $material)
				{
					// Build an array for the eve-central API call.
					$types[] = $material->materialTypeID;
					// Build an array for the eventual output.
					$manufacturing[$material->materialTypeID] = (object) array(
						"typeName"	=> Type::find($material->materialTypeID)->typeName,
						"qty"		=> $material->quantity * (1 - ($material_efficiency->materialEfficiency / 100)),
						"price"		=> 0,
						"jita"		=> FALSE,
					);
				}

				// Make an API call to get the local price of materials.
				$api = API::CREST($types, $home_region_id = Setting::where('key', 'home_region_id')->pluck('value'));

				// Loop through each returned price and update the data in the manufacturing array.
				foreach($api as $api_result)
				{
					if ($api_result->median != 0)
					{
						$manufacturing[$api_result->id]->price = $manufacturing[$api_result->id]->qty * $api_result->median;
						$total_price += $manufacturing[$api_result->id]->price;
					}
					else
					{
						// Build an array of types to check prices at Jita.
						$jita_types[] = $api_result->id;
					}
				}

				// If we need to check prices at Jita, make another API call.
				if (count($jita_types))
				{
					$api = API::CREST($jita_types, NULL, 30000142);
					// Loop through each returned price and update the data in the manufacturing array.
					foreach($api as $api_result)
					{
						$manufacturing[$api_result->id]->price = $manufacturing[$api_result->id]->qty * $api_result->median;
						$manufacturing[$api_result->id]->jita = TRUE;
						$total_price += $manufacturing[$api_result->id]->price;
					}
				}

				$potential_profits = NULL;

			}
			else
			{
				$manufacturing = NULL;
				$total_price = 999999999;
			}

		}

		// Retrieve current prices of the module in notable trade hubs.
		$jita = API::CREST($id, NULL, 30000142);

		$prices[] = (object) array(
			"solarSystemName"	=> "Jita",
			"median"			=> $jita[$id]->median,
		);

		$amarr = API::CREST($id, NULL, 30002187);

		$prices[] = (object) array(
			"solarSystemName"	=> "Amarr",
			"median"			=> $amarr[$id]->median,
		);

		$importCostToUse = ((int)$jita[$id]->median < (int)$amarr[$id]->median) ? $jita[$id]->median : $amarr[$id]->median;

		if ($shipping_cost_to_include)
		{
			$importCostToUse = $importCostToUse + $shipping_cost_to_include;
		}

		// Cache the industry and import potential profits for the item.
		$profit = $type->profit;
		if (!isset($profit))
		{
			$profit = new Profit;
			$profit->typeID = $id;
		}
		$profit->manufactureCost = round($total_price);
		$profit->profitIndustry = round($local_price->median - $total_price);
		$profit->profitImport = round($local_price->median - $importCostToUse);

		// Save the cached potential profit figure.
		$type->profit()->save($profit);

		$profitToUse = ($profit->profitIndustry > $profit->profitImport) ? $profit->profitIndustry : $profit->profitImport;
		$costToUse = ($profit->profitIndustry > $profit->profitImport) ? $total_price : $importCostToUse;

		return View::make('item')
			->with('type', $type)
			->with('icon', $icon)
			->with('local_price', $local_price)
			->with('prices', $prices)
			->with('shipping_cost', $shipping_cost_to_include)
			->with('manufacturing', $manufacturing)
			->with('t2_options', $t2_options)
			->with('total_price', $total_price)
			->with('profit', $profit)
			->with('profitToUse', $profitToUse)
			->with('costToUse', $costToUse)
			->with('material_efficiency', $material_efficiency);

	}

}
