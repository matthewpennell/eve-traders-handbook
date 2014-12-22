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

}
