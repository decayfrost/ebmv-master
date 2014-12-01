/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
	resultDivId: ''
	,pagination: {'pageNo': 1, 'pageSize': 10}
	,borrowStatusId: ''
		
	,_getEmptyBookShelfInfo: function () {
		return new Element('div', {'class': 'infobox bg-info iconbtn'})
			.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-info-sign'}) })
			.insert({'bottom': new Element('span', {'class': 'btnname'})
				.insert({'bottom': '您的书架是空的/您的書架是空的' })
				.insert({'bottom': new Element('div').update('Your bookshelf is empty') })
			})
	}
	
	,changePage: function (btn, pageNo, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.me.pagination.pageNo = pageNo;
		tmp.me.pagination.pageSize = pageSize;
		jQuery(btn.id).button('loading');
		tmp.me.showBookShelf(false, function() {
			$(btn).up('.pagination_wrapper').remove();
		});
	}
	//getting the loading div
	,_getLoadingDiv: function() {
		return new Element('span', {'class': 'loading'})
			.insert({'bottom': new Element('img', {'src': '/themes/default/images/loading.gif'})})
			.insert({'bottom': 'Loading ...'});
	}
	
	//get pagination div
	,_getPaginationDiv: function(pagination) {
		var tmp = {};
		if(pagination.pageNumber >= pagination.totalPages)
			return;
		
		tmp.me = this;
		return new Element('div', {'class': 'pagination_wrapper'})
			.insert({'bottom': new Element('span', {'class': 'btn btn-primary iconbtn', 'id': 'get_more_btn', 'data-loading-text': '处理中/處理中/Processing...'})
				.insert({'bottom': new Element('span', {'class': 'btnname'})
					.insert({'bottom': '查看更多/查看更多' })
					.insert({'bottom': new Element('small').update('Get more') })
				})
				.observe('click', function() {
					tmp.me.changePage(this, pagination.pageNumber + 1, tmp.me.pagination.pageSize);
				})
			});
	}
	
	
	//show the products
	,showBookShelf: function(clear, afterFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.clear = (clear === true ? true : false);
		if(tmp.clear === true)
		{
			this.pagination.pageNo = 1;
			$(tmp.me.resultDivId).update(tmp.me._getLoadingDiv());
		}
		
		pageJs.postAjax(tmp.me.getCallbackId("getProducts"), {'pagination': tmp.me.pagination}, {
			'onLoading': function () { }
			,'onComplete': function(sender, param) {
				tmp.resultDiv = $(tmp.me.resultDivId);
				if(tmp.clear === true)
					tmp.resultDiv.update('');
				try {
					tmp.result = pageJs.getResp(param, false, true);
					if((!tmp.result.pagination || tmp.result.pagination.totalRows === 0 || tmp.result.items.size() === 0) && $(tmp.me.resultDivId).getElementsBySelector('.listitem').size() === 0)
						tmp.resultDiv.update(tmp.me._getEmptyBookShelfInfo());
					
					tmp.result.items.each(function(item){
						tmp.resultDiv.insert({'bottom': tmp.me._getProductListItem(item) });
					});
					
					if(tmp.result.pagination.pageNumber < tmp.result.pagination.totalPages)
						tmp.resultDiv.insert({'bottom':tmp.me._getPaginationDiv(tmp.result.pagination) });
				} catch (e) {
					tmp.resultDiv.insert({'bottom': tmp.me.getAlertBox('ERROR: ' + e).addClassName('alert-danger') });
				}
				if(typeof(afterFunc) === 'function')
					afterFunc();
			}
		});
		return this;
	}
	
	,removeItem: function(btn, itemId) {
		var tmp = {};
		tmp.me = this;
		if(!confirm('你确定要从书架中删除这本书吗？/ 你確定要從書架中刪除這本書嗎？\n You are removing this BOOK from your book shelf?\n\n继续/繼續/Continue?'))
			return;
		
		pageJs.postAjax(tmp.me.getCallbackId("removeProduct"), {'itemId': itemId, pagination: tmp.me.pagination}, {
			'onLoading': function () {
				jQuery(btn.id).button('loading');
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.result = pageJs.getResp(param, false, true);
					if(tmp.result.delItem && tmp.result.delItem.id) {
						tmp.itemRow = $(tmp.me.resultDivId).down('.listitem[item_id=' + tmp.result.delItem.id + ']');
						if(tmp.itemRow)
							tmp.itemRow.remove();
					}
					if(tmp.result.nextItem) {
						if($(tmp.me.resultDivId).down('.pagination_wrapper'))
							$(tmp.me.resultDivId).down('.pagination_wrapper').insert({'before': tmp.me._getProductListItem(tmp.result.nextItem) });
						else
							$(tmp.me.resultDivId).insert({'bottom': tmp.me._getProductListItem(tmp.result.nextItem)});
					}
					if($(tmp.me.resultDivId).getElementsBySelector('.listitem').size() === 0)
						$(tmp.me.resultDivId).update(tmp.me._getEmptyBookShelfInfo());
				} catch (e) {
					$(btn).insert({'before': tmp.me.getAlertBox('ERROR: ' + e).addClassName('alert-danger') });
					jQuery(btn.id).button('reset');
				}
			}
		});
		return this;
	}
	
	,borrowItem: function (btn, shelfItemId) {
		var tmp = {};
		tmp.me = this;
		if(!confirm('You are trying to borrow this BOOK. / 您正试图借这本书. / 您正試圖借這本書. \n\n Continue / 继续 / 繼續 ?'))
			return;
		
		pageJs.postAjax(tmp.me.getCallbackId("borrowItem"), {'itemId': shelfItemId}, {
			'onLoading': function () {
				$(btn).addClassName('disabled loading');
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.result = pageJs.getResp(param, false, true);
					if(tmp.result.item && tmp.result.item.id) {
						tmp.itemRow = $(tmp.me.resultDivId).down('.listitem[item_id=' + tmp.result.item.id + ']');
						if(tmp.itemRow)
							tmp.itemRow.replace(tmp.me._getProductListItem(tmp.result.item));
					}
					alert('You have successfully borrowed this book./ 您已成功借这本书. / 您已成功借這本書.');
				} catch (e) {
					$(btn).insert({'before': tmp.me.getAlertBox('ERROR: ' + e).addClassName('alert-danger') });
					$(btn).removeClassName('disabled loading');
				}
			}
		});
		return this;
	}
	
	,returnItem: function (btn, shelfItemId) {
		var tmp = {};
		tmp.me = this;
		if(!confirm('You are trying to return this BOOK./ 你正在试图返回本书. / 你正在試圖返回本書。 \n\n Continue / 继续 / 繼續 ?'))
			return;
		
		pageJs.postAjax(tmp.me.getCallbackId("returnItem"), {'itemId': shelfItemId}, {
			'onLoading': function () {
				$(btn).addClassName('disabled loading');
			}
		,'onComplete': function(sender, param) {
			try {
				tmp.result = pageJs.getResp(param, false, true);
				if(tmp.result.item && tmp.result.item.id) {
					tmp.itemRow = $(tmp.me.resultDivId).down('.listitem[item_id=' + tmp.result.item.id + ']');
					if(tmp.itemRow)
						tmp.itemRow.replace(tmp.me._getProductListItem(tmp.result.item));
				}
				alert('You have successfully returned this book. / 您已成功返回本书。 / 您已成功返回本書。');
			} catch (e) {
				$(btn).insert({'before': tmp.me.getAlertBox('ERROR: ' + e).addClassName('alert-danger') });
				$(btn).removeClassName('disabled loading');
			}
		}
		});
		return this;
	}
	
	,_getBtnDiv: function(shelfItem) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'row btns'})
			.insert({'bottom': new Element('div', {'class': 'col-xs-12'})
			//add remove btn
			.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-sm iconbtn', 'id': 'removebtn_' + shelfItem.id, 'data-loading-text': '处理中/處理中/Processing...'})
				.insert({'bottom': new Element('span', {'class': 'btnname'})
					.update('从我的书架中删除 / 從我的書架中刪除')
					.insert({'bottom': new Element('small').update('Remove From My Bookshelf') })
				})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
				.observe('click', function(){
					tmp.me.removeItem(this, shelfItem.id);
				})
			})
		});
//		if(shelfItem.status === tmp.me.borrowStatusId) {
//			//add return book btn
//			tmp.newDiv.insert({'bottom': new Element('span', {'class': 'imgBtn returnBookBtn', 'title': 'è¿˜ä¹¦ / é‚„æ›¸ Return This Book'})
//				.observe('click', function(){
//					tmp.me.returnItem(this, shelfItem.id);
//				})
//			});
//		} else {
//			//add return book btn
//			tmp.newDiv.insert({'bottom': new Element('span', {'class': 'imgBtn borrowBookBtn', 'title': 'å€Ÿä¹¦ / å€Ÿæ›¸ Borrow This Book'})
//				.observe('click', function(){
//					tmp.me.borrowItem(this, shelfItem.id);
//				})
//			});
//		}
		return tmp.newDiv;
	}
	
	,_getProductListItemDetailsRow: function(title1, content1, title2, content2) {
		return new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-6'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-5'})
						.insert({'bottom': new Element('strong')
							.insert({'bottom': title1 })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-7'}).update(content1)})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-6'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-5'})
						.insert({'bottom': new Element('strong')
							.insert({'bottom': title2 })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-7'}).update(content2)})
				})
			});
	}
	
	//get product list item
	,_getProductListItem: function(shelfItem) {
		var tmp = {};
		tmp.me = this;
		if(!shelfItem.product || !shelfItem.product.id)
			return null;
		
		tmp.productDiv = new Element('div', {'class': 'panel panel-default listitem', 'item_id': shelfItem.id}).store('data', shelfItem)
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row nodefault plistitem'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 col-sm-3'})
						.insert({'bottom': new Element('div', {'class': 'thumbnail'})
							.insert({'bottom': new Element('a', {'href': tmp.me.getProductDetailsUrl(shelfItem.product.id)})
								.insert({'bottom': tmp.me._getProductImgDiv(shelfItem.product.attributes.image_thumb || null).addClassName('img-thumbnail') })
							})
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-8 col-sm-9'})
						.insert({'bottom': new Element('a', {'class': 'product_title', 'href': tmp.me.getProductDetailsUrl(shelfItem.product.id)})
							.insert({'bottom': new Element('h4')
								.update(shelfItem.product.title) 
							})
						})
						.insert({'bottom': tmp.me._getProductListItemDetailsRow('Author:', (shelfItem.product.attributes.author ? tmp.me._getAttrString(shelfItem.product.attributes.author).join(' ') : ''),
									'ISBN:', (shelfItem.product.attributes.isbn ? tmp.me._getAttrString(shelfItem.product.attributes.isbn).join(' ') : '') 	)
						})
						.insert({'bottom': tmp.me._getProductListItemDetailsRow('Publisher:', (shelfItem.product.attributes.publisher ? tmp.me._getAttrString(shelfItem.product.attributes.publisher).join(' ') : ''),
								'Pub. Date:', (shelfItem.product.attributes.publish_date ? tmp.me._getAttrString(shelfItem.product.attributes.publish_date).join(' ') : '') 	)
						})
						.insert({'bottom': tmp.me._getProductListItemDetailsRow('Borrowed @:', (shelfItem.borrowTime ? shelfItem.borrowTime : ''),
								'Expiry:', (shelfItem.expiryTime ? shelfItem.expiryTime : '') 	)
						})
						.insert({'bottom': tmp.me._getBtnDiv(shelfItem) })
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('small')
								.insert({'bottom': shelfItem.product.attributes.description ? tmp.me._getAttrString(shelfItem.product.attributes.description).join(' ') : '' })
							})
						})
					})
				})
			})
		;
		return tmp.productDiv;
	}
	,_getAttrString: function(attArray){
		return attArray.map(function(attr) { return attr.attribute || '';});
	}
});