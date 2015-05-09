<?php

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
        $settings = Setting::all();

        // Retrieve the category filters.
        $filters = Filter::all();

        // Build a list of selected systems by looking up their IDs.
        $system_ids = Setting::where('key', 'systems')->pluck('value');
        $systems = System::whereIn('solarSystemID', explode(',', $system_ids))->get();

        // Build an array of all systems and regions.
        $all_systems = System::all();

        // Load the template containing the form to update settings.
        return View::make('settings')
            ->with('settings', $settings)
            ->with('filters', $filters)
            ->with('systems', $systems)
            ->with('system_ids', $system_ids)
            ->with('all_systems', $all_systems);

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

}
