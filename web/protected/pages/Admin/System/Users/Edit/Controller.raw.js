/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new AdminPageJs(), {
	_user: null
	,_library: null
	,setUser: function(user) {
		this._user = user;
		return this;
	}
	,setLibrary: function(library) {
		this._library = library;
		return this;
	}
	,_getFormRow: function(label, input) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': label.addClassName('col-sm-3 control-label') })
			.insert({'bottom': input.wrap(new Element('div', {'class': 'col-sm-8'})) });
	}
	,_saveUser: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {'libraryId': tmp.me._library.id, 'userId' : tmp.me._user.id ? tmp.me._user.id : ''};
		$$('[save-panel]').each(function(element){
			tmp.data[element.readAttribute('save-panel')] = $F(element);
		});
		tmp.me.postAjax(tmp.me.getCallbackId('saveUser'), tmp.data, {
			'onLoading': function() {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.item)
						return;
					tmp.me.showModalBox('Success', "User Saved Successfully!", true);
					window.location = '/admin/libadminuser/' + tmp.me._library.id + '/' + tmp.result.item.id + '.html';
				} catch (e) {
					tmp.me.showModalBox('ERROR', e, true);
				}
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
			}
		});
		return tmp.me;
	}
	,_getEditPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.isNew = (tmp.me._user.id ? false : true);
		tmp.newDiv = new Element('div', {'class': 'save-panel form-horizontal', 'role': 'form'})
			.insert({'bottom': tmp.me._getFormRow(
					new Element('label', {'for': 'first-name'}).update('First Name'), 
					new Element('input', {'id': 'first-name', 'save-panel': 'firstName', 'class': 'form-control', 'placeholder': 'First Name', 'value': (tmp.isNew === true ? '' : tmp.me._user.person.firstName)})
				) 
			})
			.insert({'bottom': tmp.me._getFormRow(
					new Element('label', {'for': 'last-name'}).update('Last Name'),
					new Element('input', {'id': 'last-name', 'save-panel': 'lastName', 'class': 'form-control', 'placeholder': 'Last Name','value': (tmp.isNew === true ? '' : tmp.me._user.person.lastName)})
				)
			})
			.insert({'bottom': tmp.me._getFormRow(
					new Element('label', {'for': 'username'}).update('Username'),
					new Element('input', {'id': 'username', 'save-panel': 'username', 'class': 'form-control', 'placeholder': 'Username','value': (tmp.isNew === true ? '' : tmp.me._user.username)})
				)
			})
			.insert({'bottom': tmp.me._getFormRow(
					new Element('label', {'for': 'password'}).update('Password'),
					new Element('input', {'id': 'password', 'save-panel': 'password', 'class': 'form-control', 'placeholder': 'Password','value': '', 'type': 'password'})
				)
			})
			.insert({'bottom': tmp.me._getFormRow(
					new Element('label').update(''),
					new Element('span', {'class': 'btn btn-primary', 'id': 'save-btn', 'data-loading-text': 'saving ...'}).update('save')
					.observe('click', function() {
						tmp.me._saveUser(this);
					})
				)
			})
			;
		return tmp.newDiv;
	}
	,load: function(detailsDivId) {
		var tmp = {};
		tmp.me = this;
		$(detailsDivId).update(tmp.me._getEditPanel());
		return tmp.me;
	}
});