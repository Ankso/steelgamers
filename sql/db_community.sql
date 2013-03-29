-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.5.18 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2013-03-26 18:36:44
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping database structure for community
CREATE DATABASE IF NOT EXISTS `community` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `community`;


-- Dumping structure for table community.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User unique ID',
  `username` varchar(25) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username (not real name)',
  `password_sha1` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'User password in SHA1',
  `email` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT 'User e-mail',
  `ip_v4` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Last used IPv4',
  `ip_v6` varchar(39) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Last used IPv6',
  `is_online` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 if the user is online, else 0',
  `last_login` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT 'Time and date of the last user login.',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Whereas the account has been activated from the e-mail or not.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `id` (`id`,`username`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='General data about the users (name, mail, date of birth, etc...)';

-- Dumping data for table community.users: ~5 rows (approximately)
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password_sha1`, `email`, `ip_v4`, `ip_v6`, `is_online`, `last_login`, `active`) VALUES
	(16, 'Ankso', '66365614226465d9c4431dcf4662fe57509c2055', 'misterankso@gmail.com', '127.0.0.1', NULL, 1, '2013-03-26 18:35:21', 1),
	(18, 'JORGITO_YKE', 'bba2d1bec283dd3b90add09797a9235b08069064', 'georgitoneutron@yahoo.es', '83.165.9.91', NULL, 1, '2013-03-26 01:55:25', 1),
	(19, 'Killate', 'b5816518975eafd6efad3b93e917ae0b6b8c7b02', 'leocar93@gmail.com', '2.141.129.244', NULL, 1, '2013-03-25 04:44:46', 1),
	(21, 'Skyline', '05e967bec5f8f3500d3f2c5e14ee352988c8b709', 'skyline777.minecraft@gmail.com', '178.60.156.198', NULL, 1, '2013-03-26 01:26:15', 1),
	(23, 'Seldon', '31baa782be2a12e55a05ab254794a0d5e8d6588a', 'mrankso@hotmail.com', '127.0.0.1', NULL, 0, '2013-03-26 18:32:29', 1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;


-- Dumping structure for table community.users_email_verification
DROP TABLE IF EXISTS `users_email_verification`;
CREATE TABLE IF NOT EXISTS `users_email_verification` (
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User unique ID.',
  `verification_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The hash that is sent to the user''s email.',
  `sended` datetime NOT NULL COMMENT 'When the e-mail was sended.',
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `FK_USERID_USERS` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Email verification system.';

-- Dumping data for table community.users_email_verification: ~0 rows (approximately)
DELETE FROM `users_email_verification`;
/*!40000 ALTER TABLE `users_email_verification` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_email_verification` ENABLE KEYS */;


-- Dumping structure for table community.users_ranks
DROP TABLE IF EXISTS `users_ranks`;
CREATE TABLE IF NOT EXISTS `users_ranks` (
  `user_id` bigint(20) unsigned NOT NULL,
  `rank_mask` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'A mask representing the rank on each of the web game areas. The first number is the rank in the global webpage, and it has priority over all.',
  KEY `FK_RANKS_USERS` (`user_id`),
  CONSTRAINT `FK_RANKS_USERS` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Store user''s ranks';

-- Dumping data for table community.users_ranks: ~5 rows (approximately)
DELETE FROM `users_ranks`;
/*!40000 ALTER TABLE `users_ranks` DISABLE KEYS */;
INSERT INTO `users_ranks` (`user_id`, `rank_mask`) VALUES
	(23, '22222222'),
	(16, '77777777'),
	(18, '22222222'),
	(21, '22222222'),
	(19, '22222222');
/*!40000 ALTER TABLE `users_ranks` ENABLE KEYS */;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
