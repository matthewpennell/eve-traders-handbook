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

		if (Input::get('me'))
		{
			$material_efficiency->materialEfficiency = (int) Input::get('me');
		}

		// Save the updated material efficiency figure.
		$type->materialEfficiency()->save($material_efficiency);

		// Load the 64x64 icon to display.
		$icon = '';
		$ship = Ship::where('id', $id)->get();
		if (count($ship))
		{
			$icon = 'https://image.eveonline.com/Render/' . $id . '_128.png';
		}
		elseif ($type->Group->Icon)
		{
			$icon = str_replace('_', '_64_', $type->MarketGroup->Icon->iconFile);
			$icon = preg_replace('/^0/', '', $icon);
			$icon = preg_replace('/0(.)$/', '$1', $icon);
			$icon = '/eve/items/' . $icon . '.png';
		}

		// Retrieve the current price ranges this item sells for.
		$response = API::eveCentral(array($id), 10000014);
		$local_price = $response[$id];

		// Tech II items need to be treated differently.
		if ($type->metaType && $type->metaType->metaGroup && $type->metaType->metaGroup['metaGroupName'] == 'Tech II')
		{

			// Retrieve an array of different possibilities for decryptors.
			$tech_two = TechII::getInventionFigures($type);

			// For each decryptor, show the potential profit.
			$potential_profits = array();
			$total_price = -1000000000;

			foreach ($tech_two as $decryptor)
			{

				$max_runs = 10;
				if (isset($decryptor['max_run_modifier']))
				{
					$max_runs += $decryptor['max_run_modifier'];
				}
				$chance_of_success = $decryptor['chance_of_success'] / 100;
				$invention_cost = $decryptor['invention_price'];
				$manufacturing_cost_per_blueprint = $max_runs * $decryptor['t2_manufacture_price']; // TODO: use material efficiency modifier
				$income_per_blueprint = $local_price->median * $max_runs;
				$profit_per_blueprint = $income_per_blueprint - $manufacturing_cost_per_blueprint;
				$overall_profit = ($profit_per_blueprint * $chance_of_success) - $invention_cost;

				$potential_profits[] = array(
					"typeName"	=> $decryptor['typeName'],
					"profit"	=> $overall_profit,
				);

				if ($overall_profit > $total_price)
				{
					$total_price = $overall_profit;
				}

			}

			$manufacturing = NULL;

		}
		else
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
			$api = API::eveCentral($types, 10000014); // TODO: this should be controlled in app settings

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
				$api = API::eveCentral($jita_types, NULL, 30000142);
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

		// Retrieve current prices of the module in notable trade hubs.
		$jita = API::eveCentral($id, NULL, 30000142);

		$prices[] = (object) array(
			"solarSystemName"	=> "Jita",
			"median"			=> $jita[$id]->median,
		);

		$amarr = API::eveCentral($id, NULL, 30002187);

		$prices[] = (object) array(
			"solarSystemName"	=> "Amarr",
			"median"			=> $amarr[$id]->median,
		);

		// Cache the industry and import potential profits for the item.
		$profit = $type->profit;
		if (!isset($profit))
		{
			$profit = new Profit;
			$profit->typeID = $id;
		}
		$profit->manufactureCost = $total_price;
		$profit->profitIndustry = $local_price->median - $total_price;
		$profit->profitImport = $local_price->median - $jita[$id]->median;

		// Save the cached potential profit figure.
		$type->profit()->save($profit);

		$profitToUse = ($profit->profitIndustry > $profit->profitImport) ? $profit->profitIndustry : $profit->profitImport;
		$costToUse = ($profit->profitIndustry > $profit->profitImport) ? $total_price : $jita[$id]->median;

		return View::make('item')
			->with('type', $type)
			->with('icon', $icon)
			->with('local_price', $local_price)
			->with('prices', $prices)
			->with('manufacturing', $manufacturing)
			->with('t2_options', $potential_profits)
			->with('total_price', $total_price)
			->with('profit', $profit)
			->with('profitToUse', $profitToUse)
			->with('costToUse', $costToUse)
			->with('material_efficiency', $material_efficiency);

	}

}
