<?php namespace ETH;

use Setting;
use Httpful\Request;

class API {

    /*
    |--------------------------------------------------------------------------
    | API Controller
    |--------------------------------------------------------------------------
    |
    | Abstraction for all API calls, including caching.
    |
    */

    /**
     * Make a call to the eve-central.com API to pull market data on one or more
     * market types. Return the XML structure.
     *
     * TODO: Add caching of results locally.
     */
    public static function eveCentral($types, $regions = NULL, $system = NULL)
    {
        // Set the base URL.
        $url = 'http://api.eve-central.com/api/marketstat?';

        // Create the list of types to query.
        if (is_array($types))
        {
            foreach ($types as $type)
            {
                $url .= 'typeid=' . $type . '&';
            }
        }
        else
        {
            $url .= 'typeid=' . $types . '&';
        }

        // Limit the search to a specific system or region.
        if (isset($system))
        {
            $url .= 'usesystem=' . $system;
        }
        else
        {
            if (is_array($regions))
            {
                foreach ($regions as $region)
                {
                    $url .= 'regionlimit=' . $region . '&';
                }
            }
            else
            {
                $url .= 'regionlimit=' . $regions;
            }
        }

        // Make the API call.
        $response = Request::get($url)->send();

        // Convert the response into an XML object.
        $xml = simplexml_load_string($response->body);

        // Return the XML.
        return $xml;

    }

    public static function eveOnline($api, $additional_parameters = NULL)
    {

        // Set the base URL.
        $url = 'https://api.eveonline.com/';

        // Add the specific API call to use.
        $url .= $api . '.xml.aspx?';

        // Retrieve settings from database and add them to the API call.
        $api_key_id = Setting::where('key', 'api_key_id')->firstOrFail();
        $api_key_verification_code = Setting::where('key', 'api_key_verification_code')->firstOrFail();
        $url .= 'keyID=' . $api_key_id->value . '&';
        $url .= 'vCode=' . $api_key_verification_code->value;

        if (isset($additional_parameters))
        {
            foreach ($additional_parameters as $parameter)
            {
                $param_value = Setting::where('key', $parameter['db_key'])->firstOrFail();
                $url .= '&' . $parameter['url_key'] . '=' . $param_value->value;

            }
        }

        $response = Request::get($url)->send();

        return $response;

    }

}
