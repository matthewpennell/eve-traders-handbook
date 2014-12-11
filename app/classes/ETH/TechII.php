<?php namespace ETH;

use DB;
use Category;
use Group;
use Type;
use ActivityMaterial;
use ActivitySkill;
use Setting;
use Httpful\Request;

class TechII {

    /*
    |--------------------------------------------------------------------------
    | TechII Controller
    |--------------------------------------------------------------------------
    |
    | Performs all calculations relating to the invention of T2 blueprints
    | and the subsequent manufacture and potential profit of those items.
    |
    */

    public static function getInventionFigures($type)
    {

        // Suppress XML errors.
        libxml_use_internal_errors(true);

        // We'll assume that the source blueprint can be obtained for zero cost.
        // First thing to calculate is the different prices for materials involved
        // in invention (decryptors and datacores), and the effects they have on
        // the T2 blueprint created.

        // An array to hold all of the information about possible invention outcomes.
        $invention_data = array();

        // First, retrieve all the different decryptors from the database.
        $decryptor_group = Group::where('groupName', 'Generic Decryptor')->firstOrFail();
        $decryptors = Type::where('groupID', $decryptor_group->groupID)->get();

        // Now find the other items needed to invent the T2 blueprint.
        // First we need to identify the T1 blueprint for this item.
        $t1_blueprint = Type::where('typeName', str_replace('II', 'I Blueprint', $type->typeName))->firstOrFail();

        // Now, retrieve a list of the items (datacores) required to invent from it.
        $materials = ActivityMaterial::where('typeID', $t1_blueprint->typeID)->where('activityID', 8)->get();

        // Retrieve the prices of the materials from eve-central.
        $manufacturing = array();
        $total_price = 0;
        foreach ($materials as $material)
        {
            // For each manufacturing item, make an API call to get the local price of materials.
            // TODO: Change this to use stored home region ID and to cache the result.
            $url = 'http://api.eve-central.com/api/marketstat'
            . '?'
            . 'typeid=' . $material->materialTypeID
            . '&'
            . 'regionlimit=10000014';

            $response = Request::get($url)->send();
            $xml = simplexml_load_string($response->body);
            $price_per_unit = $xml->marketstat->type->sell->median;
            $jita_price = FALSE;

            // If the price returned is zero for the selected region, do another check at Jita prices.
            if ($price_per_unit == 0)
            {
                $url = 'http://api.eve-central.com/api/marketstat'
                . '?'
                . 'typeid=' . $material->materialTypeID
                . '&'
                . 'usesystem=30000142';

                $response = Request::get($url)->send();
                $jita = simplexml_load_string($response->body);
                $price_per_unit = $jita->marketstat->type->sell->median;
                $jita_price = TRUE;
            }

            $manufacturing[] = (object) array(
                "typeName"	=> Type::find($material->materialTypeID)->typeName,
                "qty"		=> $material->quantity,
                "price"		=> $material->quantity * $price_per_unit,
                "jita"		=> $jita_price,
            );
            $total_price += $material->quantity * $price_per_unit;
        }

        // Figure out which skills are needed to invent the T2 blueprint.
        $activity_skills = ActivitySkill::where('typeID', $t1_blueprint->typeID)->where('activityID', 8)->get();
        $required_skills = array();
        $skills = array();
        foreach ($activity_skills as $activity_skill)
        {
            $required_skills[] = $activity_skill->skillID;
        }

        // Create variables.
        $encryption_skill_level = 0;
        $science_skill_level = 0;

        // Retrieve the character's character sheet with skill details.
        $api_key_id = Setting::where('key', 'api_key_id')->firstOrFail();
        $api_key_verification_code = Setting::where('key', 'api_key_verification_code')->firstOrFail();
        $api_key_character_id = Setting::where('key', 'api_key_character_id')->firstOrFail();

        $url = 'https://api.eveonline.com'
        . '/char/CharacterSheet.xml.aspx'
        . '?'
        . 'keyID=' . $api_key_id->value
        . '&'
        . 'characterID=' . $api_key_character_id->value
        . '&'
        . 'vCode=' . $api_key_verification_code->value;

        $response = Request::get($url)->send();
        foreach ($response->body->result->rowset as $rowset)
        {
            if ($rowset['name'] == 'skills')
            {
                foreach ($rowset->row as $row) {
                    // Check the character's skills against those required for invention.
                    if (in_array($row['typeID'], $required_skills))
                    {
                        if (in_array($row['typeID'], ['3408', '21790', '21791', '23087', '23121']))
                        {
                            $encryption_skill_level += $row['level'];
                        }
                        else
                        {
                            $science_skill_level += $row['level'];
                        }
                    }
                }
            }
        }

        // Next, we need to retrieve the prices of the materials needed to manufacture the T2 item.
        $t2_manufacture_price = 0;
        $type_materials = DB::table('invTypeMaterials')->where('typeID', $type->typeID)->get();
        foreach ($type_materials as $material)
        {
            // For each manufacturing item, make an API call to get the local price of materials.
            // TODO: Change this to use stored home region ID and to cache the result.
            $url = 'http://api.eve-central.com/api/marketstat'
            . '?'
            . 'typeid=' . $material->materialTypeID
            . '&'
            . 'regionlimit=10000014';

            $response = Request::get($url)->send();
            if ($xml = simplexml_load_string($response->body))
            {
                $price_per_unit = $xml->marketstat->type->sell->median;
                $jita_price = FALSE;
            }
            else
            {
                $price_per_unit = 0;
            }

            // If the price returned is zero for the selected region, do another check at Jita prices.
            if ($price_per_unit == 0)
            {
                $url = 'http://api.eve-central.com/api/marketstat'
                . '?'
                . 'typeid=' . $material->materialTypeID
                . '&'
                . 'usesystem=30000142';

                $response = Request::get($url)->send();
                $jita = simplexml_load_string($response->body);
                $price_per_unit = $jita->marketstat->type->sell->median;
                $jita_price = TRUE;
            }

            $manufacturing[] = (object) array(
                "typeName"	=> Type::find($material->materialTypeID)->typeName,
                "qty"		=> $material->quantity,
                "price"		=> $material->quantity * $price_per_unit,
                "jita"		=> $jita_price,
            );
            $t2_manufacture_price += $material->quantity * $price_per_unit;
        }

        // For each decryptor, calculate the chance of blueprint creation and add the cost of the decryptor.
        // https://community.eveonline.com/news/dev-blogs/invention-updates/
        $t2_data = array();
        foreach ($decryptors as $decryptor)
        {

            // Grab the base variables.
            $base_chance_of_success = 34; // this only applies to modules, rigs and ammo, TODO fork this for ships (30%) and cruisers/industrials/Mackinaw (26%)

            // Parse the modifiers from this decryptor.
            preg_match('/Probability Multiplier: \+?(\-?\d+)\%/', $decryptor->description, $probability_modifier);
            preg_match('/Max. Run Modifier: \+?(\-?\d+)/', $decryptor->description, $max_run_modifier);
            preg_match('/Material Efficiency Modifier: \+?(\-?\d+)/', $decryptor->description, $me_modifier);
            preg_match('/Time Efficiency Modifier: \+?(\-?\d+)/', $decryptor->description, $te_modifier);

            // Calculate the overall change of invention for each decryptor.
            $modified_chance_of_success = $base_chance_of_success * (1 + (($encryption_skill_level / 40) + ($science_skill_level / 30)));
            if (count($probability_modifier) > 1)
            {
                $modified_chance_of_success = $modified_chance_of_success * (100 + $probability_modifier[1]) / 100;
            }

            // Find the cost of the decryptor and add it to the total cost.
            // TODO: Change this to use stored home region ID and to cache the result.
            $url = 'http://api.eve-central.com/api/marketstat'
            . '?'
            . 'typeid=' . $decryptor->typeID
            . '&'
            . 'regionlimit=10000014';

            $response = Request::get($url)->send();
            $xml = simplexml_load_string($response->body);
            $price_per_unit = $xml->marketstat->type->sell->median;
            $jita_price = FALSE;

            // If the price returned is zero for the selected region, do another check at Jita prices.
            if ($price_per_unit == 0)
            {
                $url = 'http://api.eve-central.com/api/marketstat'
                . '?'
                . 'typeid=' . $decryptor->typeID
                . '&'
                . 'usesystem=30000142';

                $response = Request::get($url)->send();
                $jita = simplexml_load_string($response->body);
                $price_per_unit = $jita->marketstat->type->sell->median;
                $jita_price = TRUE;
            }

            $manufacturing[] = (object) array(
                "typeName"	=> $decryptor->typeName,
                "qty"		=> 1,
                "price"		=> $price_per_unit,
                "jita"		=> $jita_price,
            );

            $t2_data[$decryptor->typeName] = array(
                "typeName"              => $decryptor->typeName,
                "chance_of_success"     => $modified_chance_of_success,
                "invention_price"       => $total_price + $price_per_unit,
                "t2_manufacture_price"  => $t2_manufacture_price,
            );

            if (count($max_run_modifier) > 1)
            {
                $t2_data[$decryptor->typeName]['max_run_modifier'] = $max_run_modifier[1];
            }
            if (count($me_modifier) > 1)
            {
                $t2_data[$decryptor->typeName]['me_modifier'] = $me_modifier[1];
            }

        }

        // Now, calculate the potential profit of each type of decryptor - taking into account the chance of failure, the max runs, and the Material Efficiency modifier.
        return $t2_data;

    }

}
