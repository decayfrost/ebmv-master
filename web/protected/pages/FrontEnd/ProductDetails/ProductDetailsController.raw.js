/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
	product: null //the product object
	,resultDivId: '' //where we are displaying product details
	,ownTypeIds: {} //the libraryowntypes
		
	,_joinAtts: function(attributes, name) {
		var tmp = {};
		tmp.attrs = [];
		if(attributes)
		{
			attributes.each(function(item) {
				tmp.attrs.push(item[name]);
			});
		}
		return tmp.attrs;
	}
		
	,_getAtts: function(attrcode, title, className, overRideContent) {
		var tmp = {};
		tmp.me = this;
		if(!tmp.me.product.attributes[attrcode] && !overRideContent)
			return [];
		
		tmp.overRideContent = (overRideContent || '');
		return new Element('div', {'class': 'col-xs-6 attr-wrapper'}).addClassName(className)
			.insert({'bottom': new Element('div', {'class': 'title'}).update(title) })
			.insert({'bottom': new Element('div', {'class': 'attribute'}).update((!tmp.overRideContent ? tmp.me._joinAtts(tmp.me.product.attributes[attrcode], 'attribute').join(', ') : tmp.overRideContent)) });
	}
	
	,_getLoadingImg: function (id) {
		return new Element('img', {'class': 'loadingImg', 'id': id, 'src': "/themes/images/loading.gif", 'width': '50px', 'height': '50px'});
	}
	
	,displayProduct: function() {
		var tmp = {};
		tmp.me = this;
		
		tmp.newDiv = new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-5 left'})
				.insert({'bottom':	tmp.me._getProductImgDiv((tmp.me.product.attributes.image_thumb || null), {'class': 'img-thumbnail'})
					.addClassName('img-thumbnail')
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-7 right'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('h3')
						.insert({'bottom': tmp.me.product.title })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getAtts('author', '<strong>作者/作者/Author:</strong>', 'author') })
					.insert({'bottom': tmp.me._getAtts('isbn', '<strong>ISBN:</strong>', 'product_isbn') })
					.insert({'bottom': tmp.me._getAtts('publisher', '<strong>出版社/出版社/Publisher:</strong>', 'product_publisher') })
					.insert({'bottom': tmp.me._getAtts('publish_date', '<strong>出版日期/出版日期/Publish Date:</strong>', 'product_publish_date') })
					.insert({'bottom': tmp.me._getAtts('languages', '<strong>语言/語言/Languages:</strong>', 'product_languages', tmp.me._joinAtts(tmp.me.product.languages, 'name').join(', ')) })
					.insert({'bottom': tmp.me._getAtts('no_of_words', '<strong>Length:</strong>', 'product_no_of_words') })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getLoadingImg('copies_display') })
				})
				.insert({'bottom': new Element('div', {'class': 'clearfix attr-wrapper'}) })
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div')
						.insert({'bottom': '<strong>内容简介/內容簡介/Description:</strong>' })
					})
					.insert({'bottom': new Element('em')	
						.insert({'bottom': tmp.me._joinAtts(tmp.me.product.attributes['description'], 'attribute').join(' ') })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'clearfix attr-wrapper'}) })
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
						.insert({'bottom':  tmp.me._getLoadingImg('view_btn') })
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
						.insert({'bottom': tmp.me._getLoadingImg('downloadBtn') })
					})
				})
			});
		$(tmp.me.resultDivId).update(tmp.newDiv);
		tmp.me._getCopies('copies_display', 'view_btn', 'downloadBtn');
		return this;
	}
	
	,_getCopies: function (readCopiesDisplayHolderId, readOnlineBtnId, downloadBtnId) {
		var tmp = {};
		tmp.me = this;
		tmp.copiesHolder = $(readCopiesDisplayHolderId).up('.row');
		tmp.btnsHolder = $(readOnlineBtnId).up('.row');
		tmp.me.postAjax(tmp.me.getCallbackId('getCopies'), {}, {
			'onLoading': function () {}
			,'onComplete': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					tmp.readCopies = tmp.downloadCopies = 'N/A';
					tmp.readBtn = new Element('span', {'class': 'btn btn-success iconbtn disabled popoverbtn visible-lg visible-md visible-sm visible-xs', 'id': 'preadonlinebtn', 'data-loading-text': "处理中/處理中/Processing ..."})
						.insert({'bottom': new Element('div', {'class': 'btnname'})
							.insert({'bottom': '在线阅读 / 在線閱讀'})
							.insert({'bottom': new Element('small').update('Read Online') })
						});
					tmp.downloadBtn = new Element('span', {'class': 'btn btn-success iconbtn disabled popoverbtn visible-lg visible-md visible-sm visible-xs', 'id': 'pdownloadbtn', 'data-loading-text': "处理中/處理中/Processing ..."})
						.insert({'bottom': new Element('div', {'class': 'btnname'})
							.insert({'bottom': '下载阅读 / 下載閱讀'})
							.insert({'bottom': new Element('small').update('Download') })
						});
					
					//getting the readonline url
					if(tmp.result.urls.viewUrl && tmp.result.copies[tmp.me.ownTypeIds.OnlineRead].avail * 1 > 0) {
						tmp.readCopies = tmp.result.copies[tmp.me.ownTypeIds.OnlineRead].avail + ' out of ' + tmp.result.copies[tmp.me.ownTypeIds.OnlineRead].total;
						tmp.readBtn.removeClassName('disabled')
							.observe('click', function(){
								return tmp.me._getLink(this, 'read');
							});
					}
					
					//getting the download url
					if(tmp.result.urls.downloadUrl && tmp.result.copies[tmp.me.ownTypeIds.Download].avail * 1 > 0) {
						tmp.downloadCopies = tmp.result.copies[tmp.me.ownTypeIds.Download].avail + ' out of ' + tmp.result.copies[tmp.me.ownTypeIds.Download].total;
						tmp.downloadBtn.removeClassName('disabled')
							.observe('click', function(){
								return tmp.me._getLink(this, 'download');
							});
					} 
					
					tmp.copiesHolder.update('')
						.insert({'bottom': tmp.me._getAtts('', '<strong>Online Read Copies:</strong>', 'online_read_copies', tmp.readCopies) })
						.insert({'bottom': tmp.me._getAtts('', '<strong>Download Copies:</strong>', 'download_copies', tmp.downloadCopies) });
					if(tmp.result.warningMsg) {
						tmp.btnsHolder.insert({'top': tmp.me.getAlertBox('<h4>Warning:</h4>', new Element('small').update(
								tmp.result.warningMsg.zh_CN + ' / ' + tmp.result.warningMsg.zh_TW + '<br />' + tmp.result.warningMsg.en)
							).addClassName('alert-warning') 
						});
					}
					if(tmp.result.stopMsg) {
						tmp.btnsHolder.insert({'top': tmp.me.getAlertBox('<h4>Error:</h4>', new Element('small').update(
								tmp.result.stopMsg.zh_CN + ' / ' + tmp.result.stopMsg.zh_TW + '<br />' + tmp.result.stopMsg.en)
						).addClassName('alert-danger') 
						});
					}
					$(readOnlineBtnId).replace(tmp.readBtn);
					$(downloadBtnId).replace(tmp.downloadBtn);
				} catch(e) {
					tmp.btnsHolder.insert({'top': tmp.me.getAlertBox('ERROR:', e).addClassName('alert-danger') });
				}
			}
		}, 120000);
	}
	
	,_openNewUrl: function(url) {
		var tmp = {};
		tmp.me = this;
		if(url.url)
			window.open(url.url);
		if(url.redirecturl)
			window.location = url.redirecturl;
		return this;
	}
	
	/**
	 * trying to get the read or download url
	 */
	,_getLink: function(btn, type) {
		var tmp = {};
		tmp.me = this;
		tmp.me.getUser(btn, function(){
				tmp.me.postAjax(tmp.me.getCallbackId('geturl'), {'type': type}, {
					'onLoading': function () {}
					,'onSuccess': function (sender, param) {
						try {
							tmp.result = tmp.me.getResp(param, false, true);
							tmp.me._openNewUrl(tmp.result);
						} catch(e) {
							$(btn).insert({'before': tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger') });
						}
					}
					,'onComplete': function(sender, param) {
						jQuery('#' + btn.id).button('reset');
					}
				}, 120000);
			}, function () {
				jQuery('#' + btn.id).button('loading');
			}
			, function () {
				jQuery('#' + btn.id).button('reset');
			}
		);
		
		return false;
	}
});