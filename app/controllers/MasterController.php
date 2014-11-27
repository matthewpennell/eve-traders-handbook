<?php

class MasterController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Controller
	|--------------------------------------------------------------------------
	|
	| TODO: Write comments here.
	|
	*/

	public function home()
	{

		// Get the page requested.
		$page = Input::get('page');
		if (!isset($page))
		{
			$page = 1;
		}

		// Check whether any filters are active.
		$active_filters = Input::get('filter');
		$whereraw = array();
		$filter_url = '';

		// If no filters are set, apply the default ones.
		if (!isset($active_filters))
		{
			$active_filters = array();
			$default_filters = Filter::where('is_default', 1)->get();
			foreach ($default_filters as $default_filter)
			{
				array_push($active_filters, $default_filter->categoryName);
			}
		}

		// Check whether a filter on tech/meta level has been applied.
		$active_meta_filters = Input::get('meta');
		if (count($active_meta_filters))
		{
			// Loop through all active filters and construct the aggregate query.
			foreach ($active_meta_filters as $active_meta_filter)
			{
				$meta_filter_raw[] = 'metaGroupName = "' . $active_meta_filter . '"';
			}

			// Bundle up all the filters.
			$whereraw[] = implode(' or ', $meta_filter_raw);

			// Make a URL to use in links.
			$filter_url .= 'meta[]=' . implode('&meta[]=', $active_meta_filters);
		}

		if (count($active_filters))
		{
			// Loop through all active filters and construct the aggregate query.
			foreach ($active_filters as $active_filter)
			{
				$active_filter_raw[] = 'categoryName = "' . $active_filter . '"';
			}

			// Bundle up all the filters.
			$whereraw[] = implode(' or ', $active_filter_raw);

			// Make a URL to use in links.
			$filter_url .= 'filter[]=' . implode('&filter[]=', $active_filters);
		}

		// Query the database for the chosen items.
		if (count($whereraw) > 0)
		{
			$items = Item::whereRaw('(' . implode(') and (', $whereraw) . ')')->get();
		}
		else
		{
			$items = Item::all();
		}

		// Loop through them all to combine the same items.
		$table = array();
		$simple_array = array(); // keep track of which items have already been counted

		foreach ($items as $item)
		{
			if (in_array($item->typeID, $simple_array))
			{
				// This item is already in the table.
				$table[$item->typeID]->qty += $item->qty;
			}
			else
			{
				$table[$item->typeID] = (object) array(
					"qty"				=> $item->qty,
					"typeID"			=> $item->typeID,
					"typeName"			=> $item->typeName,
					"category"			=> $item->categoryName,
					"meta"				=> $item->metaGroupName,
					"profitIndustry"	=> $item->type->profit['profitIndustry'],
					"profitImport"		=> $item->type->profit['profitImport'],
					"profitOrLoss"		=> ($item->type->profit['profitIndustry'] > 0) ? 'profit' : 'loss',
				);
				$simple_array[] = $item->typeID;
			}
		}

		// Sort the list of items by quantity.
		usort($table, function ($a, $b)
		{
			if ($a->qty == $b->qty) {
				return 0;
			}
			return ($a->qty > $b->qty) ? -1 : 1;
		});

		// Load the template to display all the items.
		return View::make('home')
			->with('items', array_slice($table, ($page - 1) * 20, 20))
			->with('page', $page)
			->with('filter_url', $filter_url)
			->with('pages', count($table) / 20)
			->nest('sidebar', 'filters', array(
				'filters'				=> Filter::all()->sortBy('categoryName'),
				'meta_filters'			=> array('Meta 0', 'Meta 1', 'Meta 2', 'Meta 3', 'Meta 4', 'Meta 5', 'Tech II'),
				'ships'					=> Ship::all()->sortBy('shipName'),
				'active_filters'		=> $active_filters,
				'active_meta_filters'	=> $active_meta_filters,
			));

	}

}
