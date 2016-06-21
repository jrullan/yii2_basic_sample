-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 21, 2016 at 08:48 AM
-- Server version: 5.5.49-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `basic`
--

-- --------------------------------------------------------

--
-- Table structure for table `child`
--

CREATE TABLE IF NOT EXISTS `child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_child_parent_idx` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `child`
--

INSERT INTO `child` (`id`, `name`, `description`, `parent_id`) VALUES
(7, 'One-To-Many Child Record', '', 3),
(8, 'Child #2', '', 3),
(9, 'Child #1', '', 4),
(10, 'Child #2', '', 4);

-- --------------------------------------------------------

--
-- Table structure for table `grandchild`
--

CREATE TABLE IF NOT EXISTS `grandchild` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  `child_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_grandchild_child1_idx` (`child_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `grandchild`
--

INSERT INTO `grandchild` (`id`, `name`, `description`, `child_id`) VALUES
(7, 'Block', '', 7),
(8, 'Grandchild #1', '', 9),
(9, 'Grandchild #1', '', 10),
(10, 'Grandchild #2', '', 10);

-- --------------------------------------------------------

--
-- Table structure for table `parent_data`
--

CREATE TABLE IF NOT EXISTS `parent_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  `parent_model_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_model_id_UNIQUE` (`parent_model_id`),
  KEY `fk_parent_data_parent_model1_idx` (`parent_model_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `parent_data`
--

INSERT INTO `parent_data` (`id`, `name`, `description`, `parent_model_id`) VALUES
(5, 'One-to-One Child Record', '', 3),
(6, 'Parent_data #2', '', 4);

-- --------------------------------------------------------

--
-- Table structure for table `parent_model`
--

CREATE TABLE IF NOT EXISTS `parent_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `parent_model`
--

INSERT INTO `parent_model` (`id`, `name`, `description`) VALUES
(3, 'Parent Record', ''),
(4, 'Parent_model #2', '');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `child`
--
ALTER TABLE `child`
  ADD CONSTRAINT `fk_child_parent` FOREIGN KEY (`parent_id`) REFERENCES `parent_model` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grandchild`
--
ALTER TABLE `grandchild`
  ADD CONSTRAINT `fk_grandchild_child1` FOREIGN KEY (`child_id`) REFERENCES `child` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `parent_data`
--
ALTER TABLE `parent_data`
  ADD CONSTRAINT `fk_parent_data_parent_model1` FOREIGN KEY (`parent_model_id`) REFERENCES `parent_model` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
