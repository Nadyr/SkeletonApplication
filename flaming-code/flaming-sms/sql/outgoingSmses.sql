CREATE TABLE `outgoingSmses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gatewayName` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `gatewayId` varchar(100) COLLATE utf8_general_ci DEFAULT NULL,
  `content` text COLLATE utf8_general_ci NOT NULL,
  `partCount` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `receiver` char(10) COLLATE utf8_general_ci NOT NULL,
  `senderId` varchar(20) COLLATE utf8_general_ci NOT NULL,
  `status` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `sendTime` datetime DEFAULT NULL,
  `sentTime` datetime DEFAULT NULL,
  `deliveredTime` datetime DEFAULT NULL,
  `parentSms_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gateway_id` (`gatewayId`),
  KEY `parentSms_id` (`parentSms_id`),
  KEY `gatewayName` (`gatewayName`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `outgoingSmses`
  ADD CONSTRAINT `outgoingSmsParent` FOREIGN KEY (`parentSms_id`) REFERENCES `outgoingSmses` (`id`) ON DELETE SET NULL;