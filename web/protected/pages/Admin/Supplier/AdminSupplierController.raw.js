/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CrudPageJs(), {
	
	types: null //the information types
	
	,_geDl: function(dt, dd) {
		return new Element('dl')
			.insert({'bottom': new Element('dt').update(dt) })
			.insert({'bottom': new Element('dd').update(dd) });
	}
	
	,_getItemRow: function (item, option) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div', {'class' : 'panel panel-success item', 'item_id': item.id}).store('item', item)
		.insert({'bottom' : new Element('div', {'class' : 'panel-heading'})
			.insert({'bottom' : new Element('div', {'class': "panel-title"})
				.insert({'bottom' : item.name })
				.insert({'bottom' : option.addClassName('pull-right') })
			})
		})
		.insert({'bottom' : new Element('div', {'class' : 'panel-body'})
			.insert({'bottom' : new Element('div', {'class' : 'row'})
				.insert({'bottom' : new Element('div', {'class' : 'col-xs-1'}).update(tmp.me._geDl('id:', item.id) ) })	
				.insert({'bottom' : new Element('div', {'class' : 'col-xs-6'}).update(tmp.me._geDl('Name:', item.name) ) })	
				.insert({'bottom' : new Element('div', {'class' : 'col-xs-4'}).update(tmp.me._geDl('Connector:', item.connector) ) })	
				.insert({'bottom' : new Element('div', {'class' : 'col-xs-1'}).update(tmp.me._geDl('Active?', item.istitle === true ? item.active : new Element('input', {'type': 'checkbox', 'checked': item.active, 'disabled': true})) ) })	
			})
		})
		.insert({'bottom' : tmp.me._getInfoDiv(item) });
		return tmp.div;
	}

	,_getItemRowEditBtn: function(item) {
		var tmp = {};
		tmp.me = this;
		return new Element('span', {'class': 'btn-group btn-group-xs'})
			.insert({'bottom': new Element('span', {'id': 'edit_btn' + item.id, 'class': 'btn btn-default', 'title': 'EDIT', 'data-loading-text': 'Processing...'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'})})
				.insert({'bottom': ' Edit' })
				.observe('click', function() {tmp.me.editItem(this); })
			})
			.insert({'bottom': new Element('span', {'id': 'del_btn' + item.id, 'class': 'btn btn-danger', 'title': 'DELETE', 'data-loading-text': 'Processing...'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'})})
				.insert({'bottom': ' Delete' })
				.observe('click', function() {tmp.me.delItems([item.id]); })
			});
	}
	
	,_hideShowAllEditPens: function () {
		var tmp = {};
		tmp.savePanel = $(this.resultDivId).down('.savePanel');
		if(tmp.savePanel)
			tmp.savePanel.down('.cancelBtn').click();
		return this;
	}

	,showEditPanel: function (btn, isNEW) {
		var tmp = {};
		tmp.me = this;
		if(isNEW === true) {
			$(tmp.me.resultDivId).down('.item.titleRow').insert({'after': tmp.me._getSavePanel({}, 'addDiv') });
		} else {
			tmp.row = $(btn).up('.item');
			tmp.row.replace(this._getSavePanel(tmp.row.retrieve('item'), 'editDiv'));
		}
		return this;
	}
	
	,_getResultDiv: function(items, includetitlerow, itemrowindex) {
		var tmp = {};
		tmp.me = this;
		tmp.includetitlerow = (includetitlerow === false ? false : true);
		
		tmp.resultDiv = new Element('div');
		if(tmp.includetitlerow === true) {
			tmp.resultDiv.insert({'bottom': new Element('p', {'class': 'item titleRow'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus-sign'})})
					.insert({'bottom':' Create NEW' })
					.observe('click', function(){ tmp.me.createItem(this); })
				})
			})
		}
		tmp.i = (itemrowindex || 0);
		items.each(function(item) {
			tmp.resultDiv.insert({'bottom':  tmp.me._getItemRow(item, tmp.me._getItemRowEditBtn(item))
				
			});
			tmp.i++;
		});
		return tmp.resultDiv;
	}
	
	,_getInfoDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div', {'class': 'list-group'});
		tmp.code = '';
		$H(item.info).each(function(itemArr) {
			tmp.attrCode = itemArr.key;
			if(typeof(itemArr.value) === 'object') {
				tmp.attrDiv = new Element('dl', {'class': 'list-group-item dl-horizontal'}); 
				
				//getting the title div
				tmp.attrDiv.insert({'bottom': new Element('dt').update(itemArr.value[0].type.name) });
				if(tmp.attrCode !== tmp.code) {
					tmp.code = tmp.attrCode;
				} 
				
				//getting the value div
				tmp.attrValeusDiv = new Element('dd'); 
				itemArr.value.each(function(attr) {
					tmp.attrValeusDiv.insert({'bottom': new Element('div', {'class': 'attr_value'}).update(attr.value) });
						
				});
				tmp.attrDiv.insert({'bottom': tmp.attrValeusDiv });
				tmp.div.insert({'bottom': tmp.attrDiv });
			};
		});
		return tmp.div;
	}
	
	,cancelEdit: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.item = $(btn).up('.savePanel').retrieve('item');
		//if this is for creating then remove this panel
		if(tmp.item.id === undefined || tmp.item.id === null) {
			$(btn).up('.savePanel').remove();
		} else {
			$(btn).up('.savePanel').replace(tmp.me._getItemRow(tmp.item, tmp.me._getItemRowEditBtn(tmp.item)));
		}
		return this;
	}
	
	,_collectSavePanel: function (saveBtn) {
		var tmp = {};
		tmp.me = this;
		tmp.hasError = false;
		tmp.data = {};
		tmp.savePanel = $(saveBtn).up('.savePanel');
		tmp.item = tmp.savePanel.retrieve('item');
		
		tmp.data['id'] = (tmp.item.id === null || tmp.item.id === undefined ? '' : tmp.item.id);
		//clearup all the error messages
		tmp.savePanel.down('.msgRow').update('');
		tmp.savePanel.getElementsBySelector('.has-error').each(function(div){ 
			div.removeClassName('has-error');
		});
		
		//getting all column for a library
		tmp.errMsg = '';
		tmp.savePanel.getElementsBySelector('[colname]').each(function(field) {
			tmp.fieldValue = $F(field);
			tmp.field = field.readAttribute('colname');
			if(tmp.fieldValue.blank() && field.readAttribute('noblank')) {
				$(field).up('.form-group').addClassName('has-error');
				tmp.errMsg += '<li>' + tmp.field + ' is required</li>';
			}
			tmp.data[tmp.field] = $F(field);
		});
		
		//getting all information
		tmp.attrs = [];
		tmp.savePanel.getElementsBySelector('[attr_id]').each(function(field) {
			tmp.fieldValue = $F(field);
			tmp.attrId = field.readAttribute('attr_id');
			tmp.attrTypeId = field.readAttribute('attr_type_id');
			if(tmp.fieldValue.blank() && field.readAttribute('noblank')) {
				$(field).up('.form-group').addClassName('has-error');
				tmp.errMsg += '<li>' + $(field).up('.form-group').down('.control-label').innerHTML + ' is required</li>';
			}
			tmp.attrs.push({'id': tmp.attrId, 'typeId': tmp.attrTypeId, 'value': tmp.fieldValue, 'active': (field.hasAttribute('deactivated') ? 0 : 1) });
		});
		tmp.data['info'] = tmp.attrs;
		
		if(!tmp.errMsg.blank()) {
			tmp.savePanel.down('.msgRow').update(new Element('p', {'class': 'alert alert-danger'}).update(tmp.errMsg) );
			return null;
		}
		return tmp.data;
	}
	
	//after saving the items
	,_afterSaveItems: function (saveBtn, result) {
		var tmp = {};
		tmp.item = result.items[0];
		$(saveBtn).up('.savePanel').replace(this._getItemRow(tmp.item, this._getItemRowEditBtn(tmp.item)));
		return this;
	}
	
	,_getSavePanel: function (item, cssClass) {
		var tmp = {};
		tmp.me = this;
		tmp.isNew = (item.id === undefined || item.id === null);
		tmp.newDiv = new Element('div', {'class': 'panel panel-default savePanel'}).addClassName(cssClass).store('item', item)
			.insert({'bottom':  new Element('div', {'class': 'panel-heading'})
				.insert({'bottom':  new Element('div', {'class': 'panel-title'})
					.insert({'bottom':  'Creating a new Supplier:' })
					.insert({'bottom': new Element('span', {'class': 'btn-group btn-group-xs pull-right'})
						.insert({'bottom': new Element('span', {'class': 'btn btn-primary saveBtn'})
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok-circle'}) })
							.insert({'bottom': ' Save' })
							.observe('click', function() { tmp.me.saveEditedItem(this); }) 
						
						})
						.insert({'bottom': new Element('span', {'class': 'btn btn-default cancelBtn'})
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove-circle'}) })
							.insert({'bottom':' Cancel' })
							.observe('click', function() { tmp.me.cancelEdit(this); }) 
						})
					})
				})
			})
			.insert({'bottom':  new Element('div', {'class': 'panel-body' })
				.insert({'bottom':  new Element('div', {'class': 'msgRow' }) })
				.insert({'bottom':  new Element('div', {'class': 'row' })
					.insert({'bottom':  tmp.me._getSaveFieldDiv('Name', new Element('input', {'value': (tmp.isNew ? '': item.name), "class": "txt value", 'colname': 'name', 'noblank': true, 'placeholder': 'The name of the supplier'}) )
						.addClassName('col-xs-7')
					})
					.insert({'bottom':  tmp.me._getSaveFieldDiv('Connector', new Element('input', {'value': (tmp.isNew ? '': item.connector), "class": "txt value", 'colname': 'connector', 'noblank': true, 'placeholder': 'The connector script of the supplier'}) ) 
						.addClassName('col-xs-4')
					})
					.insert({'bottom':  tmp.me._getSaveFieldDiv('Act?', new Element('input', {'type': 'checkbox', "class": "value", 'checked': (tmp.isNew === true ? true : item.active), 'disabled': tmp.isNew, 'colname': 'active'}) ) 
						.addClassName('col-xs-1')
					})
				})
			})
			.insert({'bottom':  tmp.me._getSaveAttrPanel(item.info) });
		return tmp.newDiv;
	}
	
	,_getNewAttrDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.typeSelection = new Element('select')
			.update(new Element('option', {'value': ''}).update('Pls Select:'))
			.observe('change', function() {
				$(this).up('.list-group-item')
					.replace(tmp.me._getSaveFieldDiv($(this).options[$(this).selectedIndex].innerHTML,  new Element('input', {'value': '', 'class': 'txt value', 'attr_id': '', 'attr_type_id':$F(this), 'noblank': true}), true)
						.addClassName('list-group-item dl-horizontal')
					);
			});
		tmp.me.types.each(function(type) {
			tmp.typeSelection.insert({'bottom': new Element('option', {'value': type.id}).update(type.name) });
		});
		return tmp.me._getSaveFieldDiv('Please Select a type: ',  tmp.typeSelection, true);
	}
	
	,_getSaveAttrPanel: function(attrs) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div', {"class": "list-group"}); 
		$H(attrs).each(function(itemArr) {
			if(typeof(itemArr.value) === 'object') {
				itemArr.value.each(function(attr) {
					tmp.div.insert({'bottom': tmp.me._getSaveFieldDiv(attr.type.name, new Element('input', {'value': attr.value, 'class': 'txt value', 'attr_id': attr.id, 'attr_type_id': attr.type.id, 'noblank': true}), true) 
						.addClassName('list-group-item dl-horizontal')
					});
				});
			};
		});
		tmp.div.insert({'bottom': new Element('span', {'class': 'btn btn-default'})
			.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus-sign'}) })
			.insert({'bottom': ' NEW Info' })
			.observe('click', function() {
				$(this).insert({'before': tmp.me._getNewAttrDiv().addClassName('list-group-item dl-horizontal') });
			})
		});
		return tmp.div;
	}
	
	,_getSaveFieldDiv: function (fieldName, field, showDelBtn) {
		var tmp = {};
		tmp.me = this;
		tmp.showDelBtn = (showDelBtn === true ? true : false);
		tmp.ddDiv = new Element('dd')
			.insert({'bottom':field.addClassName('form-control') });
		if(tmp.showDelBtn === true) {
			tmp.ddDiv.addClassName('input-group input-group-sm')
				.insert({'bottom': new Element('span', {'class': 'btn btn-default input-group-addon'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
				.observe('click', function(){
					if(!confirm('You are about to delete this attribute.\n Continue?'))
						return false;
					if($(this).up('.fielddiv').down('.value'))
						$(this).up('.fielddiv').down('.value').writeAttribute('deactivated', true);
					$(this).up('.fielddiv').fade();
				})
			});
		}
		return new Element('dl', {'class': 'form-group fielddiv'})
			.insert({'bottom':  new Element('dt', {'class': 'control-label'}).update(fieldName) })
			.insert({'bottom':  tmp.ddDiv });
	}
});