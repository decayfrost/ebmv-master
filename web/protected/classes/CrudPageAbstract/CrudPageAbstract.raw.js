var CrudPageJs=new Class.create();
CrudPageJs.prototype=Object.extend(new AdminPageJs(),{
	pagination: {pageNo: 1, pageSize: 30} //this is the pagination for the crud page
	,resultDivId: null //this is the result div id
	
	//show all the items
	,showItems:  function(pageNo, pageSize, itemId, resetResult) {
		var tmp = {};
		tmp.me = this;
		tmp.me.pagination.pageNo = (pageNo || tmp.me.pagination.pageNo);
		tmp.me.pagination.pageSize = (pageSize || tmp.me.pagination.pageSize);
		tmp.itemId = (itemId || null);
		tmp.resetResult = (resetResult === false ? false : true);
		tmp.me.postAjax(tmp.me.getCallbackId('getItems'), {'pagination': tmp.me.pagination, 'itemId': tmp.itemId}, {
			'onLoading': function (sender, param) {},
			'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.items || tmp.result.items === undefined || tmp.result.items === null)
						throw 'No item found/generated'; 
					if(tmp.resetResult === true) {
						$(tmp.me.resultDivId).update('');
						if($('total_no_of_items'))
							$('total_no_of_items').update(tmp.result.pagination.totalRows);
					}
					
					tmp.index = (tmp.me.pagination.pageNo - 1) * tmp.me.pagination.pageSize;
					$(tmp.me.resultDivId).insert({'bottom': tmp.me._getResultDiv(tmp.result.items, tmp.resetResult, tmp.index) })
						.insert({'bottom': tmp.me._getPaginBtns(tmp.result.pagination)});
				} catch(e) {
					$(tmp.me.resultDivId).update(e);
				}
			}
		});
	}
	
	//getting the pagination buttons
	,_getPaginBtns: function(pagination) {
		if(pagination.pageNumber >= pagination.totalPages)
			return;
		var tmp = {};
		tmp.me = this;
		tmp.paginDiv = new Element('div', {'class': 'paginDiv'})
			.insert({'bottom': new Element('span', {'class': 'btn btn-success'}).update('Get more')
				.observe('click', function() {
					$(this).up('.paginDiv').remove();
					tmp.me.showItems(tmp.me.pagination.pageNo + 1, tmp.me.pagination.pageSize, null, false);
				})
			});
		return tmp.paginDiv;
	}
	//getting the result div
	,_getResultDiv: function(items, notitlerow, itemrowindex) {
		return null;
	}
	//what to do after the items are deleted
	,_afterDelItems: function (itemIds) {
		var tmp = {};
		tmp.me = this;
		itemIds.each(function(itemId) {
			tmp.row = $(tmp.me.resultDivId).down('.row[item_id=' + itemId + ']');
			if(tmp.row)
				tmp.row.remove();
		});
		return this;
	}
	//deleting an item
	,delItems: function (itemIds) {
		var tmp = {};
		tmp.me = this;
		if(confirm('You are about to delete this item.\n Continue?')) {
			tmp.me.postAjax(tmp.me.getCallbackId('deleteItems'), {'itemIds': itemIds}, {
				'onLoading': function (sender, param) {},
				'onComplete': function (sender, param) {
					try {
						tmp.result = tmp.me.getResp(param, false, true);
						tmp.me._afterDelItems(itemIds);
					} catch(e) {
						alert(e);
					}
				}
			});
		}
		return this;
	}
	// create function for deafult behaviour of edit panel
	,showEditPanel: function (btn, isNEW) {
		throw 'function showEditPanel needs to be overrided!';
	}
	//cancel editing the item
	,cancelEdit: function(btn) {
		throw 'function cancelEdit needs to be overrided!';
	}
	//collecting the data from the save panel before saving
	,_collectSavePanel: function(saveBtn) {
		throw 'function _collectSavePanel needs to be overrided!';
	}
	//after saving the items
	,_afterSaveItems: function (saveBtn, result) {
		throw 'function _afterSaveItems needs to be overrided!';
	}
	//trying to save the item
	,saveEditedItem: function(btn) {
		var tmp = {};
		tmp.me = this;
		//collect and precheck all the user input in the save panel
		tmp.data = tmp.me._collectSavePanel(btn);
		if(tmp.data !== null) {
			tmp.me.postAjax(tmp.me.getCallbackId('saveItems'), tmp.data, {
				'onLoading': function (sender, param) {},
				'onComplete': function (sender, param) {
					try {
						tmp.result = tmp.me.getResp(param, false, true);
						if(tmp.result.items === undefined || tmp.result.items === null || tmp.result.items.size() === 0)
							throw 'System Error: not items returned after saving!';
						tmp.me._afterSaveItems(btn, tmp.result);
					} catch(e) {
						$(btn).up('.savePanel').down('.msgRow').update(new Element('p', {'class': 'alert alert-danger'}).update(e) );
					}
				}
			});
		}
		return this;
	}
	//editing an item
	,editItem: function (btn) {
		this._hideShowAllEditPens(btn, false); 
		this.showEditPanel(btn);
		return this;
	}
	//create an item
	,createItem: function (btn) {
		this._hideShowAllEditPens(btn, false); 
		this.showEditPanel(btn, true);
		return this;
	}
	//hiding the editing row
	,_hideShowAllEditPens: function(btn, show) {
		var tmp = {};
		tmp.me = this;
		tmp.btnsDiv = $(btn).up('.row').getElementsBySelector('.btns').first();
		if (show === true) {
			tmp.btnsDiv.show();
		} else {
			tmp.btnsDiv.hide();
		}
		return this;
	}
	
	
});