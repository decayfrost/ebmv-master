/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CrudPageJs(), {
	
	types: null //the attribute types
	
	,_geDl: function(dt, dd) {
		return new Element('dl')
			.insert({'bottom': new Element('dt').update(dt) })
			.insert({'bottom': new Element('dd').update(dd) });
	}
	
	,_getResultDiv: function(items, includetitlerow, itemrowindex) {
		var tmp = {};
		tmp.me = this;
		tmp.includetitlerow = (includetitlerow === false ? false : true);
		
		tmp.resultDiv = $(tmp.me.resultDivId);
		items.each(function(item) {
			tmp.resultDiv.insert({'bottom':  tmp.me._getItemRow(item, new Element('img', {'editId': item.id, 'class': 'btn', 'src': '/themes/default/images/edit.png', 'alt': 'EDIT'})
				.observe('click', function() {tmp.me.editItem(this); }), false)
			});
		});
	}

	,_getAttrString: function(attArray){
		return attArray.map(function(attr) { return attr.attribute || '';});
	}
	
	,_getInfoDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div');
		tmp.code = '';
		$H(item.attributes).each(function(itemArr) {
			tmp.attrCode = itemArr.key;
			if(typeof(itemArr.value) === 'object') {
				tmp.attrDiv = new Element('dl', {'class': 'dl-horizontal'}); 
				
				//getting the title div
				tmp.attrDiv.insert({'bottom': new Element('dt').update(itemArr.value[0].type.name + ':') });
				if(tmp.attrCode !== tmp.code) {
					tmp.code = tmp.attrCode;
				} 
				
				//getting the value div
				tmp.attrValeusDiv = new Element('dd'); 
				itemArr.value.each(function(attr) {
					console.debug(attr);
					tmp.attrValeusDiv.insert({'bottom': new Element('div', {'class': 'attr_value'}).update(attr.attribute) });
						
				});
				tmp.attrDiv.insert({'bottom': tmp.attrValeusDiv });
				tmp.div.insert({'bottom': tmp.attrDiv });
			};
		});
		return tmp.div;
	}

	,_getItemRow: function (product, option, isEdit) {
		var tmp = {};
		tmp.me = this;
		tmp.productDiv = new Element('div', {'class': 'list-group-item row nodefault plistitem'})
			.insert({'bottom': new Element('div', {'class': 'col-xs-3'})
				.insert({'bottom': tmp.me._getProductImgDiv(product.attributes.image_thumb || null).addClassName('img-thumbnail') })
			})
			.insert({'bottom': new Element('div', {'class': 'col-xs-9'})
				.insert({'bottom': new Element('h4')
					.update(product.title) 
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getInfoDiv(product) })
				})
			})
		;
		return tmp.productDiv;
	}
	
	,_getAdditionalProductInfo: function (item, isEdit) {
		var tmp = {};
		tmp.me = this;
		
		tmp.isEditing = (isEdit === true ? true : false);
		
		tmp.div = new Element('div', {'class': 'attrs_wrapper'});
		tmp.code = '';
		$H(item.attributes).each(function(itemArr) {
			tmp.attrCode = itemArr.key;
			if(typeof(itemArr.value) === 'object') {
				tmp.attrDiv = new Element('div', {'class': 'attr_wrapper'}); 
				
				//getting the title div
				tmp.attrDiv.insert({'bottom': new Element('span', {'class': 'attr_name inlineblock'}).update(itemArr.value[0].type.name) });
				if(tmp.attrCode !== tmp.code) {
					tmp.code = tmp.attrCode;
				} 
				
				//getting the value div
				tmp.attrValeusDiv = new Element('span', {'class': 'attr_values_wrapper inlineblock'}); 
				itemArr.value.each(function(attr) {
					if(tmp.isEditing === false)
						tmp.attrValeusDiv.insert({'bottom': new Element('div', {'class': 'attr_value' }).update(attr.attribute) });
					else
						tmp.attrValeusDiv.insert({'bottom': new Element('input', {'type': 'text', 'class' : 'attr_value_edit rdcrnr lightBrdr', 'value' : attr.attribute}) });
						
				});
				tmp.attrDiv.insert({'bottom': tmp.attrValeusDiv });
				
				tmp.div.insert({'bottom': tmp.attrDiv });
			};
		});
		return tmp.div;
	}
	
	,showEditPanel: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.editProductId = $(btn).readAttribute('editId');
		tmp.btnSpan = $(btn).up('span.col.btns');
		tmp.mainItem = tmp.btnSpan.up('div.row.singleRowDiv').retrieve('main_item');
		
		tmp.option = new Element('span', {'class' : 'col btns'}) 
						.insert({'bottom' : new Element('img', {'class' : 'editBtn', 'src': '/themes/default/images/save.png', 'alt': 'Save', 'title' : 'Save'})
							.observe('click', function() {tmp.me.saveEditedItem(this); }) })
						.insert({'bottom' : new Element('img', {'class' : 'cancelBtn', 'src': '/themes/default/images/cancel.gif', 'alt': 'Cancel', 'title' : 'Cancel'})
							.observe('click', function() {tmp.me.cancelEdit(this); })
						});
		
		tmp.editDiv = tmp.me._getItemRow(tmp.mainItem, tmp.option, true);
		
		tmp.btnSpan.up('div.row.singleRowDiv').insert({'bottom' : tmp.editDiv}).down('div.viewProductDiv').hide();
		
//		tmp.suk = tmp.btnSpan.previous('span.col.suk').innerHTML;
//		tmp.title = tmp.btnSpan.previous('span.col.title').innerHTML;
//		tmp.active = tmp.btnSpan.previous('span.col.active').innerHTML;
//		tmp.id = tmp.btnSpan.previous('span.col.id').innerHTML;
//		
//		tmp.activeOptions1 = new Element('option', {'value': 'true'}).update('true');
//		tmp.activeOptions2 = new Element('option', {'value': 'false'}).update('false');
//		
//		tmp.editPanel = new Element('div', {'class' : 'editProductDiv'})
//							.insert({'bottom' : new Element('span', {'class' : 'col id'}).update(tmp.id) })
//							.insert({'bottom' : new Element('span', {'class' : 'eSukLabel'}).update('Suk') })
//							.insert({'bottom' : new Element('input', {'type' : 'text', 'class' : 'eSukBox rdcrnr lightBrdr', 'value' : tmp.suk}) })
//							.insert({'bottom' : new Element('span', {'class' : 'eTitleLabel'}).update('Title') })
//							.insert({'bottom' : new Element('input', {'type' : 'text', 'class' : 'eTitleBox rdcrnr lightBrdr', 'value' : tmp.title}) })
//							.insert({'bottom' : new Element('select', {'class' : 'eActiveBox rdcrnr lightBrdr'}) 
//									.insert(tmp.activeOptions1)
//									.insert(tmp.activeOptions2)
//							})
//							.insert({'bottom' : new Element('span', {'class' : 'col btns'}) 
//								.insert({'bottom' : new Element('img', {'class' : 'editBtn', 'src': '/themes/default/images/save.png', 'alt': 'Save', 'title' : 'Save'})
//									.observe('click', function() {tmp.me.saveEditedItem(this); }) })
//								.insert({'bottom' : new Element('img', {'class' : 'cancelBtn', 'src': '/themes/default/images/cancel.gif', 'alt': 'Cancel', 'title' : 'Cancel'})
//									.observe('click', function() {tmp.me.cancelEdit(this); })
//								})
//						});
//		
//		tmp.btnSpan.up('div.singleRowDiv').insert({'bottom' : tmp.editPanel}).down('div.viewProductDiv').hide();
	}
	
	,cancelEdit: function(btn) {
		var tmp = {};
		tmp.me = this;
		
		tmp.me._hideShowAllEditPens(btn, true);
		tmp.rowDiv = $(btn).up('div.singleRowDiv');
		tmp.rowDiv.down('div.viewProductDiv').show();
		tmp.rowDiv.down('div.editProductDiv').remove();
	}
	
	,saveEditedItem: function(btn) {
		var tmp = {};
		tmp.me = this;
		try
		{
			tmp.editDiv = $(btn).up('div.editProductDiv');
			tmp.editProductId = tmp.editDiv.down('span.col.id').innerHTML;
			tmp.eSukValue = tmp.editDiv.down('span.eSukBox').getValue();
			//tmp.eTitleValue = tmp.editDiv.down('span.eTitleBox').value.trim();
			//tmp.eActiveValue = tmp.editDiv.down('span.eActiveBox').value;
			
			//console.debug(tmp.eSukValue);
			
			if(eSukValue === '' || eSukValue === undefined)
				throw 'SUK must be provided';
			if(eTitleValue === '' || eTitleValue === undefined)
				throw 'Title must be provided';
		}
		catch(e)
		{
			alert(e);
		}
		tmp.editDiv.down('span.col.id');
		
	}

});