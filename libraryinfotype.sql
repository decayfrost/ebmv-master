-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2014-12-01 08:57:38
-- 服务器版本： 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ebmv`
--

-- --------------------------------------------------------

--
-- 表的结构 `libraryinfotype`
--

CREATE TABLE IF NOT EXISTS `libraryinfotype` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `code` varchar(50) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `createdById` (`createdById`),
  KEY `updatedById` (`updatedById`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- 转存表中的数据 `libraryinfotype`
--

INSERT INTO `libraryinfotype` (`id`, `name`, `code`, `active`, `created`, `createdById`, `updated`, `updatedById`) VALUES
(1, 'The Australian Library Code', 'aus_code', 1, '2014-01-07 08:47:41', 100, '2014-01-07 08:47:41', 100),
(2, 'The url of the library', 'lib_url', 1, '2014-01-07 08:47:41', 100, '2014-01-07 08:47:41', 100),
(3, 'The timezone of the library', 'lib_timezone', 1, '2014-01-07 08:47:41', 100, '2014-01-07 08:47:41', 100),
(4, 'The theme of the library', 'lib_theme', 1, '2014-01-07 08:47:41', 100, '2014-01-07 08:47:41', 100),
(5, 'The running mode of the library system', 'running_mode', 1, '2014-01-07 08:47:41', 100, '2014-01-07 08:47:41', 100),
(6, 'The SOAP WSDL URL', 'soap_wsdl', 1, '2014-02-09 12:22:04', 100, '2014-02-09 12:22:04', 100),
(7, 'The SIP2 host addr[203.23.231.1:8627]', 'sip2_host', 1, '2014-02-12 10:17:03', 100, '2014-02-12 10:17:03', 100),
(8, 'The Library Borrow Limit', 'borrow_limit', 1, '2014-07-25 06:35:34', 100, '2014-07-25 06:35:34', 100),
(9, 'The Library Max Loan Time', 'max_loan_time', 1, '2014-07-25 06:35:34', 100, '2014-07-25 06:35:34', 100),
(10, 'The Library Gross Profit Margin', 'gross_profit_margin', 1, '2014-11-25 09:04:56', 100, '2014-11-25 01:04:56', 100);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
