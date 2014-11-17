<?php

use Httpful\Request;

class DetailsController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Details Controller
	|--------------------------------------------------------------------------
	|
	| TODO: Write comments here.
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
		$icon = str_replace('_', '_64_', $type->Group->Icon->iconFile);
		$icon = preg_replace('/^0/', '', $icon);

		// Get a list of what is needed to manufacture the item.
		$manufacturing = array();
		

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

		return View::make('item')->with('type', $type)->with('icon', $icon)->with('prices', $prices)->with('manufacturing', $manufacturing);

	}

}
