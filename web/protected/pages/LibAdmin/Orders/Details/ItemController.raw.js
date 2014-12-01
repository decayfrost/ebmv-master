/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
	htmlIDs: {'orderDetailsDiv': ''}
	,_order: {} //the order object
	/**
	 * setting the HTML IDs
	 */
	,setHTMLIds: function(orderDetailsDiv) {
		this.htmlIDs.orderDetailsDiv = orderDetailsDiv;
		return this;
	}
	/**
	 * setting the order object
	 */
	,setOrder: function(order) {
		this._order = order;
		return this;
	}
	,_deleteItem: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.row = btn.up('.item-row');
		tmp.item = tmp.row.retrieve('data');
		tmp.me.postAjax(tmp.me.getCallbackId('delItem'), tmp.item, {
			'onLoading': function() {}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.item)
						return
					tmp.items = [];
					tmp.me._order.items.each(function(item){
						if(item.id !== tmp.result.item.id) {
							tmp.items.push(item);
						}
					});
					tmp.me._order.items = tmp.items;
					tmp.row.remove();
				} catch (e) {
					tmp.me.showModalbox('ERROR', e, true);
				}
			}
		})
		return tmp.me;
	}
	,_getItemsRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle === true ? true : false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.qty = (tmp.me._order.status !== 'OPEN' ? row.qty : new Element('div', {'class': 'input-group input-group-sm'})
			.insert({'bottom': new Element('input', {'class': 'form-control order-qty', 'save-order': 'item-qty', 'type': 'text', 'item-id': row.id, 'value': row.qty, 'style': 'padding: 4px;'}) })
			.insert({'bottom': new Element('span', {'class': 'input-group-btn'}) 
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger'})
					.update(new Element('span', {'class': 'glyphicon glyphicon-trash'})) 
					.observe('click', function(){
						if(!confirm('Do you want to delete this item from this order?'))
							return false;
						tmp.me._deleteItem(this);
					})
				}) 
			})
		);
		tmp.newDiv = new Element('tr', {'class': 'item-row'}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? new Element('span') 
					.insert({'bottom': new Element('input', {'type': 'checkbox', 'id': 'mark-all-marc'}) 
						.observe('click', function(){
							tmp.checked = this.checked;
							$$('[save-order=need-marc]').each(function(chkbox) {
								chkbox.checked = tmp.checked;
							})
						})
					})
					.insert({'bottom': new Element('label', {'for': 'mark-all-marc'}).update(' <small>MARC?</small>') })	
				: new Element('input', {'type': 'checkbox', 'save-order': 'need-marc'})
			) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Title' : row.product.title) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'ISBN' : (!row.product.attributes.isbn ? '' : row.product.attributes.isbn[0].attribute)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Author' : (!row.product.attributes.author ? '' : row.product.attributes.author[0].attribute)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Publisher' : (!row.product.attributes.publisher ? '' : row.product.attributes.publisher[0].attribute)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'PublishDate' : (!row.product.attributes.publish_date ? '' : row.product.attributes.publish_date[0].attribute)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? 'Qty' : tmp.qty ) })
		;
		return tmp.newDiv;
	}
	,_saveItem: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.me._signRandID(btn);
		tmp.data = {'id': tmp.me._order.id, 'comments': $F($$('[save-order=comments]').first()), 'items': []};
		$$('[save-order=item-qty]').each(function(item){
			tmp.data.items.push({'id': item.readAttribute('item-id'), 'qty': $F(item), 'needMARC': $(item).up('.item-row').down('[save-order=need-marc]').checked});
		});
		tmp.me.postAjax(tmp.me.getCallbackId('saveOrder'), tmp.data, {
			'onLoading': function() {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, false);
					if(!tmp.result.order)
						return;
					tmp.me.showModalBox('Success', "Order Saved Successfully!", true);
					window.location = document.URL;
				} catch(e) {
					tmp.me.showModalbox('ERROR', e, true);
				}
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
			}
		})
		return tmp.me;
	}
	/**
	 * displaying the order
	 */
	,displayOrder: function() {
		var tmp = {};
		tmp.me = this;
		tmp.dateString = '';
		if(tmp.me._order.submitDate !== '0001-01-01 00:00:00' && !tmp.me._order.submitDate.blank()) {
			tmp.date = tmp.me.loadUTCTime(tmp.me._order.submitDate);
			tmp.dateString = tmp.date.getDate() + '/' + (tmp.date.getMonth() * 1 + 1) + '/' + tmp.date.getFullYear();
		}
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'})
					.insert({'bottom': new Element('dl')
						.insert({'bottom': new Element('dt').update('Order No.:') })
						.insert({'bottom': new Element('dd').update(tmp.me._order.orderNo) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'})
					.insert({'bottom': new Element('dl')
						.insert({'bottom': new Element('dt').update('Order status.:') })
						.insert({'bottom': new Element('dd').update(tmp.me._order.status) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'})
					.insert({'bottom': new Element('dl')
						.insert({'bottom': new Element('dt').update('Order Date.:') })
						.insert({'bottom': new Element('dd').update(tmp.dateString ) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'})
					.insert({'bottom': new Element('dl')
						.insert({'bottom': new Element('dt').update('Order By.:') })
						.insert({'bottom': new Element('dd').update(tmp.me._order.submitBy && tmp.me._order.submitBy.person ? tmp.me._order.submitBy.person.fullname : '') })
					})
				})
			})
			.insert({'bottom': new Element('table', {'class': 'table table-striped table-hover'})
				.insert({'bottom': new Element('thead').update(tmp.me._getItemsRow({}, true) ) })
				.insert({'bottom': tmp.tbody = new Element('tbody') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-12'})
					.insert({'bottom': new Element('strong').update('comments:') })
					.insert({'bottom': tmp.me._order.status !== 'OPEN' ? new Element('div', {'class': 'panel panel-default'}).update(new Element('div', {'class': 'panel-body'}).update(tmp.me._order.comments) ) : new Element('textarea', {'class': 'form-control', 'save-order': 'comments'}).update(tmp.me._order.comments) })
				})
			});
		tmp.me._order.items.each(function(item){
			tmp.tbody.insert({'bottom': tmp.me._getItemsRow(item, false)  });
		});
		
		if(tmp.me._order.status === 'OPEN' ) {
			tmp.newDiv.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary pull-right col-sm-4', 'data-loading-text': 'saving ...'}) 
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-upload'}) }) 
					.insert({'bottom': ' Submit This Order' }) 
					.observe('click', function(){
						tmp.me._saveItem(this);
					})
				})
			});
		}
		$(tmp.me.htmlIDs.orderDetailsDiv).update(tmp.newDiv);
		return tmp.me;
	}
});