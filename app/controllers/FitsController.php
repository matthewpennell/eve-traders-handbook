<?php

class FitsController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Fitting Controller
	|--------------------------------------------------------------------------
	|
	| Ship fittings controller. Displays saved fits, allows for creating,
	| editing and deleting fits.
	|
	*/

	public function __construct()
    {

        // Check for a valid CSRF token on POST requests.
        $this->beforeFilter('csrf', array('on' => 'post'));

    }

	/**
	 * Show all saved fits.
	 */
	public function getIndex()
	{

		// Retrieve all the saved fits.
		$fits = Fit::all();

		// Load the template.
		return View::make('fits')
            ->with('fits', $fits);

	}

	/**
	 * Handle submitted changes to the fits.
	 */
	public function postIndex()
	{

		// Gather all submitted data.
		$fits = Input::all();

		// Loop through existing fits and update saved information.
		foreach($fits as $key => $value)
		{
			if (stristr($key, 'fit_new_'))
			{
				$eft_fitting = $value;
				$fittingarray = preg_split ('/$\R?^/m', $eft_fitting);
				$fit = new Fit();
				$fit->name = trim($fittingarray[0], "[]\n\r");
				$fit->eft_fitting = $eft_fitting;
				$fit->ship_dna = $this->makeDNA($eft_fitting);
				$fit->save();
			}
			else if (stristr($key, 'fit_'))
			{
				$id = substr($key, 4);
				$eft_fitting = $value;
				$fittingarray = preg_split ('/$\R?^/m', $eft_fitting);
				$fit = Fit::find($id);
				$fit->name = trim($fittingarray[0], "[]\n\r");
				$fit->eft_fitting = $eft_fitting;
				$fit->ship_dna = $this->makeDNA($eft_fitting);
				$fit->save();
			}
		}

		// Redirect back to the fitting display page.
		return Redirect::to('fits');

	}

	/**
	 * Given a typeID, return a list of the fits that use that module/item to display in a tooltip.
	 */
	public function getItem($id)
	{

		$tooltip = '';
		$fits = Fit::all();
		foreach ($fits as $fit)
		{
			if (stristr($fit->ship_dna, ':' . $id . ':'))
			{
				$tooltip .= $fit->name . "<br>";
			}
		}
		echo $tooltip;

	}

	/**
	 * Convert EFT fitting format into DNA-like string (http://wiki.eveonline.com/en/wiki/Ship_DNA).
	 * This code adapted from Fuzzysteve's Ship.js (https://github.com/fuzzysteve/Ship.js).
	 */
	public function makeDNA($fitting)
	{

		$fittingarray = preg_split ('/$\R?^/m', $fitting);
		$shipdetails = explode(",", $fittingarray[0], 2);

		if (count($shipdetails) > 40 || (substr($shipdetails[0], 0, 1) != "["))
		{
			echo "Fitting name too big or incorrect format.";
 			die();
		}

		$shipname = trim($shipdetails[0], "[");
		$mods = array();
		$mods[$shipname] = 1;
		$inner = array_shift($fittingarray);

		foreach ($fittingarray as $line)
		{
			if (preg_match('/^(.*) x(\d+)$/', trim($line), $matches))
			{
				if (array_key_exists($matches[1], $mods))
				{
					$mods[$matches[1]] += $matches[2];
				}
 				else
				{
					$mods[$matches[1]] = $matches[2];
				}
			}
			else if (!preg_match('/\[/', $line))
			{
				$line = trim($line);
				$moduledetail = explode(",", $line, 2);
				if (array_key_exists($moduledetail[0], $mods))
				{
					$mods[$moduledetail[0]]++;
				}
				else
				{
					$mods[$moduledetail[0]] = 1;
				}
				if (array_key_exists(1, $moduledetail))
				{
					if (array_key_exists(trim($moduledetail[1]), $mods))
					{
						$mods[trim($moduledetail[1])]++;
					}
					else
					{
						$mods[trim($moduledetail[1])] = 1;
					}
				}
			}
		}

		// Build a string of type IDs. This array also contains quantities, but we don't care about that at the moment.
		unset($mods['']);
		$mod_strings = '("' . implode('", "', array_keys($mods)) . '")';
		$items = Type::whereRaw('typeName IN ' . $mod_strings)->get();
		$dna = ':';
		foreach($items as $item)
		{
			$dna .= $item->typeID . ':';
		}

		// Return a list of type IDs.
		return $dna;

	}

}
