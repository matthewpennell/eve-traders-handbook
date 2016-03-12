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
     * Make a call to the public CREST API provided by CCP to pull market data on
     * an individual market type. Returns JSON for 13 months of market data.
     *
     * Because this needs to function in the same way as the eve-central.com API call
     * it replaces, it makes recurive calls to itself until it has retrieved all the
     * items requested.
     */
    public static function CREST($types, $regions = '', $system = '', $pricedata = NULL)
    {

        // If $pricedata doesn't exist we need to create it.
        if (!isset($pricedata))
        {
            $pricedata = array();
        }

        // If the passed arguments are not arrays, convert them to arrays.
        if (!is_array($types))
        {
            $types = array($types);
        }
        if (!is_array($regions))
        {
            $regions = array($regions);
        }

        // CREST doesn't use systems, so if a system was passed we need to convert it into the appropriate region check.
        if ($system == 30000142) // Jita is in The Forge
        {
            array_push($regions, 10000002);
        }
        if ($system == 30002187) // Amarr is in Domain
        {
            array_push($regions, 10000043);
        }

        // Check if we have a cached price for each item (in combination with the selected regions and/or system) in the database already.
        // If a cached price is found, remove that ID from the array and update the pricedata array.
        if (!isset($pricedata) || count($pricedata) == 0)
        {
            foreach ($types as $type)
            {
                $price = Price::where('typeID', $type)->where('regions', implode(',', $regions))->where('system', $system)->whereRaw('updated_at > DATE_SUB(now(), INTERVAL 1 HOUR)')->first();
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
        }

        // If there is more than one item in the $types array, process the last item.
        if (count($types) > 0)
        {

            $type = array_shift($types);

            // Set the base URL.
            $url = 'https://public-crest.eveonline.com/market/';

            foreach ($regions as $region)
            {
                $url .= $region;
            }

            $url .= '/types/' . $type . '/history/';

            // Make the API call.
            $response = Request::get($url)->send();

            // Convert the response into an XML object.
            $json = $response->body;

            // Calculate the average volume and price over the last period.
            $find_average = API::findAverage($json);

            $id = (int) $type;

            // Cache the retrieved prices.
            $price = Price::firstOrNew(array(
                'typeID'    => $id,
                'regions'   => (isset($regions)) ? implode(',', $regions) : '',
                'system'    => ($system != '') ? $system : '',
            ));
            $price->volume = $find_average->volume;
            $price->avg = $find_average->avgPrice;
            $price->max = $find_average->highPrice;
            $price->min = $find_average->lowPrice;
            $price->median = $find_average->median;
            $price->save();

            // Build the response object.
            $pricedata[$id] = (object) array(
                "id"        => $id,
                "volume"	=> $find_average->volume,
                "avg"		=> $find_average->avgPrice,
                "max"		=> $find_average->highPrice,
                "min"		=> $find_average->lowPrice,
                "median"	=> $find_average->median,
            );

        }

        if (count($types) != 0)
        {
            return API::CREST($types, $regions, $system, $pricedata);
        }
        else
        {
            return $pricedata;
        }

    }

    /**
     * Given the JSON response from CREST, look at the last 30 days and
     * return the average daily volume, average price, min, max and median
     * prices.
     */
    private static function findAverage($json)
    {

        $days = 30;

        $total_volume = 0;
        $total_avg_price = 0;
        $overall_max_price = 0;
        $overall_min_price = 1000000000;
        $avg_prices_array = array();

        for ($i = 0; $i < $days; $i++)
        {
            $last_day = array_pop($json->items);
            $total_volume += $last_day->volume;
            $total_avg_price += $last_day->avgPrice;
            if ($overall_max_price < $last_day->highPrice)
            {
                $overall_max_price = $last_day->highPrice;
            }
            if ($overall_min_price > $last_day->lowPrice)
            {
                $overall_min_price = $last_day->lowPrice;
            }
            array_push($avg_prices_array, $last_day->avgPrice);
        }

        // Calculate the median price.
        sort($avg_prices_array);
        $median = $days / 2;
        if ($median == round($median))
        {
            $median_price = $avg_prices_array[$median];
        }
        else
        {
            $median_price = ($avg_prices_array[$median - 0.5] + $avg_prices_array[$median + 0.5]) / 2;
        }

        $price_data = (object) array(
            'volume'    => round($total_volume / $days),
            'avgPrice'  => round($total_avg_price / $days),
            'highPrice' => $overall_max_price,
            'lowPrice'  => $overall_min_price,
            'median'    => $median_price,
        );

        return $price_data;

    }

    /**
     * Make a call to the eve-central.com API to pull market data on one or more
     * market types. Return the XML structure.
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
            $price = Price::where('typeID', $type)->where('regions', implode(',', $regions))->where('system', $system)->whereRaw('updated_at > DATE_SUB(now(), INTERVAL 1 HOUR)')->first();
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
            if (is_array($additional_parameters))
            {
                foreach ($additional_parameters as $parameter)
                {
                    $param_value = Setting::where('key', $parameter['db_key'])->firstOrFail();
                    $url .= '&' . $parameter['url_key'] . '=' . $param_value->value;

                }
            }
            else
            {
                $url .= '&' . $additional_parameters;
            }
        }

        $response = Request::get($url)->send();

        return $response;

    }

}
