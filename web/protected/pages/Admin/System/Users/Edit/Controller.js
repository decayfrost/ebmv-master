var PageJs=new Class.create();PageJs.prototype=Object.extend(new AdminPageJs(),{_user:null,_library:null,setUser:function(a){this._user=a;return this},setLibrary:function(a){this._library=a;return this},_getFormRow:function(b,a){return new Element("div",{"class":"form-group"}).insert({bottom:b.addClassName("col-sm-3 control-label")}).insert({bottom:a.wrap(new Element("div",{"class":"col-sm-8"}))})},_saveUser:function(b){var a={};a.me=this;a.data={libraryId:a.me._library.id,userId:a.me._user.id?a.me._user.id:""};$$("[save-panel]").each(function(c){a.data[c.readAttribute("save-panel")]=$F(c)});a.me.postAjax(a.me.getCallbackId("saveUser"),a.data,{onLoading:function(){jQuery("#"+b.id).button("loading")},onSuccess:function(c,f){try{a.result=a.me.getResp(f,false,true);if(!a.result.item){return}a.me.showModalBox("Success","User Saved Successfully!",true);window.location="/admin/libadminuser/"+a.me._library.id+"/"+a.result.item.id+".html"}catch(d){a.me.showModalBox("ERROR",d,true)}},onComplete:function(){jQuery("#"+b.id).button("reset")}});return a.me},_getEditPanel:function(){var a={};a.me=this;a.isNew=(a.me._user.id?false:true);a.newDiv=new Element("div",{"class":"save-panel form-horizontal",role:"form"}).insert({bottom:a.me._getFormRow(new Element("label",{"for":"first-name"}).update("First Name"),new Element("input",{id:"first-name","save-panel":"firstName","class":"form-control",placeholder:"First Name",value:(a.isNew===true?"":a.me._user.person.firstName)}))}).insert({bottom:a.me._getFormRow(new Element("label",{"for":"last-name"}).update("Last Name"),new Element("input",{id:"last-name","save-panel":"lastName","class":"form-control",placeholder:"Last Name",value:(a.isNew===true?"":a.me._user.person.lastName)}))}).insert({bottom:a.me._getFormRow(new Element("label",{"for":"username"}).update("Username"),new Element("input",{id:"username","save-panel":"username","class":"form-control",placeholder:"Username",value:(a.isNew===true?"":a.me._user.username)}))}).insert({bottom:a.me._getFormRow(new Element("label",{"for":"password"}).update("Password"),new Element("input",{id:"password","save-panel":"password","class":"form-control",placeholder:"Password",value:"",type:"password"}))}).insert({bottom:a.me._getFormRow(new Element("label").update(""),new Element("span",{"class":"btn btn-primary",id:"save-btn","data-loading-text":"saving ..."}).update("save").observe("click",function(){a.me._saveUser(this)}))});return a.newDiv},load:function(a){var b={};b.me=this;$(a).update(b.me._getEditPanel());return b.me}});