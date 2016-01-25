-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 25, 2016 at 05:52 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `micro`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `articles_lang`
--

CREATE TABLE IF NOT EXISTS `articles_lang` (
  `article_id` int(10) unsigned NOT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`article_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `alias` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `alias`) VALUES
(1, 'Гост', 'guest'),
(2, 'Потребител', 'user'),
(3, 'Администратор', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`) VALUES
(1, 'Български'),
(2, 'English');

-- --------------------------------------------------------

--
-- Table structure for table `nom_cities`
--

CREATE TABLE IF NOT EXISTS `nom_cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `nom_cities_lang`
--

CREATE TABLE IF NOT EXISTS `nom_cities_lang` (
  `city_id` int(10) unsigned NOT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`city_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `nom_countries`
--

CREATE TABLE IF NOT EXISTS `nom_countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `nom_countries`
--

INSERT INTO `nom_countries` (`id`) VALUES
(18),
(19);

-- --------------------------------------------------------

--
-- Table structure for table `nom_countries_lang`
--

CREATE TABLE IF NOT EXISTS `nom_countries_lang` (
  `country_id` int(10) unsigned NOT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`country_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `nom_countries_lang`
--

INSERT INTO `nom_countries_lang` (`country_id`, `language_id`, `name`) VALUES
(18, 2, 'ддд'),
(19, 2, 'ххх');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` binary(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `group_id`, `username`, `password`) VALUES
(1, 3, 'admin', '$2y$10$AZ8TR.gC6GWshZeY4entEuLMsdBkY9XE75Y8stcE0KmoZ6wUeCtQ2'),
(3, 2, 'user', '$2y$10$wLofpm23ZyCDgd2iB0HK7.M43GgeS7TJIZSmiHqPtIjB2b.tJW4cu'),
(4, 2, 'qwe', '$2y$10$ZMl/BztctCNfdQSZwN0hr.c8.9Ko7PtRsflYD/cIUVYS9wg2GyuHC');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles_lang`
--
ALTER TABLE `articles_lang`
  ADD CONSTRAINT `articles_lang_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nom_cities`
--
ALTER TABLE `nom_cities`
  ADD CONSTRAINT `nom_cities_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `nom_countries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nom_cities_lang`
--
ALTER TABLE `nom_cities_lang`
  ADD CONSTRAINT `nom_cities_lang_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `nom_cities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nom_countries_lang`
--
ALTER TABLE `nom_countries_lang`
  ADD CONSTRAINT `nom_countries_lang_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `nom_countries` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
