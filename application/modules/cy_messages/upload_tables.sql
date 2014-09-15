--
-- Estructura de tabla para la tabla `forums`
--

CREATE TABLE IF NOT EXISTS `forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `permission` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL,
  `stores_posts` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `forums_posts`
--

CREATE TABLE IF NOT EXISTS `forums_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_title` varchar(255) NOT NULL,
  `message_text` text NOT NULL,
  `parent_id` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `creation_date` datetime NOT NULL,
  `edited` tinyint(4) NOT NULL DEFAULT '0',
  `edition_date` datetime NOT NULL,
  `edition_ip` varchar(50) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `last_answer` int(11) NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `stick` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`,`user_id`,`parent_id`,`deleted`),
  KEY `last_answer` (`last_answer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_title` varchar(255) NOT NULL,
  `message_text` text NOT NULL,
  `parent_id` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `creation_date` datetime NOT NULL,
  `edited` tinyint(4) NOT NULL DEFAULT '0',
  `edition_date` datetime NOT NULL,
  `edition_ip` varchar(50) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`,`user_id`,`parent_id`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


INSERT INTO `pruebas`.`permissions` (`id`, `name`) VALUES (NULL, 'blog_post_permission');

