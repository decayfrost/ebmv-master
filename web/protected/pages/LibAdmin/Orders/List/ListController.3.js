/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
	htmlIDs: {'totalCountDiv': '', 'listingDiv': ''}
	,pagination: {'pageNo': 1, 'pageSize': 30}
	,order: {} //the order object
	/**
	 * Getting the HTML IDs
	 */
	,setHTMLIDs: function(totalCountDiv, listingDiv) {
		this.htmlIDs.totalCountDiv = totalCountDiv;
		this.htmlIDs.listingDiv = listingDiv;
		return this;
	}
	/**
	 * Open the order details page
	 */
	,_openDetailsPage: function(row) {
		var tmp = {};
		tmp.me = this;
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: '/libadmin/order/' + row.id + '.html',
			'beforeClose'	    : function() {
				if($(tmp.me.htmlIDs.listingDiv).down('.item-row[item-id=' + row.id + ']'))
					$(tmp.me.htmlIDs.listingDiv).down('.item-row[item-id=' + row.id + ']').replace(tmp.me._getResultTableRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._order, false));
			}
 		});
		return tmp.me;
	}
	//get pagination div
	,_getPaginationDiv: function(pagination) {
		var tmp = {};
		if(pagination.pageNumber >= pagination.totalPages)
			return;
		
		tmp.me = this;
		return new Element('div', {'class': 'pagination_wrapper pull-right'}).insert({'bottom': tmp.me._getPaginationBtn('查看更多 / 查看更多<br />Get more', pagination.pageNumber + 1) });
	}
	/**
	 * Getting the next page
	 */
	,changePage: function (btn, pageNo, pageSize) {
		var tmp = {};
		this.pagination.pageNo = pageNo;
		this.pagination.pageSize = pageSize;
		$(btn).update('Getting more ....').writeAttribute('disabled', true);
		this.getResult(false, function() {
			$(btn).up('.pagination_wrapper').remove();
		});
	}
	/**
	 * getting the pagination button
	 */
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
	/**
	 * Getting a row for displaying the result
	 */
	,_getResultTableRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.dateString = '';
		if(row.submitDate !== '0001-01-01 00:00:00' && !row.submitDate.blank()) {
			tmp.date = tmp.me.loadUTCTime(row.submitDate);
			tmp.dateString = tmp.date.getDate() + '/' + (tmp.date.getMonth() * 1 + 1) + '/' + tmp.date.getFullYear();
		}
		tmp.row = new Element('tr', {'class': 'item-row', 'item-id': row.id}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(row.orderNo) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(row.status) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(row.items.size()) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.dateString ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(row.submitBy && row.submitBy.person ? row.submitBy.person.fullname : '') })
			.insert({'bottom': new Element(tmp.tag).update(row.comments) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'})
				.insert({'bottom': new Element('a', {'title': 'Click to View the Details', 'href': 'javascript: void(0);'})
					.insert({'bottom': new Element('span', {'class': row.status === 'OPEN' ? 'glyphicon glyphicon-pencil' : 'glyphicon glyphicon-eye-open'}) })
					.observe('click', function() {
						tmp.me._openDetailsPage(row);
					})
				})
			})
			;
		return tmp.row;
	}
	/**
	 * Getting the list of the products
	 */
	,getResult: function(reset, afterFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.me.postAjax(tmp.me.getCallbackId('getItems'), {'pagination': tmp.me.pagination}, {
			'onLoading': function () {
				if(tmp.reset === true)
				{
					tmp.me.pagination.pageNo = 1;
					$(tmp.me.htmlIDs.listingDiv).update(tmp.me.getLoadingImg());
				}
			}
			,'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.items)
						return;
					
					if(tmp.reset === true) {
						$(tmp.me.htmlIDs.listingDiv).update('');
						$(tmp.me.htmlIDs.totalCountDiv).update(tmp.result.pagination.totalRows);
					}
					tmp.result.items.each(function(item) {
						$(tmp.me.htmlIDs.listingDiv).insert({'bottom': tmp.me._getResultTableRow(item, false) });
					});
					$(tmp.me.htmlIDs.listingDiv).insert({'bottom': tmp.me._getPaginationDiv(tmp.result.pagination) });
				} catch (e) {
					$(tmp.me.htmlIDs.listingDiv).update(tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger') );
				}
				
				if(typeof(afterFunc) === 'function')
					afterFunc();
			}
		});
		return tmp.me;
	}
});