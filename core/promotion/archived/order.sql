CREATE TABLE `order` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`orderNo` varchar(50) NOT NULL DEFAULT '',
	`status` varchar(10) NOT NULL DEFAULT '',
	`libraryId` int(10) unsigned NOT NULL DEFAULT 0,
	`comments` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`orderNo`)
	,INDEX (`status`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`created`)
	,INDEX (`updated`)
	,INDEX (`libraryId`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

CREATE TABLE `orderitem` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`orderId` int(10) unsigned NOT NULL DEFAULT 0,
	`productId` int(10) unsigned NOT NULL DEFAULT 0,
	`unitPrice` double(10,4) unsigned NOT NULL DEFAULT '0.0000',
	`qty` int(10) unsigned NOT NULL DEFAULT 0,
	`totalPrice` double(10,4) unsigned NOT NULL DEFAULT '0.0000',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`orderId`)
	,INDEX (`productId`)
	,INDEX (`unitPrice`)
	,INDEX (`qty`)
	,INDEX (`totalPrice`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`created`)
	,INDEX (`updated`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

CREATE TABLE  `bmv`.`productstaticslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL DEFAULT '0',
  `libraryId` int(10) unsigned NOT NULL DEFAULT '0',
  `value` int(100) unsigned NOT NULL DEFAULT '0',
  `typeId` int(10) unsigned NOT NULL DEFAULT '0',
  `staticsId` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  KEY `typeId` (`typeId`),
  KEY `createdById` (`createdById`),
  KEY `updatedById` (`updatedById`),
  KEY `value` (`value`),
  KEY `libraryId` (`libraryId`),
  KEY `staticsId` (`staticsId`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;