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

    /**
     * Import zKillboard kills for the selected systems and alliances.
     */
    public function getZkillboard($systems = '')
    {

        // Retrieve the selected systems from the database.
        if ($systems == '')
        {
            $systems_object = Setting::where('key', 'systems')->firstOrFail();
            $systems = $systems_object->value;
        }
        $systems_array = explode(',', $systems);
        if (count($systems_array) > 10)
        {
            $this->getZkillboard(implode(',',array_splice($systems_array, 10)));
            $systems = implode(',',array_splice($systems_array, 0, 10));
        }

        // Retrieve the selected alliances from the database.
        $alliances = Setting::where('key', 'alliances')->firstOrFail();

        // Build the API URL.
        $url = 'https://zkillboard.com/api/xml/losses/no-attackers/'
             . 'allianceID/' . preg_replace('/\s+/', '', $alliances->value) . '/'
             . 'solarSystemID/' . preg_replace('/\s+/', '', $systems) . '/';

        // Send the request.
        $response = Request::get($url)
            ->addHeader('Accept-Encoding', 'gzip')
            ->addHeader('User-Agent', 'eve-traders-handbook')
            ->send();

        if (isset($response->body) && strlen($response->body) > 0)
        {

            $body = simplexml_load_string(gzdecode($response->body));

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
                        if (!stristr($ship->shipName, 'Capsule'))
                        {
                            $ship->save();
                            // Insert the ship loss into the items database as well.
                            $item = new Item;
                            $item->killID = $row['killID'];
                            $item->typeID = $kill->shipTypeID;
                            $item->typeName = $type->typeName;
                            $item->categoryName = $type->group->category['categoryName'];
                            $item->metaGroupName = (isset($type->metaType->metaGroup['metaGroupName'])) ? $type->metaType->metaGroup['metaGroupName'] : '';
                            $item->allowManufacture = (stristr($type->typeName, 'Capsule')) ? 0 : 1;
                            $item->qty = 1;
                            $item->save();
                        }
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
                                $item->killID = (int) $row['killID'];
                                $item->typeID = $typeID;
                                $item->typeName = $type->typeName;
                                $item->categoryName = $type->group->category['categoryName'];
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
                                $filter = Filter::find($type->group->category['categoryID']);

                                if ( ! isset($filter->categoryID))
                                {
                                    $filter = new Filter;
                                    $filter->categoryID = $type->group->category['categoryID'];
                                    $filter->categoryName = $type->group->category['categoryName'];
                                    $filter->iconID = $type->group->category['iconID'];
                                    $filter->save();
                                }

                            }
                        }

                    }

                }

            }

            echo "Inserted $insert_count new kills!";

        }
        else
        {
            echo "No response received from zKillboard API.";
        }
    }

}
