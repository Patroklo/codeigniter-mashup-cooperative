-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 16-07-2014 a las 17:49:09
-- Versión del servidor: 5.5.35
-- Versión de PHP: 5.5.10-1+deb.sury.org~precise+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `portafolius`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `multilanguage_data`
--

CREATE TABLE IF NOT EXISTS `multilanguage_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inner_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `text_data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inner_id` (`inner_id`,`field_id`,`language_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=524 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
