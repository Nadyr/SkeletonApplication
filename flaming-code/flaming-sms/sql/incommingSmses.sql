CREATE TABLE `incommingSmses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phone` char(10) COLLATE utf8_general_ci NOT NULL,
  `content` text COLLATE utf8_general_ci NOT NULL,
  `shortcode` varchar(10) COLLATE utf8_general_ci NOT NULL,
  `receivedTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;