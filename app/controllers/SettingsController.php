<?php

use ETH\API;

class SettingsController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Settings Controller
    |--------------------------------------------------------------------------
    |
    | Where the user can maintain their API key.
    |
    */

    public function __construct()
    {

        // Check for a valid CSRF token on POST requests.
        $this->beforeFilter('csrf', array('on' => 'post'));

    }

    public function getIndex()
    {

        // Retrieve all of the stored settings from the database.
        $api_key_id = Setting::where('key', 'api_key_id')->first();
        $api_key_verification_code = Setting::where('key', 'api_key_verification_code')->first();
        $api_key_character_id = Setting::where('key', 'api_key_character_id')->first();
        $home_region_id = Setting::where('key', 'home_region_id')->first();
        $home_region_name = Region::where('regionID', $home_region_id->value)->pluck('regionName');
        $shipping_cost = Setting::where('key', 'shipping_cost')->first();

        $characters = array();
        // If the API key is set, retrieve a list of characters.
        if ($api_key_id->value != '' && $api_key_verification_code->value != '')
        {
            $response = API::eveOnline('account/Characters');
            if ($response->body->result)
            {
                foreach ($response->body->result->rowset->row as $row)
                {
                    $characters[(string)$row['characterID']] = $row['name'];
                }
            }
        }

        // Retrieve the category filters.
        $filters = Filter::all();

        // Build a list of selected regions by looking up their IDs.
        $region_ids = Setting::where('key', 'regions')->pluck('value');
        $regions = Region::whereIn('regionID', explode(',', $region_ids))->get();

        // Do the same for selected alliances.
        $alliance_ids = Setting::where('key', 'alliances')->pluck('value');
        $alliances = Alliance::whereIn('id', explode(',', $alliance_ids))->get();

        // Load the template containing the form to update settings.
        return View::make('settings')
            ->with('api_key_id', $api_key_id)
            ->with('api_key_verification_code', $api_key_verification_code)
            ->with('api_key_character_id', $api_key_character_id)
            ->with('shipping_cost', $shipping_cost)
            ->with('home_region_id', $home_region_id)
            ->with('home_region_name', $home_region_name)
            ->with('characters', $characters)
            ->with('alliances', $alliances)
            ->with('alliance_ids', $alliance_ids)
            ->with('filters', $filters)
            ->with('regions', $regions)
            ->with('region_ids', $region_ids);

    }

    /**
     * Handle submitted settings.
     *
     * @return
     */
    public function postIndex()
    {

        if (Input::has('api_key_id'))
        {
            $api_key_id = Setting::where('key', 'api_key_id')->firstOrFail();
            $api_key_id->value = Input::get('api_key_id');
            $api_key_id->save();
        }

        if (Input::has('api_key_verification_code'))
        {
            $api_key_verification_code = Setting::where('key', 'api_key_verification_code')->firstOrFail();
            $api_key_verification_code->value = Input::get('api_key_verification_code');
            $api_key_verification_code->save();
        }

        if (Input::has('api_key_character_id'))
        {
            $api_key_character_id = Setting::where('key', 'api_key_character_id')->firstOrFail();
            $api_key_character_id->value = Input::get('api_key_character_id');
            $api_key_character_id->save();
        }

        if (Input::has('home_region_id'))
        {
            $home_region_id = Setting::where('key', 'home_region_id')->firstOrFail();
            $home_region_id->value = Input::get('home_region_id');
            $home_region_id->save();
        }

        if (Input::has('regions'))
        {
            // Clean up the input in case the JS screwed up somewhere.
            $input = preg_replace('/^,|,$/', '', Input::get('regions'));
            $regions = Setting::where('key', 'regions')->firstOrFail();
            $regions->value = $input;
            $regions->save();
        }

        if (Input::has('alliances'))
        {
            // Clean up the input in case the JS screwed up somewhere.
            $input = preg_replace('/^,|,$/', '', Input::get('alliances'));
            $alliances = Setting::where('key', 'alliances')->firstOrFail();
            $alliances->value = $input;
            $alliances->save();
            // We also need to populate the alliances table with these new alliances.
            $response = API::eveOnline('eve/AllianceList', 'version=1');
            foreach ($response->body->result->rowset->row as $row)
            {
                if (strpos($input, (string)$row['allianceID']) !== FALSE) {
                    $alliance = Alliance::find($row['allianceID']);
                    if ( ! isset($alliance->id))
                    {
                        $alliance = new Alliance;
                        $alliance->id = $row['allianceID'];
                        $alliance->allianceName = $row['name'];
                        $alliance->save();
                    }
                }
            }
        }

        if (Input::has('shipping_cost'))
        {
            $shipping_cost = Setting::where('key', 'shipping_cost')->firstOrFail();
            $shipping_cost->value = Input::get('shipping_cost');
            $shipping_cost->save();
        }

        // Process default filters.
        DB::table('filters')->update(array('is_default' => 0));
        $filters = Filter::all();
        foreach ($filters as $filter)
        {
            if (Input::has($filter->categoryName))
            {
                $category = Filter::find($filter->categoryID);
                $category->is_default = 1;
                $category->save();
            }
        }

        return Redirect::to('settings');

    }

    /**
     * AJAX response for autocomplete of systems. No longer used.
     */
    public function getSystems()
    {
        $matches = array();
        $systems = System::where('solarSystemName', 'LIKE', '%' . Input::get('term') . '%')->get();
        foreach ($systems as $system)
        {
            $matches[] = '{"region":"' . $system->region->regionName . '","label":"' . $system->solarSystemName . '","value":"' . $system->solarSystemID . '"}';
        }
        echo '[' . implode(',', $matches) . ']';
    }

    /**
     * AJAX response for autocomplete of regions.
     */
    public function getRegions()
    {
        $matches = array();
        $regions = Region::where('regionName', 'LIKE', '%' . Input::get('term') . '%')->get();
        foreach ($regions as $region)
        {
            $matches[] = '{"label":"' . $region->regionName . '","value":"' . $region->regionID . '"}';
        }
        echo '[' . implode(',', $matches) . ']';
    }

    /**
     * AJAX response for autocomplete of alliances.
     * TODO: This is horrifically slow, need to cache the alliance list.
     */
    public function getAlliances()
    {
        $matches = array();
        $response = API::eveOnline('eve/AllianceList', 'version=1');
        foreach ($response->body->result->rowset->row as $row)
        {
            if (stristr($row['name'], Input::get('term'))) {
                $matches[] = '{"label":"' . $row['name'] . '","value":"' . $row['allianceID'] . '"}';
            }
        }
        echo '[' . implode(',', $matches) . ']';
    }

}
