<?php

use Httpful\Request;
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
		$url = 'http://api.eve-central.com/api/marketstat'
			. '?'
			. 'typeid=' . $id
			. '&'
			. 'regionlimit=10000014'; // TODO: Parameterise this

		$response = Request::get($url)->send();
		$xml = simplexml_load_string($response->body);
		$local_price = (object) array(
			"volume"	=> $xml->marketstat->type->sell->volume,
			"avg"		=> $xml->marketstat->type->sell->avg,
			"max"		=> $xml->marketstat->type->sell->max,
			"min"		=> $xml->marketstat->type->sell->min,
			"median"	=> $xml->marketstat->type->sell->median,
		);

		// Tech II items need to be treated differently.
		if ($type->metaType->metaGroup['metaGroupName'] == 'Tech II')
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
			foreach ($type_materials as $material)
			{
				// For each manufacturing item, make an API call to get the local price of materials.
				// TODO: Change this to use stored home region ID and to cache the result.
				$url = 'http://api.eve-central.com/api/marketstat'
				. '?'
				. 'typeid=' . $material->materialTypeID
				. '&'
				. 'regionlimit=10000014';

				$response = Request::get($url)->send();
				$xml = simplexml_load_string($response->body);
				$price_per_unit = $xml->marketstat->type->sell->median;
				$jita_price = FALSE;

				// If the price returned is zero for the selected region, do another check at Jita prices.
				if ($price_per_unit == 0)
				{
					$url = 'http://api.eve-central.com/api/marketstat'
					. '?'
					. 'typeid=' . $material->materialTypeID
					. '&'
					. 'usesystem=30000142';

					$response = Request::get($url)->send();
					$jita = simplexml_load_string($response->body);
					$price_per_unit = $jita->marketstat->type->sell->median;
					$jita_price = TRUE;
				}

				$manufacturing[] = (object) array(
					"typeName"	=> Type::find($material->materialTypeID)->typeName,
					"qty"		=> $material->quantity,
					"price"		=> $material->quantity * $price_per_unit,
					"jita"		=> $jita_price,
				);
				$total_price += $material->quantity * $price_per_unit;
			}

			$potential_profits = NULL;

		}

		// Make an API call to eve-central for the price at Jita.
		$url = 'http://api.eve-central.com/api/marketstat'
		. '?'
		. 'typeid=' . $id
		. '&'
		. 'usesystem=30000142';

		$response = Request::get($url)->send();

		$jita = simplexml_load_string($response->body);

		$prices[] = (object) array(
			"solarSystemName"	=> "Jita",
			"median"			=> $jita->marketstat->type->sell->median,
		);

		// Make an API call to eve-central for the price at Amarr.
		$url = 'http://api.eve-central.com/api/marketstat'
		. '?'
		. 'typeid=' . $id
		. '&'
		. 'usesystem=30002187';

		$response = Request::get($url)->send();

		$amarr = simplexml_load_string($response->body);

		$prices[] = (object) array(
			"solarSystemName"	=> "Amarr",
			"median"			=> $amarr->marketstat->type->sell->median,
		);

		// Cache the industry and import potential profits for the item.
		$profit = $type->profit;
		if (!isset($profit))
		{
			$profit = new Profit;
			$profit->typeID = $id;
		}
		$profit->profitIndustry = $local_price->median - $total_price;
		$profit->profitImport = $local_price->median - $jita->marketstat->type->sell->median;

		// Save the cached potential profit figure.
		$type->profit()->save($profit);

		$profitToUse = ($profit->profitIndustry > $profit->profitImport) ? $profit->profitIndustry : $profit->profitImport;

		return View::make('item')
			->with('type', $type)
			->with('icon', $icon)
			->with('local_price', $local_price)
			->with('prices', $prices)
			->with('manufacturing', $manufacturing)
			->with('t2_options', $potential_profits)
			->with('total_price', $total_price)
			->with('profit', $profit)
			->with('profitToUse', $profitToUse);

	}

}
