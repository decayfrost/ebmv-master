############################ add role table
ALTER TABLE `role` AUTO_INCREMENT = 10;
insert into `role`(`id`, `name`,`active`, `created`, `createdById`, `updated`, `updatedById`) values 
	(1, 'Guest', 1, NOW(), 100, NOW(), 100),
	(2, 'Reader', 1, NOW(), 100, NOW(), 100),
	(10, 'Admin', 1, NOW(), 100, NOW(), 100);

############################ add person table
ALTER TABLE `person` AUTO_INCREMENT = 100;
insert into `person`(`id`, `firstName`, `lastName`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'guest', 'user', 1, NOW(), 100, NOW(), 100),
	(10, 'test', 'user', 1, NOW(), 100, NOW(), 100),
	(11, 'test', 'YL user', 1, NOW(), 100, NOW(), 100),
	(100, 'admin', 'system', 1, NOW(), 100, NOW(), 100);


############################ add user table
ALTER TABLE `useraccount` AUTO_INCREMENT = 100;
insert into `useraccount`(`id`, `username`, `password`, `personId`, `libraryId`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, md5('guestusername'), 'disabled', 1, 1, 1, NOW(), 100, NOW(), 100),
	(10, 'test_user', sha1('test_pass'), 10, 1, 1, NOW(), 100, NOW(), 100),
	(11, 'testuser_yl', sha1('testpass_yl'), 11, 1, 1, NOW(), 100, NOW(), 100),
	(100, 'admin', sha1('admin'), '100', 1, 1, NOW(), 100, NOW(), 100);

############################ add role_useraccount table
insert into `role_useraccount`(`userAccountId`, `roleId`, `created`, `createdById`) values 
	(1, 1, NOW(), 100),
	(10, 2, NOW(), 100),
	(11, 2, NOW(), 100),
	(100, 10, NOW(), 100);
	
############################ add language table
insert into `language` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, '简体中文',  1, NOW(), 100, NOW(), 100),
	(2, '繁体中文',  1, NOW(), 100, NOW(), 100),
	(3, 'English',  1, NOW(), 100, NOW(), 100);
	
############################ add languagecode table
insert into `languagecode`(`languageId`, `code`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'zh-CN', 1, NOW(), 100, 1, 100),
	(1, 'zh_CN', 1, NOW(), 100, 1, 100),
	(2, 'zh-hk', 1, NOW(), 100, 1, 100),
	(2, 'zh_hk', 1, NOW(), 100, 1, 100),
	(2, 'zh-tw', 1, NOW(), 100, 1, 100),
	(2, 'zh_tw', 1, NOW(), 100, 1, 100),
	(3, 'en_us', 1, NOW(), 100, 1, 100),
	(3, 'en-us', 1, NOW(), 100, 1, 100);
	
############################ add productattributetype table
insert into `productattributetype` (`name`, `code`, `searchable`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	('Author', 'author', 1, 1, NOW(), 100, NOW(), 100),
	('ISBN', 'isbn', 1, 1, NOW(), 100, NOW(), 100),
	('Publisher', 'publisher', 1, 1, NOW(), 100, NOW(), 100),
	('PublishDate', 'publish_date', 1, 1, NOW(), 100, NOW(), 100),
	('Number Of Words', 'no_of_words', 1, 1, NOW(), 100, NOW(), 100),
	('Image', 'image', 1, 1, NOW(), 100, NOW(), 100),
	('ImageThumbnail', 'image_thumb', 1, 1, NOW(), 100, NOW(), 100),
	('Description', 'description', 1, 1, NOW(), 100, NOW(), 100),
	('Cno', 'cno', 1, 1, NOW(), 100, NOW(), 100),
	('Cip', 'cip', 1, 1, NOW(), 100, NOW(), 100);

############################ add producttype table
insert into `producttype` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'book',  1, NOW(), 100, NOW(), 100),
	(2, 'newspaper',  1, NOW(), 100, NOW(), 100),
	(3, 'magazine',  1, NOW(), 100, NOW(), 100);

############################ add productstaticstype table
insert into `productstaticstype` (`id`, `name`, `code`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'Click Rate', 'no_of_clicks',  1, NOW(), 100, NOW(), 100),
	(2, 'Borrow Rate', 'no_of_borrows',  1, NOW(), 100, NOW(), 100);

############################ add productstaticstype table
insert into `supplierinfotype` (`id`, `name`, `code`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
(1, 'The URL to import', 'import_url',  1, NOW(), 100, NOW(), 100),
(2, 'The URL to view online', 'view_url',  1, NOW(), 100, NOW(), 100),
(3, 'The URL to download', 'download_url',  1, NOW(), 100, NOW(), 100),
(4, 'Default Language ID', 'default_lang_id',  1, NOW(), 100, NOW(), 100),
(5, 'Default Type ID', 'default_product_type_id',  1, NOW(), 100, NOW(), 100),
(6, 'Default Image Directory for products', 'default_img_dir',  1, NOW(), 100, NOW(), 100),
(7, 'Supplier Key', 'skey',  1, NOW(), 100, NOW(), 100),
(8, 'Supplied Product Type Ids', 'stype_ids',  1, NOW(), 100, NOW(), 100),
(9, 'Supplier Partner ID', 'partner_id',  1, NOW(), 100, NOW(), 100);

############################ add supplier table
insert into `supplier` (`id`, `name`, `connector`,`active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'Xin Hua', 'SC_XinHua', 1, NOW(), 100, NOW(), 100),
	(2, 'Tai Wan', 'SC_TW', 1, NOW(), 100, NOW(), 100),
	(3, '大公報', 'SC_TaKungPao', 1, NOW(), 100, NOW(), 100),
	(4, '文匯報', 'SC_WenHuiPo', 1, NOW(), 100, NOW(), 100),
	(5, '新民晚报', 'SC_XinMinWanBao', 1, NOW(), 100, NOW(), 100),
	(6, '新民周刊', 'SC_XinMinZhouKan', 1, NOW(), 100, NOW(), 100);

############################ add supplierinfo table
insert into `supplierinfo` (`supplierId`, `typeId`,`value`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
    ('1', 1, 'http://au.xhestore.com/AULibService.asmx?wsdl', 1, NOW(), 100, NOW(), 100),
    ('1', 2, 'http://au.xhestore.com/book/readbook', 1, NOW(), 100, NOW(), 100),
    ('1', 3, 'http://au.xhestore.com/book/downloadbook', 1, NOW(), 100, NOW(), 100),
    ('1', 4, '1', 1, NOW(), 100, NOW(), 100),
    ('1', 5, '1', 1, NOW(), 100, NOW(), 100),
    ('1', 6, '/var/www/html/protected/asset/supplier1/', 1, NOW(), 100, NOW(), 100),
    ('1', 7, '8985A41E813AE00A78EE4AACF606F643', 1, NOW(), 100, NOW(), 100),
    ('1', 8, '1', 1, NOW(), 100, NOW(), 100),
    
    (2, 1, 'http://m2.ebook4rent.tw/pont/1.00/{SiteID}/{method}/', 1, NOW(), 100, NOW(), 100),
    (2, 4, 2, 1, NOW(), 100, NOW(), 100),
    (2, 5, '1', 1, NOW(), 100, NOW(), 100),
    (2, 6, '/var/www/html/protected/asset/supplier2/', 1, NOW(), 100, NOW(), 100),
    (2, 8, '1,3', 1, NOW(), 100, NOW(), 100),
    (2, 2, 'http://m2.ebook4rent.tw/pont/1.00/{SiteID}/launchViewer/', 1, NOW(), 100, NOW(), 100),
    (2, 7, 'B985A41E813AE00A78EE4AACF606F643', 1, NOW(), 100, NOW(), 100),
    (2, 9, '0', 1, NOW(), 100, NOW(), 100),
    
    ('3', 2, 'http://news.takungpao.com.hk/paper/{productKey}.html', 1, NOW(), 100, NOW(), 100),
    ('3', 3, 'http://paper.takungpao.com/resfile/PDF/{productKey}/ZIP/{productKey}_pdf.zip', 1, NOW(), 100, NOW(), 100),
    ('3', 4, '2', 1, NOW(), 100, NOW(), 100),
    ('3', 5, '2', 1, NOW(), 100, NOW(), 100),
    ('3', 8, '2', 1, NOW(), 100, NOW(), 100),
    ('3', 6, '/var/www/html/protected/asset/supplier3/', 1, NOW(), 100, NOW(), 100),

    ('4', 2, 'http://pdf.wenweipo.com/{productKey}/pdf1.htm', 1, NOW(), 100, NOW(), 100),
    ('4', 4, '2', 1, NOW(), 100, NOW(), 100),
    ('4', 5, '2', 1, NOW(), 100, NOW(), 100),
    ('4', 8, '2', 1, NOW(), 100, NOW(), 100),
    ('4', 6, '/var/www/html/protected/asset/supplier4/', 1, NOW(), 100, NOW(), 100),
    
    ('5', 2, 'http://xmwb.xinmin.cn/html/{productKey}/node_1.htm', 1, NOW(), 100, NOW(), 100),
    ('5', 4, '1', 1, NOW(), 100, NOW(), 100),
    ('5', 5, '2', 1, NOW(), 100, NOW(), 100),
    ('5', 6, '/var/www/html/protected/asset/supplier5/', 1, NOW(), 100, NOW(), 100),
    ('5', 8, '2', 1, NOW(), 100, NOW(), 100),
    
    ('6', 2, 'http://xmzk.xinmin.cn/html/{productKey}/node_1.htm', 1, NOW(), 100, NOW(), 100),
    ('6', 4, '1', 1, NOW(), 100, NOW(), 100),
    ('6', 5, '3', 1, NOW(), 100, NOW(), 100),
    ('6', 6, '/var/www/html/protected/asset/supplier6/', 1, NOW(), 100, NOW(), 100),
    ('6', 8, '3', 1, NOW(), 100, NOW(), 100);

############################ add library table
insert into `library` (`id`, `name`, `connector`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'test lib', 'LC_Local', 1, NOW(), 100, NOW(), 100),
	(2, 'Bankstown Library', 'LC_Bankstown',1, NOW(), 100, NOW(), 100),
    (3, 'Yarra Plenty Library', 'LC_SIP2',1, NOW(), 100, NOW(), 100),
    (4, 'Whitehorse Library', 'LC_SIP2',1, NOW(), 100, NOW(), 100);

############################ add libraryinfotype table
insert into `libraryinfotype` (`id`, `name`, `code`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'The Australian Library Code', 'aus_code',  1, NOW(), 100, NOW(), 100),
	(2, 'The url of the library', 'lib_url',  1, NOW(), 100, NOW(), 100),
	(3, 'The timezone of the library', 'lib_timezone',  1, NOW(), 100, NOW(), 100),
	(4, 'The theme of the library', 'lib_theme',  1, NOW(), 100, NOW(), 100),
	(5, 'The running mode of the library system', 'running_mode',  1, NOW(), 100, NOW(), 100),
	(6, 'The SOAP WSDL URL', 'soap_wsdl',  1, NOW(), 100, NOW(), 100),
    (7, 'The SIP2 host addr[203.23.231.1:8627]', 'sip2_host',  1, NOW(), 100, NOW(), 100);
	
############################ add libraryinfo table
insert into `libraryinfo` (`libraryId`, `typeId`,`value`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
    ('1', '1', '37', 1, NOW(), 100, NOW(), 100),
    ('1', '2', 'localhost', 1, NOW(), 100, NOW(), 100),
    ('1', '2', 'ebmv.com.au', 1, NOW(), 100, NOW(), 100),
    ('1', '2', 'www.ebmv.com.au', 1, NOW(), 100, NOW(), 100),
    ('1', '3', 'Australia/Melbourne', 1, NOW(), 100, NOW(), 100),
    ('1', '4', 'bankstown', 1, NOW(), 100, NOW(), 100),
    ('1', '5', '1', 1, NOW(), 100, NOW(), 100),
    
    ('2', '1', 'NBANK', 1, NOW(), 100, NOW(), 100),
    ('2', '2', 'bankstownlib.ebmv.com.au', 1, NOW(), 100, NOW(), 100),
    ('2', '3', 'Australia/Melbourne', 1, NOW(), 100, NOW(), 100),
    ('2', '4', 'bankstown', 1, NOW(), 100, NOW(), 100),
    ('2', '5', '1', 1, NOW(), 100, NOW(), 100),
    ('2', '6', 'http://library.bankstown.nsw.gov.au/Libero/LiberoWebServices.WebOpac.cls', 1, NOW(), 100, NOW(), 100),
    
    ('3', '1', 'UNK', 1, NOW(), 100, NOW(), 100),
    ('3', '2', 'yarraplenty.ebmv.com.au', 1, NOW(), 100, NOW(), 100),
    ('3', '3', 'Australia/Melbourne', 1, NOW(), 100, NOW(), 100),
    ('3', '4', 'bankstown', 1, NOW(), 100, NOW(), 100),
    ('3', '5', '1', 1, NOW(), 100, NOW(), 100),
    ('3', '7', '206.187.32.61:8163', 1, NOW(), 100, NOW(), 100),
    
    ('4', '1', 'UNK', 1, NOW(), 100, NOW(), 100),
    ('4', '2', 'whlib.ebmv.com.au', 1, NOW(), 100, NOW(), 100),
    ('4', '3', 'Australia/Melbourne', 1, NOW(), 100, NOW(), 100),
    ('4', '4', 'bankstown', 1, NOW(), 100, NOW(), 100),
    ('4', '5', '1', 1, NOW(), 100, NOW(), 100),
    ('4', '7', '203.89.253.70:6021', 1, NOW(), 100, NOW(), 100);
    
############################ add libraryownstype table
insert into `libraryownstype` (`id`, `code`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
    ('1', 'ReadOnline', 'The copies the libary owns for online view', 1, NOW(), 100, NOW(), 100),
    ('2', 'Download', 'The copies the libary owns for download', 1, NOW(), 100, NOW(), 100),
    ('3', 'BorrowTimes', 'The times the libary user can borrow', 1, NOW(), 100, NOW(), 100);


