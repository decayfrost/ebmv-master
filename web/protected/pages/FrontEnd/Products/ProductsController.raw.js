/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
	
	resultDivId: '', //the result div for the product list
	getProductsBtn: '', //the callbackId for getting the products
	pagination: {'pageNo': 1, 'pageSize': 10},
	searchCriteria: {'searchString': '', 'categoryIds': [], 'searchOpt': '', 'searchCat' : '', 'language' : '', 'productType' : ''},
	getProductItemFunc: '_getProductGridItem',
	searchProductUrl: '/products/search/{searchTxt}', //the searching product url
	
	//constructor
	initialize: function(resultDivId, getProductsBtn) {
		this.resultDivId = resultDivId;
		this.getProductsBtn = getProductsBtn;
	}

	//show the products
	,showProducts: function(clear, afterFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.clear = (clear === true ? true : false);
		if(tmp.clear === true)
		{
			this.pagination.pageNo = 1;
			$(tmp.me.resultDivId).update(tmp.me._getLoadingDiv());
		}
		pageJs.postAjax(this.getProductsBtn, {'pagination': tmp.me.pagination, 'searchCriteria':  tmp.me.searchCriteria}, {
			'onLoading': function () {
			}
			,'onComplete': function(sender, param) {
				if(tmp.clear === true)
				{
					tmp.list = (tmp.me.getProductItemFunc === '_getProductGridItem' ? new Element('div', {'class': 'row'}) : new Element('ul', {'class': 'media-list'}) );
					tmp.list.addClassName('plist');
					if(!tmp.me.searchCriteria.searchString.blank()) {
						tmp.list.insert({'top': new Element('h4').update('搜索结果 / 搜索結果 / Search result for: ' + tmp.me.searchCriteria.searchString)});
					}
					$(tmp.me.resultDivId).update(tmp.list);
				}
				try {
					tmp.result = pageJs.getResp(param, false, true);
					if(!tmp.result.pagination || tmp.result.pagination.totalRows === 0)
						throw 'Nothing found!';
					
					tmp.list = $(tmp.me.resultDivId).down('.plist');
					tmp.result.products.each(function(item){
						tmp.list.insert({'bottom': tmp.me[tmp.me.getProductItemFunc](item) });
					});
					$(tmp.me.resultDivId).insert({'bottom': tmp.me._getPaginationDiv(tmp.result.pagination) });
				} catch (e) {
					$(tmp.me.resultDivId).insert({'bottom': tmp.me.getAlertBox('ERROR: ' + e).addClassName('alert-danger') });
				}
				if(typeof(afterFunc) === 'function')
					afterFunc();
			}
		});
		return this;
	}
	
	//getting the loading div
	,_getLoadingDiv: function() {
		return new Element('span', {'class': 'loading'})
			.insert({'bottom': new Element('img', {'src': '/images/loading.gif'})})
			.insert({'bottom': 'Loading ...'});
	}
	
	//get pagination div
	,_getPaginationDiv: function(pagination) {
		var tmp = {};
		if(pagination.pageNumber >= pagination.totalPages)
			return;
		
		tmp.me = this;
		return new Element('div', {'class': 'pagination_wrapper pull-right'}).insert({'bottom': tmp.me._getPaginationBtn('查看更多 / 查看更多<br />Get more', pagination.pageNumber + 1) });
	}
	
	,changePage: function (btn, pageNo, pageSize) {
		var tmp = {};
		this.pagination.pageNo = pageNo;
		this.pagination.pageSize = pageSize;
		$(btn).update('Getting more ....').writeAttribute('disabled', true);
		this.showProducts(false, function() {
			$(btn).up('.pagination_wrapper').remove();
		});
	}
	
	,_getPaginationBtn: function (txt, pageNo) {
		var tmp = {};
		tmp.me = this;
		return new Element('button', {'class': 'btn btn-primary', 'type': 'button'})
			.update(txt)
			.observe('click', function() {
				tmp.me.changePage(this, pageNo, tmp.me.pagination.pageSize);
			})
		;
	}
	
	//get product list item
	,_getProductListItem: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.productDiv = new Element('div', {'class': 'row nodefault plistitem'})
			.insert({'bottom': new Element('div', {'class': 'col-xs-3'})
				.insert({'bottom': new Element('div', {'class': 'thumbnail'})
					.insert({'bottom': new Element('a', {'href': tmp.me.getProductDetailsUrl(product.id)})
						.insert({'bottom': tmp.me._getProductImgDiv(product.attributes.image_thumb || null).addClassName('img-thumbnail') })
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-xs-9'})
				.insert({'bottom': new Element('a', {'class': 'product_title', 'href': tmp.me.getProductDetailsUrl(product.id)})
					.insert({'bottom': new Element('h4')
						.update(product.title) 
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-sm-5'})
								.insert({'bottom': new Element('strong')
									.insert({'bottom': 'Author:' })
								})
							})
							.insert({'bottom': new Element('div', {'class': 'col-sm-7'}).update(product.attributes.author ? tmp.me._getAttrString(product.attributes.author).join(' ') : '')})
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-sm-5'})
								.insert({'bottom': new Element('strong')
									.insert({'bottom': 'ISBN:' })
								})
							})
							.insert({'bottom': new Element('div', {'class': 'col-sm-7'}).update(product.attributes.isbn ? tmp.me._getAttrString(product.attributes.isbn).join(' ') : '')})
						})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-sm-5'})
								.insert({'bottom': new Element('strong')
									.insert({'bottom': 'Publisher:' })
								})
							})
							.insert({'bottom': new Element('div', {'class': 'col-sm-7'}).update(product.attributes.publisher ? tmp.me._getAttrString(product.attributes.publisher).join(' ') : '')})
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-sm-5'})
								.insert({'bottom': new Element('strong')
									.insert({'bottom': 'Pub. Date:' })
								})
							})
							.insert({'bottom': new Element('div', {'class': 'col-sm-7'}).update(product.attributes.publish_date ? tmp.me._getAttrString(product.attributes.publish_date).join(' ') : '')})
						})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('small')
						.insert({'bottom': product.attributes.description ? tmp.me._getAttrString(product.attributes.description).join(' ') : '' })
					})
				})
			})
		;
		return tmp.productDiv;
	}
	
	//get product grid item
	,_getProductGridItem: function(product) {
		return new Element('div', {"class": "col-md-3 col-sm-4 col-xs-6"}).update(this._getProductThumbnail(product));
	}
	
	,_getAttrString: function(attArray){
		return attArray.map(function(attr) { return attr.attribute || '';});
	}
	
	,searchProducts: function(searchBox) {
		var tmp = {};
		tmp.me = this;
		if($F(searchBox).blank()){
			tmp.me.markFormGroupError($(searchBox), '没什么可搜索 / 沒什麼可搜索<br />Nothing to Search.');
		} else {
			window.location= tmp.me.searchProductUrl.replace('{searchTxt}', $F(searchBox));
		}
		return tmp.me;
	}
});