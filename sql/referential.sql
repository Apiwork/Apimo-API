CREATE TABLE `apimo_referential` (
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `culture` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `names` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`type`,`value`,`culture`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;