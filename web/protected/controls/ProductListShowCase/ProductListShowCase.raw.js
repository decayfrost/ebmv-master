/**
 * The ProductListShowCaseJs file
 */
var ProductListShowCaseJs = new Class.create();
ProductListShowCaseJs.prototype = Object.extend(new FrontPageJs(), {
	pagination: {'pageNo': 1, 'pageSize': 12}
	,_langId: null//the languageId
	,_callbackId: '' //the callbackId
	,_wrapperId: '' //the wrapper id

	,fetch: function(callbackId, wrapperId) {
		var tmp = {};
		tmp.me = this;
		tmp.me._callbackId = callbackId; 
		tmp.me._wrapperId = wrapperId; 
		
		tmp.resultDiv = $(wrapperId).down('.list');
		tmp.me.postAjax(callbackId, {'pagnation': tmp.me.pagination, 'languageId': tmp.me._langId}, {
			'onLoading': function () {
				$(tmp.resultDiv).update(tmp.me._getLoadingDiv());
			}
			,'onComplete': function(sender, param) {
				tmp.listDiv = new Element('div');
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result.products.size() === 0) 
						throw 'Nothing found!';
					tmp.result.products.each(function(item){
						tmp.listDiv.insert({'bottom': tmp.me._getProductThumbnail(item).wrap(new Element('div', {"class": "col-lg-2 col-md-3 col-sm-4 col-xs-6"})) });
					});
					$(tmp.resultDiv).update(tmp.listDiv);
				} catch (e) {
					$(tmp.resultDiv).update(new Element('p', {'class': 'bg-danger'}).update(e));
				}
			}
		});
		return this;
	}
	
	//getting the loading div
	,_getLoadingDiv: function() {
		return new Element('span', {'class': 'loading'})
			.insert({'bottom': new Element('img', {'src': '/themes/images/loading.gif', "style": "width: 33px; height: 33px;"})})
			.insert({'bottom': 'Loading ...'});
	}
	
	,changeLanguage: function(btn) {
		var tmp = {};
		tmp.me = this;
		$(btn).up('.langlist').getElementsBySelector('li.langitem').each(function(item){
			item.removeClassName('active');
		})
		tmp.me._langId = $(btn).addClassName('active').readAttribute('langid').strip();
		this.fetch(tmp.me._callbackId, tmp.me._wrapperId);
		return this;
	}
});
