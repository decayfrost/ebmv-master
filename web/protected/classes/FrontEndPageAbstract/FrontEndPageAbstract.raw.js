/**
 * The FrontEndPageAbstract Js file
 */
var FrontPageJs = new Class.create();
FrontPageJs.prototype = {
	modalId: 'page_modal_box_id'
	,productDetailsUrl: '/product/{id}' 
		
	,_currentLib: null //the id of current library
	
	//the callback ids
	,callbackIds: {}

	//constructor
	,initialize: function () {}
	
	,setCallbackId: function(key, callbackid) {
		this.callbackIds[key] = callbackid;
		return this;
	}
	
	,getCallbackId: function(key) {
		if(this.callbackIds[key] === undefined || this.callbackIds[key] === null)
			throw 'Callback ID is not set for:' + key;
		return this.callbackIds[key];
	}
	
	//posting an ajax request
	,postAjax: function(callbackId, data, requestProperty, timeout) {
		var tmp = {};
		tmp.request = new Prado.CallbackRequest(callbackId, requestProperty);
		tmp.request.setCallbackParameter(data);
		tmp.timeout = (timeout || 30000);
		if(tmp.timeout < 30000) {
			tmp.timeout = 30000;
		}
		tmp.request.setRequestTimeOut(tmp.timeout);
		tmp.request.dispatch();
		return tmp.request;
	}
	//parsing an ajax response
	,getResp: function (response, expectNonJSONResult, noAlert) {
		var tmp = {};
		tmp.expectNonJSONResult = (expectNonJSONResult !== true ? false : true);
		tmp.result = response;
		if(tmp.expectNonJSONResult === true)
			return tmp.result;
		if(!tmp.result.isJSON()) {
			tmp.error = 'Invalid JSON string: ' + tmp.result;
			if (noAlert === true)
				throw tmp.error;
			else 
				return alert(tmp.error);
		}
		tmp.result = tmp.result.evalJSON();
		if(tmp.result.errors.size() !== 0) {
			tmp.error = 'Error: \n\n' + tmp.result.errors.join('\n');
			if (noAlert === true)
				throw tmp.error;
			else 
				return alert(tmp.error);
		}
		return tmp.result.resultData;
	}
	//format the currency
	,getCurrency: function(number, dollar, decimal, decimalPoint, thousandPoint) {
		var tmp = {};
		tmp.decimal = (isNaN(decimal = Math.abs(decimal)) ? 2 : decimal);
		tmp.dollar = (dollar == undefined ? "$" : dollar);
		tmp.decimalPoint = (decimalPoint == undefined ? "." : decimalPoint);
		tmp.thousandPoint = (thousandPoint == undefined ? "," : thousandPoint);
		tmp.sign = (number < 0 ? "-" : "");
		tmp.Int = parseInt(number = Math.abs(+number || 0).toFixed(tmp.decimal)) + "";
		tmp.j = (tmp.j = tmp.Int.length) > 3 ? tmp.j % 3 : 0;
		return tmp.dollar + tmp.sign + (tmp.j ? tmp.Int.substr(0, tmp.j) + tmp.thousandPoint : "") + tmp.Int.substr(tmp.j).replace(/(\d{3})(?=\d)/g, "$1" + tmp.thousandPoint) + (tmp.decimal ? tmp.decimalPoint + Math.abs(number - tmp.Int).toFixed(tmp.decimal).slice(2) : "");
	}
	//do key enter
	,keydown: function (event, enterFunc, nFunc) {
		//if it's not a enter key, then return true;
		if(!((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13))) {
			if(typeof(nFunc) === 'function') {
				nFunc();
			}
			return true;
		}
		
		if(typeof(enterFunc) === 'function') {
			enterFunc();
		}
		return false;
	}
	//getting te product thumbnail div
	,_getProductThumbnail: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.productDiv = new Element('div', {'class': 'thumbnail nodefault', 'title': product.title})
			.insert({'bottom': new Element('a', {'href': tmp.me.getProductDetailsUrl(product.id) })
				.update(tmp.me._getProductImgDiv(product.attributes.image_thumb || null)) 
			})
			.insert({'bottom': new Element('div', {'class': 'caption'})
				.insert({'bottom': product.title })
			})
		;
		return tmp.productDiv;
	}
	//redirect the product to detailspage
	,showDetailsPage: function(productId) {
		window.location = this.getProductDetailsUrl(productId);
	}
	
	//getting the product details page's url
	,getProductDetailsUrl: function (productId) {
		return this.productDetailsUrl.replace('{id}', productId);
	}
	
	//getting the product image div
	,_getProductImgDiv: function (images, attributes) {
		var tmp = {};
		tmp.loadingImg = new Image();
		tmp.loadingImg.writeAttribute('data-src', "holder.js/100%x180")
			.writeAttribute('src', 'data:image/gif;base64,R0lGODlhyADwAMZAAAQCBAQGBAwKDAwODBQSFBQWFBwaHBweHCQiJCQmJCwqLCwuLDQyNDQ2NDw6PDw+PERCRERGRExKTExOTFRSVFRWVFxaXFxeXGRiZGRmZGxqbGxubHRydHR2dHx6fHx+fISChISGhIyKjIyOjJSSlJSWlJyanJyenKSipKSmpKyqrKyurLSytLS2tLy6vLy+vMTCxMTGxMzKzMzOzNTS1NTW1Nza3Nze3OTi5OTm5Ozq7Ozu7PTy9PT29Pz6/Pz+/P///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH/C05FVFNDQVBFMi4wAwEAAAAh/hFDcmVhdGVkIHdpdGggR0lNUAAh+QQIBgD/ACxWAFsAIAAgAAAH/oBAgoOEhYaHiIQyFxcwiY+INgEAlC6Ql4MflJQRmIg/MCYsPSCbAAyDLxMrnjwQmwYnk5Qggj8JAAI/mBqmAAMqEAkfPoMRAAu7kD8DvgAfhzwsO5g+s6YXno+vvh6YPyuWhzLXvziYJZSOhzEOlA0yniKUMYk/PDvK1Sctlz4uKVzw0PboR4lmlAaI0EeQkAdnADg0LBQDIj1MNUDMKITBIgAN/ggACKCDUAOPEC71EEDpBiF3FitgenEhRaGHFktMHISj3KYEPXYOasHS1IEa1ahFunBAgIIPSiHVKADgQrFPDAnNgLDABKFelSb+QLDpxaARZSfmMOVNkI8NFww+ZK1mYBMroYZiMCggF+8nv4ADIwoEACH5BAkGAH8ALFYAWwAgACAAAAf+gECCg4SFhoeIiYqLhj0eGjmMkoMiAAASk4o/P4IglguELxMrmTIPAQgjPjsSCS2DPwgAApyMNwKWlhyIEAALtYshubk6hzstPJMdw5YvmYkuzACRjD8qLog/F8MdkySWMNkqFhQpwIuVADHPjD4nr+zxiDwqICc78ocuBbkCKfmEbASQtk5SDRAzDGWQBoCC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');
		
		tmp.imgSRC = '/themes/images/no_image_found.jpg';
		if(images !== undefined && images !== null && images.size() > 0)
			tmp.imgSRC = '/asset/get?id=' + images[0].attribute;
		tmp.img = new Image();
		if(attributes) {
			$H(attributes).each(function(attr) {
				tmp.img.writeAttribute(attr.key, attr.value);
			})
		}
		tmp.img.writeAttribute('data-src', "holder.js/100%x180")
		.writeAttribute('src',tmp.imgSRC)
		.observe('load', function(){
			tmp.loadingImg.replace(tmp.img);
		})
		return tmp.loadingImg;
	}
	//getting the current user
	,getUser: function(btn, afterFunc, loadingFunc, cancelLoginFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('getUser'), {}, {
			'onLoading': function () {
				if(typeof(loadingFunc) === 'function')
					loadingFunc();
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(typeof(afterFunc) === 'function')
						afterFunc();
				} catch(e) {
					jQuery('#' + btn.id).popover({
						'html': true,
						'placement': 'auto',
						'title': '登陆/登陸/Sign In',
						'content': tmp.me._getLoginPanel(btn, cancelLoginFunc),
						'container': 'body'
					})
					.popover('show');
//					jQuery('.popoverbtn').not(jQuery('#' + btn.id)).popover('hide').button('reset');
//					tmp.me.showLoginPanel(btn, cancelLoginFunc);
				}
			}
		});
	}
	//showing login panel
	,_getLoginPanel: function(btn, cancelLoginFunc) {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'login-form loginpanel', 'role': 'form'})
			.insert({'bottom': new Element('div', {'class': 'row msgpanel'}) })
			.insert({'bottom': new Element('div', {'class': 'form-group'})
				.insert({'bottom': new Element('label', {'for': 'username'}).update('图书馆卡号/圖書館卡號/Library Card No.') })
				.insert({'bottom': new Element('div', {'class': 'input-group'})
					.insert({'bottom': new Element('span', {'class': 'input-group-addon'}) 
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-user'}) })
					})
					.insert({'bottom': new Element('input', {'id': 'username', 'type': 'text', 'class': 'form-control username', 'placeholder': 'Username', 'required': true, 'autofocus': true}) 
						.observe('keydown', function(event) {
							pageJs.keydown(event, function(){$(Event.element(event)).up('.loginpanel').down('.loginbtn').click();});
						})
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'form-group'})
				.insert({'bottom': new Element('label', {'for': 'password'}).update('密码/密碼/PIN') })
				.insert({'bottom': new Element('div', {'class': 'input-group'})
					.insert({'bottom': new Element('span', {'class': 'input-group-addon'}) 
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-lock'}) })
					})
					.insert({'bottom': new Element('input', {'id': 'password', 'type': 'password', 'class': 'form-control password', 'placeholder': 'Password', 'required': true}) 
						.observe('keydown', function(event) {
							pageJs.keydown(event, function(){$(Event.element(event)).up('.loginpanel').down('.loginbtn').click();});
						})
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'form-group btns'})
				.insert({'bottom': new Element('span', {'id': 'pop_login_btn', 'class': 'loginbtn btn btn-sm btn-primary btn-block iconbtn', 'data-loading-text': '登陆中/登陸中/Processing...'})
					.insert({'bottom': new Element('div', {'class': 'btnname'})
						.insert({'bottom': '登陆/登陸' })
						.insert({'bottom': new Element('small').update('Sign in') })
					})
					.observe('click', function() {
						tmp.me._login(this, null, function() {
							window.location = document.URL;
						});
					})	
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-sm btn-default btn-block iconbtn'})
					.insert({'bottom': new Element('div', {'class': 'btnname'})
						.insert({'bottom': '取消/撤消' })
						.insert({'bottom': new Element('small').update('Cancel') })
					})
					.observe('click', function() {
						jQuery(btn).popover('hide');
						if(typeof(cancelLoginFunc) === 'function')
							cancelLoginFunc();
					})
				})
			});
	}
	
	,_getErrMsg: function (msg) {
		return new Element('span', {'class': 'errmsg smalltxt'}).update(msg);
	}

	,_login: function (btn, loadingFunc, afterFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.panel = $(btn).up('.loginpanel');
		tmp.usernamebox = tmp.panel.down('.username');
		tmp.passwordbox = tmp.panel.down('.password');
		if(tmp.me._preLogin(tmp.usernamebox, tmp.passwordbox) === false) {
			return;
		}
		
		tmp.loadingMsg = new Element('div', {'class': 'loadingMsg'}).update('log into system ...');
		tmp.me.postAjax(tmp.me.getCallbackId('loginUser'), {'username': $F(tmp.usernamebox), 'password': $F(tmp.passwordbox)}, {
			'onLoading': function () {
				$(btn).up('.row').hide().insert({'after': tmp.loadingMsg });
				tmp.panel.down('.msgpanel').update('');
				if(typeof(loadingFunc) === 'function')
					loadingFunc();
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(typeof(afterFunc) === 'function')
						afterFunc();
				}
				catch(e)
				{
					$(tmp.usernamebox).select();
					tmp.panel.down('.msgpanel').update(tmp.me._getErrMsg(e));
				}
				tmp.loadingMsg.remove();
				$(btn).up('.row').show();
			}
		});
	}
	/**
	 * pre checking for login
	 */
	,_preLogin: function (usernamebox, passwordbox) {
		var tmp = {};
		tmp.me = this;
		tmp.loginPanel = $(usernamebox).up('.loginpanel');
		//cleanup error msg
		tmp.loginPanel.getElementsBySelector('.has-error').each(function(item) {
			item.removeClassName('has-error');
		});
		tmp.loginPanel.down('.msgpanel').update('');
		
		tmp.me.errorMsg = '';
		if($F(usernamebox).blank()) {
			$(usernamebox).up('.form-group').addClassName('has-error');
			tmp.me.errorMsg += '<span class="label label-danger">Username is required</span> ';
		}
		
		if($F(passwordbox).blank()) {
			$(passwordbox).up('.form-group').addClassName('has-error');
			tmp.me.errorMsg += '<span class="label label-danger">Password is required</span> ';
		}
		
		if(!tmp.me.errorMsg.blank()) {
			tmp.loginPanel.down('.msgpanel').update(tmp.me.errorMsg);
			return false;
		}
		return true;
	}
	/**
	 * Getting an alert box
	 */
	,getAlertBox: function(title, msg) {
		return new Element('div', {'class': 'alert alert-dismissible', 'role': 'alert'})
		.insert({'bottom': new Element('button', {'class': 'close', 'data-dismiss': 'alert'})
			.insert({'bottom': new Element('span', {'aria-hidden': 'true'}).update('&times;') })
			.insert({'bottom': new Element('span', {'class': 'sr-only'}).update('Close') })
		})
		.insert({'bottom': new Element('strong').update(title) })
		.insert({'bottom': msg })
	}
	/**
	 * give the input box a random id
	 */
	,_signRandID: function(input) {
		if(!input.id)
			input.id = 'input_' + String.fromCharCode(65 + Math.floor(Math.random() * 26)) + Date.now();
		return this;
	}
	/**
	 * Marking a form group to has-error
	 */
	,_markFormGroupError: function(input, errMsg) {
		var tmp = {}
		tmp.me = this;
		if(input.up('.form-group')) {
			input.up('.form-group').addClassName('has-error');
			tmp.me._signRandID(input);
			jQuery('#' + input.id).tooltip({
				'trigger': 'manual'
				,'placement': 'auto'
				,'container': 'body'
				,'placement': 'bottom'
				,'html': true
				,'title': errMsg
			})
			.tooltip('show');
			$(input).observe('change', function() {
				input.up('.form-group').removeClassName('has-error');
				jQuery(this).tooltip('hide').tooltip('destroy').show();
			});
		}
		return tmp.me;
	}
	/**
	 * showing the modal box
	 */
	,showModalBox: function(title, content, isSM, footer) {
		var tmp = {};
		tmp.me = this;
		tmp.isSM = (isSM === true ? true : false);
		tmp.footer = (footer ? footer : null);
		tmp.newBox = new Element('div', {'class': 'modal', 'tabindex': '-1', 'role': 'dialog', 'aria-hidden': 'true', 'aria-labelledby': 'page-modal-box'})
			.insert({'bottom': new Element('div', {'class': 'modal-dialog ' + (tmp.isSM === true ? 'modal-sm' : 'modal-lg') })
				.insert({'bottom': new Element('div', {'class': 'modal-content' })
					.insert({'bottom': new Element('div', {'class': 'modal-header' })
						.insert({'bottom': new Element('div', {'class': 'close', 'type': 'button', 'data-dismiss': 'modal'})
							.insert({'bottom':new Element('span', {'aria-hidden': 'true'}).update('&times;') })
						})
						.insert({'bottom': new Element('strong', {'class': 'modal-title', 'id': 'page-modal-box'}).update(title) })
					})
					.insert({'bottom': new Element('div', {'class': 'modal-body' }).update(content) })
					.insert({'bottom': tmp.footer === null ? '' : new Element('div', {'class': 'modal-footer' }).update(tmp.footer) })
				})
			});
		
		if($(tmp.me.modalId)) {
			$(tmp.me.modalId).remove();
		}
		
		$$('body')[0].insert({'bottom': tmp.newBox.writeAttribute('id',  tmp.me.modalId)});
		jQuery('#' + tmp.me.modalId).modal({'show': true, 'target': '#' + tmp.me.modalId});
		return tmp.me;
	}
	/**
	 * returning a loading image
	 */
	,getLoadingImg: function() {
		return Element('img', {'class': 'loading-img', 'src': 'data:image/gif;base64,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'})
	}
	/**
	 * Load the mysql utc time into Date object
	 */
	,loadUTCTime: function (utcString) {
		var tmp = {}
		tmp.strings = utcString.strip().split(' ');
		tmp.dateStrings = tmp.strings[0].split('-');
		tmp.timeStrings = tmp.strings[1].split(':');
		return new Date(Date.UTC(tmp.dateStrings[0], (tmp.dateStrings[1] * 1 - 1), tmp.dateStrings[2], tmp.timeStrings[0], tmp.timeStrings[1], tmp.timeStrings[2]));
		
	}
};
