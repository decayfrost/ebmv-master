DROP TABLE IF EXISTS `systemsettings`;
CREATE TABLE `systemsettings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`),
  KEY `createdById` (`createdById`),
  KEY `updatedById` (`updatedById`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

insert into `systemsettings` (`type`, `value`, `description`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	('def_borrow_limit', '10', 'The default value of the user borrowing limit', 1, NOW(), 100, NOW(), 100),
	('def_max_borrow_time', '+2 week', 'The max time of a borrow item before auto returned(PHP datetime modification string)', 1, NOW(), 100, NOW(), 100);


insert into `libraryinfotype` (`id`, `name`, `code`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(8, 'The Library Borrow Limit', 'borrow_limit',  1, NOW(), 100, NOW(), 100),
	(9, 'The Library Max Loan Time', 'max_loan_time',  1, NOW(), 100, NOW(), 100);