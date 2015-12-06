CREATE TABLE `apimo_referential` (
  `type` varchar(100) CHARACTER SET utf8 NOT NULL,
  `value` varchar(100) CHARACTER SET utf8 NOT NULL,
  `culture` varchar(7) CHARACTER SET utf8 NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `names` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`type`,`value`,`culture`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;