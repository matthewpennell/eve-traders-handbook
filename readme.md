## EVE Traders Handbook

The **EVE Traders Handbook** is a multi-purpose web application for traders, importers, and manufacturers in the popular space MMORPG [EVE Online](http://www.eveonline.com/).

It allows players to see details of the ships, modules, drones and ammunition that are being lost by their fellow capsuleers within the systems/regions they select. In this way, traders can identify those items likely to be in high demand, and manufacturers can compare the cost of importing versus mining and manufacturing items locally.

It makes use of the EVE API, the [zKillboard](http://zkillboard.com/) API, and the [eve-central.com](http://eve-central.com/) API to track player losses and item costs. It also makes extensive use of the EVE [Static Data Export](https://developers.eveonline.com/resource/static-data-export) database dumps ([fuzzwork](https://www.fuzzwork.co.uk/dump/latest/)) and [Image Export Collection](https://developers.eveonline.com/resource/image-export-collection) to display basic information and icons.

### Requirements

**ETH** runs on PHP5.3 and uses the [Laravel PHP framework](http://laravel.com/) and its associated systems (Blade templates, Composer for dependency management, Eloquent ORM). It requires a MySQL database.

### Installation

Clone or download this repository to your local machine or live server. Create a MySQL database, and update the host, database, username and password values in `app/config/database.php`.

As the source data for all EVE Online assets is rather large, they are not provided with this repository. Download the individual .sql.bz2 tables listed below from [www.fuzzwork.co.uk/dump/latest](https://www.fuzzwork.co.uk/dump/latest/), unzip them, and import them into your database:

* dgmTypeAttributes
* eveIcons
* industryActivityMaterials
* industryActivitySkills
* invCategories
* invGroups
* invMarketGroups
* invMetaGroups
* invMetaTypes
* invTypeMaterials
* invTypes
* mapSolarSystems

Now import the `eth_database_setup.sql` file into your database to create the custom tables used by the application.

### Setup

Visit `www.yoursite.com/settings` to enter some basic information about yourself and the area that you are interested in. You'll need to create an API key on [the EVE Online API Key Management site](https://community.eveonline.com/support/api-key/) first.

* **Key ID** - your EVE Online API key
* **Verification Code** - your EVE Online API verification code
* **Systems** - a comma-separated list of the system IDs that you want to monitor for losses
* **Alliances** - a comma-separated list of the alliance IDs that you want to monitor for losses
* **Character ID** - the EVE character ID of the character you are using for T2 invention
* **Default filters** - select the types of items that you want the app to show by default (this list will grow as more lost items are tracked)

Now visit `wwww.yoursite.com/import/zkillboard` to run the first import of kills/losses. Note that the zKillboard API is restricted to 200 kills per request.

### Cron

To enable regular imports of new kills/losses, you should create a cronjob (or other means of scheduled task) that regularly loads `www.yoursite.com/import/zkillboard`. An hourly task should be more than sufficient for all but the most active PvP alliances.

## Problems/Bugs?

Report bugs on [this project's GitHub Issues page](https://github.com/matthewpennell/eve-traders-handbook/issues), or via the in-game chat channel "EVE Traders Handbook".

If you enjoy using this software, please consider making an in-game ISK donation to [Shei Bushaava](https://gate.eveonline.com/Profile/Shei%20Bushaava).
