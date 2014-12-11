## EVE Traders Handbook

The **EVE Traders Handbook** is a multi-purpose web application for traders, importers, and manufacturers in the popular space MMORPG [EVE Online](http://www.eveonline.com/).

It allows players to see details of the ships, modules, drones and ammunition that are being lost by their fellow capsuleers within the systems/regions they select. In this way, traders can identify those items likely to be in high demand, and manufacturers can compare the cost of importing versus mining and manufacturing items locally.

It makes use of the EVE API, the [zKillboard](http://zkillboard.com/) API, and the [eve-central.com](http://eve-central.com/) API to track player losses and item costs. It also makes extensive use of the EVE [Static Data Export](https://developers.eveonline.com/resource/static-data-export) database dumps ([fuzzwork](https://www.fuzzwork.co.uk/dump/latest/)) and [Image Export Collection](https://developers.eveonline.com/resource/image-export-collection) to display basic information and icons.

### Requirements

**ETH** runs on PHP5.3 and uses the [Laravel PHP framework](http://laravel.com/) and its associated systems (Blade templates, Composer for dependency management, Eloquent ORM). It requires a MySQL database.

### Installation

Clone or download this repository to your local machine or live server.

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

Now import the eth_database_setup.sql file into your database to create the custom tables used by the application.
