/**
 * The header Js file
 */
var HeaderJs = new Class.create();
HeaderJs.prototype = {
	//constructor
	initialize: function () {}

	,load: function(chineseCourses) {
		var tmp = {};
		tmp.me = this;
		tmp.menuList = $$('ul#learn-chinese-menu').first();
		if(tmp.menuList) {
			chineseCourses.each(function(course){
				tmp.menuList.insert({'bottom': new Element('li', {'role': 'presentation'})
					.insert({'bottom': new Element('a', {'href': '/product/' + course.id})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-xs-12'}).update(course.title) })
						})
					})
				});
			});
		}
		return tmp.me;
	}
}
