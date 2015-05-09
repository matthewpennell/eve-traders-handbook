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
        $alliances = Setting::where('key', 'alliances')->first();

        $characters = array();
        // If the API key is set, retrieve a list of characters.
        if ($api_key_id->value != '' && $api_key_verification_code->value != '')
        {
            $response = API::eveOnline('account/Characters');
            foreach ($response->body->result->rowset->row as $row)
            {
                $characters[(string)$row['characterID']] = $row['name'];
            }
        }

        // Retrieve the category filters.
        $filters = Filter::all();

        // Build a list of selected systems by looking up their IDs.
        $system_ids = Setting::where('key', 'systems')->pluck('value');
        $systems = System::whereIn('solarSystemID', explode(',', $system_ids))->get();

        // Load the template containing the form to update settings.
        return View::make('settings')
            ->with('api_key_id', $api_key_id)
            ->with('api_key_verification_code', $api_key_verification_code)
            ->with('api_key_character_id', $api_key_character_id)
            ->with('characters', $characters)
            ->with('alliances', $alliances)
            ->with('filters', $filters)
            ->with('systems', $systems)
            ->with('system_ids', $system_ids);

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

        if (Input::has('systems'))
        {
            $systems = Setting::where('key', 'systems')->firstOrFail();
            $systems->value = Input::get('systems');
            $systems->save();
        }

        if (Input::has('alliances'))
        {
            $alliances = Setting::where('key', 'alliances')->firstOrFail();
            $alliances->value = Input::get('alliances');
            $alliances->save();
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
     * AJAX response for autocomplete.
     */
    public function getSystems()
    {
        $json = '[';
        $systems = System::where('solarSystemName', 'LIKE', '%' . Input::get('term') . '%')->get();
        $count = count($systems);
        $i = 1;
        foreach ($systems as $system)
        {
            $json .= '{"region":"' . $system->region->regionName . '","label":"' . $system->solarSystemName . '","value":"' . $system->solarSystemID . '"}';
            if ($i < $count)
            {
                $json .= ',';
            }
            $i++;
        }
        $json .= ']';
        echo $json;
    }

}
