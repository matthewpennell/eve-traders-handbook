## EVE Traders Handbook

The **EVE Traders Handbook** is a multi-purpose web application for traders, importers, and manufacturers in the popular space MMORPG [EVE Online](http://www.eveonline.com/).

It allows players to see details of the ships, modules, drones and ammunition that are being lost by their fellow capsuleers within the systems/regions they select. In this way, traders can identify those items likely to be in high demand, and manufacturers can compare the cost of importing versus mining and manufacturing items locally.

It makes use of the EVE API, the [zKillboard](http://zkillboard.com/) API, and the [public CREST](https://developers.eveonline.com/) API to track player losses and item costs. It also makes extensive use of the EVE [Static Data Export](https://developers.eveonline.com/resource/static-data-export) database dumps ([fuzzwork](https://www.fuzzwork.co.uk/dump/latest/)) and [Image Export Collection](https://developers.eveonline.com/resource/image-export-collection) to display basic information and icons.

### Changelog

* 27 April 2016 - Updated CREST API call due to withdrawal of public endpoint
* 12 March 2016 - converted all eve-central.com API calls to use CREST
* 12 July 2015 - Add ability to include shipping costs
* 9 May 2015 - Add Ajax-powered selection of systems and alliances
* 18 April 2015 - Added search function
* 14 February 2014 - Added Material Efficiency multiplier
* 3 January 2015 - Added profit projections
* 22 December 2014 - Added T2 invention support
* 16 November 2014 - Initial release

### Requirements

**ETH** runs on PHP5.4+ and uses the [Laravel PHP framework](http://laravel.com/) and its associated systems (Blade templates, Composer for dependency management, Eloquent ORM). It requires a MySQL database.

### Installation

Full [installation instructions can be found on the wiki](https://github.com/matthewpennell/eve-traders-handbook/wiki/Installation).

## Problems/Bugs?

Report bugs on [this project's GitHub Issues page](https://github.com/matthewpennell/eve-traders-handbook/issues), or via the in-game chat channel "EVE Traders Handbook".

If you enjoy using this software, please consider making an in-game ISK donation to [Shei Bushaava](https://gate.eveonline.com/Profile/Shei%20Bushaava).
