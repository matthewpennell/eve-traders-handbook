-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Generation Time: Apr 18, 2015 at 04:30 PM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `alliances`
--

DROP TABLE IF EXISTS `alliances`;
CREATE TABLE IF NOT EXISTS `alliances` (
  `id` int(11) NOT NULL,
  `allianceName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `corporations`
--

DROP TABLE IF EXISTS `corporations`;
CREATE TABLE IF NOT EXISTS `corporations` (
  `id` int(11) NOT NULL,
  `corporationName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filters`
--

DROP TABLE IF EXISTS `filters`;
CREATE TABLE IF NOT EXISTS `filters` (
  `categoryID` int(11) NOT NULL,
  `categoryName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `iconID` int(11) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `filters`
--

INSERT INTO `filters` (`categoryID`, `categoryName`, `iconID`, `is_default`, `created_at`, `updated_at`) VALUES
(6, 'Ship', NULL, 1, '2014-11-27 16:29:21', '2015-04-09 12:38:50'),
(7, 'Module', 67, 1, '2014-11-27 16:29:22', '2015-04-09 12:38:50'),
(8, 'Charge', NULL, 0, '2014-11-27 16:29:21', '2014-11-27 16:29:21'),
(18, 'Drone', 0, 1, '2014-11-27 16:29:22', '2015-04-09 12:38:50');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `killID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `typeName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `categoryName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `metaGroupName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allowManufacture` tinyint(1) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kills`
--

DROP TABLE IF EXISTS `kills`;
CREATE TABLE IF NOT EXISTS `kills` (
  `killID` int(11) NOT NULL,
  `solarSystemID` int(11) NOT NULL,
  `characterID` int(11) NOT NULL,
  `characterName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allianceID` int(11) NOT NULL,
  `corporationID` int(11) NOT NULL,
  `shipTypeID` int(11) NOT NULL,
  `killTime` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materialEfficiency`
--

DROP TABLE IF EXISTS `materialEfficiency`;
CREATE TABLE IF NOT EXISTS `materialEfficiency` (
  `typeID` int(11) NOT NULL,
  `materialEfficiency` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ships`
--

DROP TABLE IF EXISTS `ships`;
CREATE TABLE IF NOT EXISTS `ships` (
  `id` int(11) NOT NULL,
  `shipName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prices`
--

DROP TABLE IF EXISTS `prices`;
CREATE TABLE IF NOT EXISTS `prices` (
  `typeID` int(11) NOT NULL,
  `regions` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `system` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `volume` int(11) NOT NULL,
  `avg` int(11) NOT NULL,
  `max` int(11) NOT NULL,
  `min` int(11) NOT NULL,
  `median` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `typeID` (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profits`
--

DROP TABLE IF EXISTS `profits`;
CREATE TABLE IF NOT EXISTS `profits` (
  `typeID` int(11) NOT NULL,
  `manufactureCost` float(11,2) NOT NULL,
  `profitIndustry` float(11,2) NOT NULL,
  `profitImport` float(11,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `label`, `value`, `created_at`, `updated_at`) VALUES
(1, 'api_key_id', 'Key ID', '', '0000-00-00 00:00:00', '2014-12-11 12:48:43'),
(2, 'api_key_verification_code', 'Verification Code', '', '0000-00-00 00:00:00', '2014-12-11 12:48:43'),
(3, 'api_key_character_id', 'Character', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'home_region_id', 'Home Region', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'home_station_id', 'Home Station', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'alliances', 'Alliances (comma-separated)', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'regions', 'Regions (comma-separated)', '', '0000-00-00 00:00:00', '2015-04-09 07:40:12'),
(8, 'shipping_cost', 'Shipping cost per m³', '0', '0000-00-00 00:00:00', '2015-04-09 07:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `fits`
--

CREATE TABLE `fits` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `eft_fitting` text COLLATE utf8_unicode_ci NOT NULL,
  `ship_dna` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
