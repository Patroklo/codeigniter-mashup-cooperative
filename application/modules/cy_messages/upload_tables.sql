-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         5.5.35-0ubuntu0.13.10.2 - (Ubuntu)
-- SO del servidor:              debian-linux-gnu
-- HeidiSQL Versión:             8.3.0.4808
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Volcando estructura para tabla pruebas.forums
CREATE TABLE IF NOT EXISTS `forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `permission` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL,
  `stores_posts` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.


-- Volcando estructura para tabla pruebas.forums_posts
CREATE TABLE IF NOT EXISTS `forums_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `message_text` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `parent_id` int(11) NOT NULL,
  `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creation_date` datetime NOT NULL,
  `edited` tinyint(4) NOT NULL DEFAULT '0',
  `edition_date` datetime NOT NULL,
  `edition_ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `last_answer` int(11) NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `stick` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`,`user_id`,`parent_id`,`deleted`),
  KEY `last_answer` (`last_answer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.


-- Volcando estructura para tabla pruebas.messages
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_type` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no_type',
  `reference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `anonymous_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `message_url` varchar(300) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `message_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `message_text` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `parent_id` int(11) NOT NULL,
  `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creation_date` datetime NOT NULL,
  `edited` tinyint(4) NOT NULL DEFAULT '0',
  `edition_date` datetime NOT NULL,
  `edition_ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`,`user_id`,`parent_id`,`deleted`),
  KEY `message_type` (`message_type`),
  KEY `message_url` (`message_url`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;



INSERT INTO `permissions` (`id`, `name`) VALUES (NULL, 'blog_post_permission');

