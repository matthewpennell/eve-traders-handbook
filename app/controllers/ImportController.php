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

    // How many systems we want to retrieve at once (zKillboard API limit).
    public $api_system_limit = 10;

    /**
     * Import zKillboard kills for the selected systems and alliances.
     */
    public function getZkillboard($systems = '')
    {

        // If this is the initial call to the function, retrieve the list of systems from the DB.
        if ($systems == '')
        {
            // Pull the list of regions.
            $regions_setting = Setting::where('key', 'regions')->firstOrFail();
            // Loop through them and build the list of systems.
            $systems_array = array();
            $regions = explode(',', $regions_setting->value);
            foreach ($regions as $region)
            {
                $child_systems = System::where('regionID', $region)->get();
                foreach ($child_systems as $child_system)
                {
                    array_push($systems_array, $child_system->solarSystemID);
                }
            }
            // Order the system IDs sequentially (new zkillboard restriction).
            sort($systems_array);
            // Turn it back into a string.
            $systems = implode(',', $systems_array);
        }

        // Convert the comma-seperated string into an array.
        $systems_array = explode(',', $systems);

        // If there are more systems in the list than we want to pull at once, chop off the first X and call this function again.
        while (count($systems_array) > $this->api_system_limit)
        {
            $this->getZkillboard(implode(',', array_splice($systems_array, 0, $this->api_system_limit)));
        }

        // Retrieve the selected alliances from the database.
        $alliance_ids = '';
        $alliances_setting = Setting::where('key', 'alliances')->firstOrFail();
        if ($alliances_setting->value != '')
        {
            $alliances_array = explode(',', $alliances_setting->value);
            // Order the system IDs sequentially (new zkillboard restriction).
            sort($alliances_array);
            // Turn it back into a string.
            $alliances = implode(',', $alliances_array);
            $alliance_ids = 'allianceID/' . $alliances . '/';
        }

        // Build the API URL.
        $url = 'https://zkillboard.com/api/xml/losses/no-attackers/'
             . $alliance_ids
             . 'solarSystemID/' . preg_replace('/\s+/', '', $systems) . '/';

        // Send the request.
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
                    echo display_xml_error($error, $xml);
                }
                libxml_clear_errors();
            }
            else
            {
                if (isset($body->result->rowset))
                {

                    $insert_count = 0;

                    // Parse the response, inserting the losses into the database.
                    foreach ($body->result->rowset->row as $row) {

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
                                            // If the item is a rig, we need to be replace the categoryName with "Rig".
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
                                            echo 'Item #' . $typeID . ' was not found in the database. Perhaps you need to import the latest export dump? ';
                                        }

                                    }
                                }

                            }

                        }

                    }

                    echo "Inserted $insert_count new kills! ";

                }
                else
                {
                    echo "No kills found in selected system(s). ";
                }
            }
        }
        else
        {
            echo "No response received from zKillboard API. ";
        }

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
