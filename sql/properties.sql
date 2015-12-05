CREATE TABLE `apimo_properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` int(11) DEFAULT NULL,
  `agency_id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `price` decimal(12, 2) DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key_1` (`agency_id`),
  KEY `key_2` (`category`),
  CONSTRAINT `FK_1` FOREIGN KEY (`agency_id`) REFERENCES `apimo_agencies` (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;