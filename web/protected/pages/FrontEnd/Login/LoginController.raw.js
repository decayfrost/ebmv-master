/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
	
	login: function (btn) {
		var tmp = {};
		tmp.me = this;
		tmp.panel = $(btn).up('.LoginPanel');
		tmp.usernamebox = tmp.panel.down('.username');
		tmp.passwordbox = tmp.panel.down('.password');
		if(tmp.me._preSubmit(tmp.usernamebox, tmp.passwordbox) === false) {
			return;
		}
		
		tmp.loadingMsg = new Element('div', {'class': 'loadingMsg'}).update('log into system ...');
		tmp.me.postAjax(tmp.me.getCallbackId('login'), {'username': $F(tmp.usernamebox), 'password': $F(tmp.passwordbox)}, {
			'onLoading': function () {
				$(btn).hide().insert({'after': tmp.loadingMsg });
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result.url)
						window.location = tmp.result.url;
				} catch(e) {
					$(tmp.usernamebox).select();
					tmp.panel.down('.msgpanel').update(tmp.me._getErrMsg(e));
				}
				tmp.loadingMsg.remove();
				$(btn).show();
			}
		}, 60000);
	}

	,_preSubmit: function (usernamebox, passwordbox) {
		var tmp = {};
		tmp.me = this;
		//cleanup error msg
		$$('.errmsg').each(function(item) {
			item.remove();
		});
		
		if($F(usernamebox).blank()) {
			$(usernamebox).insert({'after': tmp.me._getErrMsg('Please provide an username!') });
			$(usernamebox).focus();
			return false;
		}
		
		if($F(passwordbox).blank()) {
			$(passwordbox).insert({'after': tmp.me._getErrMsg('Please provide an password!') });
			$(passwordbox).focus();
			return false;
		}
		return true;
	}

	,_getErrMsg: function (msg) {
		return new Element('span', {'class': 'errmsg smalltxt'}).update(msg);
	}
	
});