ALTER TABLE  `order` ADD  `submitDate` datetime NULL DEFAULT NULL AFTER  `libraryId` ,
ADD INDEX (  `submitDate` ) ;

ALTER TABLE  `order` ADD  `submitById` int(10) unsigned NULL DEFAULT NULL AFTER  `submitDate` ,
ADD INDEX (  `submitById` ) ;

ALTER TABLE  `orderitem` ADD  `needMARCRecord` bool NOT NULL DEFAULT 0 AFTER  `totalPrice` ,
ADD INDEX (  `needMARCRecord` ) ;

