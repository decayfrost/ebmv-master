insert into `supplier` (`id`, `name`, `connector`,`active`, `created`, `createdById`, `updated`, `updatedById`) values
(9, 'Apabi', 'SC_Apabi', 1, NOW(), 100, NOW(), 100);

insert into `supplierinfo` (`supplierId`, `typeId`,`value`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
    (9, 1, 'http://paper.apabi.com/servlet/getPagePicsServlet', 1, NOW(), 100, NOW(), 100),
    (9, 2, 'http://www.apabi.com/', 1, NOW(), 100, NOW(), 100),
    (9, 4, '1', 1, NOW(), 100, NOW(), 100),
    (9, 5, '2', 1, NOW(), 100, NOW(), 100),
    (9, 6, '/var/www/html/protected/asset/supplier9/', 1, NOW(), 100, NOW(), 100),
    (9, 8, '2,3', 1, NOW(), 100, NOW(), 100),
    (9, 9, '0', 1, NOW(), 100, NOW(), 100);