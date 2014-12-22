<?php namespace ETH;

use Price;
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
    public static function eveCentral($types, $regions = '', $system = '')
    {

        // Create the array to be filled with price data.
        $pricedata = array();

        // If the passed arguments are not arrays, convert them to arrays.
        if (!is_array($types))
        {
            $types = array($types);
        }
        if (!is_array($regions))
        {
            $regions = array($regions);
        }


        // Check if we have a cached price for each item (in combination with the selected regions and/or system) in the database already.
        // If a cached price is found, remove that ID from the array and update the pricedata array.
        foreach ($types as $type)
        {
            $price = Price::where('typeID', $type)->where('regions', implode(',', $regions))->where('system', $system)->first();
            if (isset($price))
            {
                // Found a cached price - add this item to the response.
                $pricedata[$type] = (object) array(
                    "id"        => $type,
                    "volume"	=> $price->volume,
                    "avg"		=> $price->avg,
                    "max"		=> $price->max,
                    "min"		=> $price->min,
                    "median"	=> $price->median,
                );
                // Drop this item from the list to be retrieved from the API.
                if(($key = array_search($type, $types)) !== false) {
                    unset($types[$key]);
                }
            }
        }

        // If there is anything left to query...
        if (count($types))
        {

            // Set the base URL.
            $url = 'http://api.eve-central.com/api/marketstat?';

            foreach ($types as $type)
            {
                $url .= 'typeid=' . $type . '&';
            }

            // Limit the search to a specific system or region.
            if ($system != '')
            {
                $url .= 'usesystem=' . $system;
            }
            else
            {
                foreach ($regions as $region)
                {
                    $url .= 'regionlimit=' . $region . '&';
                }
            }

            // Make the API call.
            $response = Request::get($url)->send();

            // Convert the response into an XML object.
            $xml = simplexml_load_string($response->body);

            // Loop through the results, building a response and caching the data.
            foreach($xml->marketstat->type as $api_result)
            {
                $id = (int) $api_result['id'];
                // Cache the retrieved prices.
                $price = Price::firstOrNew(array(
                    'typeID'    => $id,
                    'regions'   => (isset($regions)) ? implode(',', $regions) : '',
                    'system'    => ($system != '') ? $system : '',
                ));
                $price->volume = $api_result->sell->volume;
                $price->avg = $api_result->sell->avg;
                $price->max = $api_result->sell->max;
                $price->min = $api_result->sell->min;
                $price->median = $api_result->sell->median;
                $price->save();

                // Build the response object.
                $pricedata[$id] = (object) array(
                    "id"        => $id,
                    "volume"	=> $api_result->sell->volume,
                    "avg"		=> $api_result->sell->avg,
                    "max"		=> $api_result->sell->max,
                    "min"		=> $api_result->sell->min,
                    "median"	=> $api_result->sell->median,
                );

            }

        }

        return $pricedata;

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
