/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CrudPageJs(), {
	
	_getItemRow: function (item, option) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div', {'class' : 'row', 'item_trans_id': item.transId}).store('item', item)
			.insert({'bottom' : new Element('span', {'class' : 'col created'}).update(item.created) })
			.insert({'bottom' : new Element('span', {'class' : 'col entry_count'}).update(item.entryCount) })
			.insert({'bottom' : new Element('span', {'class' : 'col created_by'}).update(item.createdBy) })
			.insert({'bottom' : new Element('span', {'class' : 'col btns'}).update(option) });
		return tmp.div;
	}

	,_getResultDiv: function(items, includetitlerow, itemrowindex) {
		var tmp = {};
		tmp.me = this;
		tmp.includetitlerow = (includetitlerow === false ? false : true);
		
		tmp.resultDiv = new Element('div');
		if(tmp.includetitlerow === true)
			tmp.resultDiv.insert({'bottom':  tmp.me._getItemRow({'created': 'Created Time', 'entryCount': 'No Of Logs', 'createdBy': 'Logged By', 'istitle': true}).addClassName('titleRow') });
		tmp.i = (itemrowindex || 0);
		items.each(function(item) {
			tmp.resultDiv.insert({'bottom':  tmp.me._getItemRow(item, null).addClassName(tmp.i % 2 === 1 ? 'even' : 'odd') });
			tmp.i++;
		});
		return tmp.resultDiv;
	}
});