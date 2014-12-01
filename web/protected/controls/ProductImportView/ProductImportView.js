/**
 * The control Js file
 */
var ProductImportViewJs = new Class.create();
ProductImportViewJs.prototype = {
	_pageJs: null, //the pageJs object
	_callbackIds: {}, //the callback ids
	_getNextLog: true, //whether we are displaying the next lot of logs
	
	//constructor
	initialize: function(pageJs, getSupLibInfoBtn, isImportingBtn, importBtn, getLogBtn) {
		this._pageJs = pageJs;
		this._callbackIds.suplibinfobtn = getSupLibInfoBtn;
		this._callbackIds.isImportingBtn = isImportingBtn;
		this._callbackIds.importBtn = importBtn;
		this._callbackIds.getLogBtn = getLogBtn;
	}

	,_getFieldDiv: function(title, field) {
		return new Element('div', {'class': 'fieldDiv'})
			.insert({'bottom': new Element('span', {'class': 'title'}).update(title)
				.insert({'bottom': new Element('span', {'class': 'btns'})
					.insert({'bottom': new Element('span', {'class': 'inlineblock btnwrapper'})
						.insert({'bottom': new Element('input', {'class': 'allbtn', 'type': 'checkbox', 'value': 'all'}) })
						.insert({'bottom': new Element('lable').update('All') })
					})
				})
			})
			.insert({'bottom': new Element('span', {'class': 'field'}).update(field) });
	}

	,_getImportDiv: function(supplierSelBox, libSelBox, maxQty) {
		var tmp = {};
		tmp.me = this;
		tmp.maxQty = (maxQty || 'all');
		tmp.newDiv = new Element('div', {'class': 'productImportWrapper'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'class': 'inlineblock'}).update( tmp.me._getFieldDiv('Supplier:', supplierSelBox.writeAttribute('importinfo', 'supplierIds')))	})
				.insert({'bottom': new Element('span', {'class': 'inlineblock'}).update( tmp.me._getFieldDiv('Library:', libSelBox.writeAttribute('importinfo', 'libraryIds')))	})
				.insert({'bottom': new Element('span', {'class': 'inlineblock'}).update( tmp.me._getFieldDiv('Max Qty:', new Element('input', {'type': 'textbox', 'importinfo': 'maxQty', 'class': 'maxQty', 'value': '0'}) ))	})
				.insert({'bottom': new Element('span', {'class': 'inlineblock'})
					.insert({'bottom': new Element('span', {'class': 'button submitbtn import'}).update('Import NOW') })
					.insert({'bottom': new Element('span', {'class': 'button submitbtn cancel'}).update('Cancel') })
				})
			})
		;
		return tmp.newDiv;
	}
	
	,_getSelBox: function (options) {
		var tmp = {};
		tmp.me = this;
		tmp.selBox = new Element('select', {'multiple': 'multiple'});
		options.each(function(opt) {
			tmp.selBox.insert({'bottom': new Element('option', {'value': opt.id}).update(opt.name) });
		});
		return tmp.selBox;
	}
	
	,_nextLog: function (transId, nowUTC, resultDivId) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._getNextLog === false)
			return this;
		tmp.me._pageJs.postAjax(tmp.me._callbackIds.getLogBtn, {'transId': transId, 'nowUTC': nowUTC}, {
			'onLoading': function (sender, param) {},
			'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me._pageJs.getResp(param, false, true);
					if(tmp.result.logs.size() >0) {
						tmp.result.logs.each(function(log){
							tmp.divRow = new Element('div', {'class': 'row'})
								.insert(new Element('span', {'class': 'logTime inlineblock col'}).update(log.created))
								.insert(new Element('span', {'class': 'logResult inlineblock col'}).update(log.msg));
							$(resultDivId).insert({'bottom': tmp.divRow}).scrollTop = $(resultDivId).scrollHeight;
						});
					}
					if(tmp.result.hasMore) {
						setTimeout(function () { tmp.me._nextLog(transId, tmp.result.nowUTC, resultDivId); }, 3000);
					} else {
						Modalbox.MBcaption.update('Importing finished!');
					}
				} catch(e) {
					alert(e);
				}
			}
		});
		return this;
	}
	
	,_showImportLogs: function (transId, nowUTC) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div', {'class': 'inProgressWrapper'})
			.insert({'bottom': new Element('div', {'class': 'inProgressDiv', 'id': 'inProgressResultDiv'}) });
		Modalbox.show(tmp.div.wrap(new Element('div', {'class': 'productImportWrapper'})), {
			'title': 'Importing in progress...', 
			'width': 800,
			'afterLoad': function() {
				tmp.me._nextLog(transId, nowUTC, 'inProgressResultDiv'); 
			},
			'onHide': function() {
				tmp.me._getNextLog = false;
			},
			'onLoad': function() {
				tmp.me._getNextLog = true;
			}
		});
		return this;
	}
	
	,_startImport: function(btn) {
		var tmp = {};
		tmp.me = this;
		if(confirm('Once you start the import, you can NOT stop it until it is finished.\n Are you sure you want to continue?')) {
			tmp.requestData = {};
			$(btn).up('.productImportWrapper').getElementsBySelector('[importinfo]').each(function (item) {
				tmp.requestData[item.readAttribute('importinfo')] = $F(item);
			});
			
			tmp.me._pageJs.postAjax(tmp.me._callbackIds.importBtn, tmp.requestData, {
				'onLoading': function (sender, param) {},
				'onComplete': function (sender, param) {
					try {
						tmp.result = tmp.me._pageJs.getResp(param, false, true);
						tmp.me._showImportLogs(tmp.result.transId, tmp.result.nowUTC);
					} catch(e) {
						alert(e);
					}
				}
			});
		}
		return this;
	}
	
	,_showModalbox: function(supplierSelBox, libSelBox) {
		var tmp = {};
		tmp.me = this;
		tmp.div = tmp.me._getImportDiv(supplierSelBox, libSelBox);
		Modalbox.show(tmp.div, {
			'title': 'Do you want to import from:', 
			'width': 800,
			'afterLoad': function() {
				Modalbox.MBcontent.getElementsBySelector('input.allbtn').each(function(item) {
					item.observe('click', function() { 
						tmp.checked = this.checked;
						tmp.valueBox = $(this).up('.fieldDiv').down('[importinfo]');
						if(tmp.valueBox.nodeName.toLowerCase() === 'select') {
							for (tmp.i = 0; tmp.i < tmp.valueBox.options.length; tmp.i++) {
								tmp.valueBox.options[tmp.i].selected = tmp.checked;
							}
						} else {
							tmp.valueBox.setValue(tmp.checked === true ? 'all' : 0);
						}
						tmp.valueBox.disabled = tmp.checked;
					});
					item.click();
				});
				Modalbox.MBcontent.down('.submitbtn.cancel').observe('click', function() { Modalbox.hide(); });
				Modalbox.MBcontent.down('.submitbtn.import').observe('click', function() { tmp.me._startImport(this); });
			}
		});
		return this;
	}
	
	,_getImportPanel: function(supplier, lib) {
		var tmp = {};
		tmp.me = this;
		
		//trying to form the selection box
		tmp.supplierSelBox = tmp.libSelBox = null;
		if(supplier && supplier.id && supplier.name)
			tmp.supplierSelBox = tmp.me._getSelBox([{'id': supplier.id, 'name': supplier.name}]);
		if(lib && lib.id && lib.name)
			tmp.libSelBox = tmp.me._getSelBox([{'id': lib.id, 'name': lib.name}]);
		
		if(tmp.supplierSelBox === null || tmp.libSelBox === null) {
			tmp.me._pageJs.postAjax(tmp.me._callbackIds.suplibinfobtn, {'suppliers': tmp.supplierSelBox === null, 'libraries': tmp.libSelBox === null}, {
				'onLoading': function (sender, param) {},
				'onComplete': function (sender, param) {
					try {
						tmp.result = tmp.me._pageJs.getResp(param, false, true);
						if(!tmp.result.suppliers || !tmp.result.libraries)
							throw 'System Error: No information found/generated, contact BMV directly now!';
						if(tmp.supplierSelBox === null)
							tmp.supplierSelBox = tmp.me._getSelBox(tmp.result.suppliers);
						if(tmp.libSelBox === null)
							tmp.libSelBox = tmp.me._getSelBox(tmp.result.libraries);
						tmp.me._showModalbox(tmp.supplierSelBox, tmp.libSelBox);
					} catch(e) {
						alert(e);
					}
				}
			});
		} else {
			tmp.me._showModalbox(tmp.supplierSelBox, tmp.libSelBox);
		}
	}
	
	,load: function(supplier, lib, afterLoadFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.me._pageJs.postAjax(tmp.me._callbackIds.isImportingBtn, {}, {
			'onLoading': function (sender, param) {},
			'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me._pageJs.getResp(param, false, true);
					if(!tmp.result.isImporting)
						tmp.me._getImportPanel(supplier, lib);
					else
						tmp.me._showImportLogs(tmp.result.transId, tmp.result.nowUTC);
					if(typeof(afterLoadFunc) === 'function')
						afterLoadFunc();
				} catch(e) {
					alert(e);
				}
			}
		});
	}
};