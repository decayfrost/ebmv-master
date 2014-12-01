insert into `producttype` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
    (4, 'course',  1, NOW(), 100, NOW(), 100);

insert into `supplier` (`id`, `name`, `connector`,`active`, `created`, `createdById`, `updated`, `updatedById`) values
(8, '新东方', 'SC_XinDongFang', 1, NOW(), 100, NOW(), 100);

insert into `supplierinfo` (`supplierId`, `typeId`,`value`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
    (8, 1, 'http://library.koolearn.com/externalRemoteService', 1, NOW(), 100, NOW(), 100),
    (8, 2, 'http://library.koolearn.com/externalRemoteService', 1, NOW(), 100, NOW(), 100),
    (8, 4, '1', 1, NOW(), 100, NOW(), 100),
    (8, 5, '4', 1, NOW(), 100, NOW(), 100),
    (8, 6, '/var/www/html/protected/asset/supplier8/', 1, NOW(), 100, NOW(), 100),
    (8, 8, '4', 1, NOW(), 100, NOW(), 100),
    (8, 7, 'B9B8F6684C45A15E9B775800717BCE6A', 1, NOW(), 100, NOW(), 100),
    (8, 9, '0', 1, NOW(), 100, NOW(), 100);