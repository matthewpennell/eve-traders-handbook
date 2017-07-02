<?php

use Httpful\Request;

class ImportController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Import Controller
    |--------------------------------------------------------------------------
    |
    | Import most recent kill data from zKillboard API.
    |
    */

    public $API_SYSTEM_LIMIT = 10; // how many systems we want to retrieve at once (zKillboard API limit)
    public $zkillboard_urls = array(); // array of zKillboard URLs we need to send a request to
    public $kills = array(); // all kill records imported from zkillboard

    /**
     * Refactored zKillboard import script July 2nd 2017 to hopefully reduce
     * the volume of OOM errors thrown by the cron job.
     */
    public function getZkillboard()
    {

        // Get the list of requested regions.
        $regions_setting = Setting::where('key', 'regions')->firstOrFail();
        $regions = explode(',', $regions_setting->value);

        // Loop through each region and create a list of all of the the child systems.
        $systems = array();
        $child_systems = DB::table('mapSolarSystems')->select('solarSystemID')->whereIn('regionID', $regions)->get();
        foreach ($child_systems as $child_system)
        {
            array_push($systems, $child_system->solarSystemID);
        }

        // Re-order the system IDs to be in numeric order (zKillboard requires this in API calls).
        sort($systems);

        // Retrieve the selected alliances from the database.
        $alliances_setting = Setting::where('key', 'alliances')->firstOrFail();
        if ($alliances_setting->value != '')
        {
            // Turn into an array, resort, then convert back into a comma-seperated string.
            $alliances_array = explode(',', $alliances_setting->value);
            sort($alliances_array);
            $alliances = implode(',', $alliances_array);
        }

        // Build a set of zKill API calls, limited to 10 systems per call.
        while (count($systems) > 0)
        {

            // Shift the first $API_SYSTEM_LIMIT systems off the array and into a string.
            $first_ten_systems = array();
            for ($i = 0; $i < $this->API_SYSTEM_LIMIT && count($systems); $i++)
            {
                $first_ten_systems[] = preg_replace('/\s+/', '', array_shift($systems));
            }

            // Build the API URL.
            $url = 'https://zkillboard.com/api/xml/losses/no-attackers/allianceID/' . $alliances . '/solarSystemID/' . implode(',', $first_ten_systems);

            // Store it in the global array.
            $this->zkillboard_urls[] = $url;

        }

        // Perform the API requests.
        $this->callZkillboardAPI();

        // Process all of the retrieved kill data.
        $this->processKillData();

    }

    // Call each of the prepared zKill URLs, and store the results in the global kill list array.
    public function callZkillboardAPI()
    {

        foreach($this->zkillboard_urls as $url)
        {

            $response = Request::get($url)
                ->addHeader('Accept-Encoding', 'gzip')
                ->addHeader('User-Agent', 'Eve Traders Handbook')
                ->send();

            if (isset($response->body) && strlen($response->body) > 0)
            {

                // Error handler for invalid XML response.
                libxml_use_internal_errors(true);
                $body = simplexml_load_string(gzdecode($response->body));
                $xml = explode("\n", gzdecode($response->body));
                if (!$body)
                {
                    $errors = libxml_get_errors();
                    foreach ($errors as $error)
                    {
                        echo display_xml_error($error, $xml) . "\n";
                    }
                    libxml_clear_errors();
                }
                elseif (isset($body->result->rowset))
                {
                    // Parse the response, inserting each result row into the global $kills array.
                    foreach ($body->result->rowset->row as $row) {
                        $this->kills[] = $row;
                    }
                }
                else
                {
                    echo "No kills found in selected system(s).\n";
                }

            }
            else
            {
                echo "No response received from zKillboard API: $url\n";
            }

        }

    }

    /**
     * Parse all of the kill data and insert it into the database.
     */
    public function processKillData()
    {

        $insert_count = 0;

        // Parse the kill data, inserting the losses into the database.
        foreach ($this->kills as $row) {

            // First check whether this kill has not already been recorded.
            $kill = Kill::find($row['killID']);

            if ( ! isset($kill->killID))
            {

                // Create and save the new kill record.
                $kill = new Kill;
                $kill->killID = $row['killID'];
                $kill->solarSystemID = $row['solarSystemID'];
                $kill->characterID = $row->victim['characterID'];
                $kill->characterName = $row->victim['characterName'];
                $kill->allianceID = $row->victim['allianceID'];
                $kill->corporationID = $row->victim['corporationID'];
                $kill->shipTypeID = $row->victim['shipTypeID'];
                $kill->killTime = $row['killTime'];
                $kill->save();
                $insert_count++;

                // Insert the alliance information into the database unless it already exists.
                $alliance = Alliance::find($kill->allianceID);

                if ( ! isset($alliance->id))
                {
                    $alliance = new Alliance;
                    $alliance->id = $kill->allianceID;
                    $alliance->allianceName = $row->victim['allianceName'];
                    $alliance->save();
                }

                // Insert the corporation information into the database unless it already exists.
                $corporation = Corporation::find($kill->corporationID);

                if ( ! isset($corporation->id))
                {
                    $corporation = new Corporation;
                    $corporation->id = $kill->corporationID;
                    $corporation->corporationName = $row->victim['corporationName'];
                    $corporation->save();
                }

                // Insert the ship type that was lost into the database unless it already exists.
                $ship = Ship::find($kill->shipTypeID);
                $type = Type::find($kill->shipTypeID);

                if ( ! isset($ship->id))
                {
                    $ship = new Ship;
                    $ship->id = $kill->shipTypeID;
                    $ship->shipName = $type->typeName;
                    $ship->save();
                }

                // Insert the ship loss into the items database as well.
                if (stristr($ship->shipName, 'Capsule') === FALSE)
                {
                    $item = new Item;
                    $item->killID = $row['killID'];
                    $item->typeID = $kill->shipTypeID;
                    $item->typeName = $type->typeName;
                    $item->categoryName = $type->group->category['categoryName'];
                    $item->metaGroupName = (isset($type->metaType->metaGroup['metaGroupName'])) ? $type->metaType->metaGroup['metaGroupName'] : '';
                    $item->allowManufacture = 1;
                    $item->qty = 1;
                    $item->save();
                }

                // Add the category to the list of filters available on the site.
                $filter = Filter::find($type->group->category['categoryID']);

                if ( ! isset($filter->categoryID))
                {
                    $filter = new Filter;
                    $filter->categoryID = $type->group->category['categoryID'];
                    $filter->categoryName = $type->group->category['categoryName'];
                    $filter->iconID = $type->group->category['iconID'];
                    $filter->save();
                }

                // Loop through the items lost in the kill. Insert each one into the items table.
                if (isset($row->rowset->row))
                {
                    foreach ($row->rowset->row as $loss)
                    {
                        $typeID = (int) $loss['typeID'];
                        $item = Item::where('typeID', '=', $typeID)->first();

                        // If this item already exists in the items table, we don't need to re-query all the additional
                        // information, we can just copy it from an existing row.
                        if (isset($item))
                        {

                            // This type has already been seen. Duplicate the record and save the new instance.
                            $clone = new Item;
                            $clone = $item->replicate();

                            // Update the right killID and quantity, and unset the primary key and date columns.
                            $clone->killID = $row['killID'];
                            $clone->qty = $loss['qtyDropped'] + $loss['qtyDestroyed'];
                            unset($clone->id);
                            unset($clone->created_at);
                            unset($clone->updated_at);

                            // Save the cloned row.
                            $clone->save();

                        }
                        else
                        {

                            // This is a never-before-seen lost item. Create a new row and look up all the related details.
                            $item = new Item;
                            $type = Type::find($typeID);
                            // If the type wasn't found, probably the app owner hasn't updated their databases with the most recent data dump.
                            if (isset($type))
                            {
                                $item->killID = (int) $row['killID'];
                                $item->typeID = $typeID;
                                $item->typeName = $type->typeName;
                                // If the item is a rig, we need to be replace the categoryName with "Rigs".
                                if (substr($type->marketGroup['marketGroupName'], -5) == ' Rigs')
                                {
                                    $item->categoryName = 'Rigs';
                                }
                                else
                                {
                                    $item->categoryName = $type->group->category['categoryName'];
                                }
                                $metaGroupName = (isset($type->metaType->metaGroup['metaGroupName'])) ? $type->metaType->metaGroup['metaGroupName'] : '';
                                if ($metaGroupName == 'Tech I' || $metaGroupName == '')
                                {
                                    $metaLevel = DB::table('dgmTypeAttributes')->where('typeID', $typeID)->where('attributeID', 633)->first();
                                    if (isset($metaLevel))
                                    {
                                        $metaGroupName = 'Meta ';
                                        $metaGroupName .= (isset($metaLevel->valueInt)) ? $metaLevel->valueInt : $metaLevel->valueFloat;
                                    }
                                }
                                $item->metaGroupName = $metaGroupName;
                                $blueprint = Type::where('typeName', $type->typeName . ' Blueprint')->count();
                                if ($blueprint > 0)
                                {
                                    $item->allowManufacture = 1;
                                }
                                $item->qty = $loss['qtyDropped'] + $loss['qtyDestroyed'];
                                $item->save();

                                // Add the category to the list of filters available on the site.
                                if (substr($type->marketGroup['marketGroupName'], -5) == ' Rigs')
                                {
                                    $categoryID = 999;
                                }
                                else {
                                    $categoryID = $type->group->category['categoryID'];
                                }

                                $filter = Filter::find($categoryID);

                                if ( ! isset($filter->categoryID))
                                {
                                    $filter = new Filter;
                                    if ($categoryID == 999)
                                    {
                                        $filter->categoryID = $categoryID;
                                        $filter->categoryName = 'Rigs';
                                        $filter->iconID = $type->group->category['iconID'];
                                    }
                                    else
                                    {
                                        $filter->categoryID = $type->group->category['categoryID'];
                                        $filter->categoryName = $type->group->category['categoryName'];
                                        $filter->iconID = $type->group->category['iconID'];
                                    }
                                    $filter->save();
                                }

                            }
                            else
                            {
                                echo 'Item #' . $typeID . " was not found in the database. Perhaps you need to import the latest export dump?\n";
                            }

                        }
                    }

                }

            }

        }

        echo "Inserted $insert_count new kills!\n";

    }

    /**
     * Clear out all of the stored kills data.
     */
    public function getClear()
    {
        Kill::truncate();
        Filter::truncate();
        Item::truncate();
        return Redirect::action('MasterController@home');
    }

}
