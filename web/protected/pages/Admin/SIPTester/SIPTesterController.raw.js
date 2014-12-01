/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new AdminPageJs(), {
	resultDivId: null
	
	,testSIP: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.origBtnValue = $(btn).value;
		tmp.testData = tmp.me._collectData(btn);
		if(tmp.testData === null)
			return this;
		
		tmp.me._signRandID(btn);
		this.postAjax(tmp.me.getCallbackId('testSIP'), {'testdata': tmp.testData}, {
			'onLoading': function (sender, param) {
				jQuery(btn.id).button('loading');
			}
			,'onSuccess': function (sender, param) {
				$(tmp.me.resultDivId).update('');
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.logs || tmp.result.logs.size() === 0)
						throw 'System Error: No result return!';
					
					tmp.result.logs.each(function(log) {
						$(tmp.me.resultDivId).insert({'bottom': new Element('div', {'class': 'panel panel-default'})
							.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('<strong>' + log.title + '</strong>') })
							.insert({'bottom': tmp.listGroup = new Element('div', {'class': 'list-group'}) })
						});
						log.info.each(function(msg){
							tmp.listGroup.insert({'bottom': new Element('div', {'class': 'list-group-item'}).update(msg) });
						})
					});
				} catch(e) {
					$(tmp.me.resultDivId).insert({'bottom': tmp.me.getAlertBox('Error', e).addClassName('alert-danger') });
				}
				
			}
			,'onComplete': function (sender, param) {
				jQuery(btn.id).button('reset');
			}
		});
		return this;
	}
	
	,_collectData: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {};
		tmp.hasErr = false;
		//collect the data
		$(btn).up('.info-panel').getElementsBySelector('[sip_request]').each(function(item) {
			tmp.value = $F(item);
			if(tmp.value.blank() && item.hasAttribute('required')) {
				tmp.me._markFormGroupError(item, 'Required')
			}
			tmp.data[item.readAttribute('sip_request')] = tmp.value;
		});
		return tmp.hasErr === true ? null : tmp.data;
	}

});