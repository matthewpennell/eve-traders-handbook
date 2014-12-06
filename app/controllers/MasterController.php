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
			$filter_url .= '&meta[]=' . implode('&meta[]=', $active_meta_filters);
		}

		// Check whether there are any filters on the existence of blueprints.
		$active_blueprint_filters = Input::get('blueprint');

		if (count($active_blueprint_filters))
		{
			// Loop through all active filters and construct the aggregate query.
			foreach ($active_blueprint_filters as $active_blueprint_filter)
			{
				$value = ($active_blueprint_filter == 'Yes') ? 1 : 0;
				$blueprint_filter_raw[] = 'allowManufacture = ' . $value;
			}

			// Bundle up all the filters.
			$whereraw[] = implode(' or ', $blueprint_filter_raw);

			// Make a URL to use in links.
			$filter_url .= '&blueprint[]=' . implode('&blueprint[]=', $active_blueprint_filters);
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
			$filter_url .= '&filter[]=' . implode('&filter[]=', $active_filters);
		}

		// Query the database for the chosen items.
		$items = Item::selectedItems($page, $whereraw);

		// Load the template to display all the items.
		return View::make('home')
			->with('items', $items)
			->with('page', $page)
			->with('filter_url', $filter_url)
			->with('pages', Item::getRowCount($whereraw) / 20)
			->nest('sidebar', 'filters', array(
				'filters'					=> Filter::all()->sortBy('categoryName'),
				'meta_filters'				=> array('Meta 0', 'Meta 1', 'Meta 2', 'Meta 3', 'Meta 4', 'Meta 5', 'Tech II'),
				'ships'						=> Ship::all()->sortBy('shipName'),
				'active_filters'			=> $active_filters,
				'active_meta_filters'		=> $active_meta_filters,
				'active_blueprint_filters'	=> $active_blueprint_filters,
			));

	}

}
