CREATE TABLE `apimo_properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` int(11) DEFAULT NULL,
  `agency_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `step_id` int(11) NOT NULL DEFAULT 1,
  `reference` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `category` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `subtype` int(11) NOT NULL,
  `city` varchar(100) CHARACTER SET utf8 NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `district` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `price_fees` decimal(12,2) DEFAULT NULL,
  `price_hide` tinyint(4) NOT NULL DEFAULT 0,
  `commission` decimal(12,2) DEFAULT NULL,
  `guarantee` decimal(12,2) DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `area` decimal(8,2) DEFAULT NULL,
  `rooms` decimal(3,1) DEFAULT NULL,
  `bedrooms` decimal(2,0) DEFAULT NULL,
  `sleeps` decimal(2,0) DEFAULT NULL,
  `view_type` int(11) DEFAULT NULL,
  `view_landscape` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `orientations` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `heating_device` int(11) DEFAULT NULL,
  `heating_access` int(11) DEFAULT NULL,
  `heating_type` int(11) DEFAULT NULL,
  `hot_water_device` int(11) DEFAULT NULL,
  `hot_water_access` int(11) DEFAULT NULL,
  `waste_water` int(11) DEFAULT NULL,
  `condition` int(11) DEFAULT NULL,
  `standing` int(11) DEFAULT NULL,
  `activities` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `services` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `proximities` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `tags` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key_agency` (`agency_id`),
  KEY `key_user` (`user_id`),
  KEY `key_category` (`category`),
  KEY `key_type` (`type`),
  KEY `key_subtype` (`subtype`),
  KEY `key_city` (`city_id`),
  CONSTRAINT `FK_properties_agencies` FOREIGN KEY (`agency_id`) REFERENCES `apimo_agencies` (`id`),
  CONSTRAINT `FK_properties_users` FOREIGN KEY (`user_id`) REFERENCES `apimo_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;