/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
	questions: {}

	,init: function(summaryListDiv, listDiv) {
		var tmp = {};
		tmp.me = this;
		tmp.i = 0;
		tmp.questionList = new Element('div', {'class': 'list-group'});
		tmp.me.questions.each(function(item) {
			$(tmp.questionList).insert({'bottom': tmp.me._getQuestionListItem('question_' + tmp.i, item) });
			$(listDiv).insert({'bottom': tmp.me._getQuestionDiv('question_' + tmp.i, item) });
			tmp.i++;
		});
		
		$(summaryListDiv).update(tmp.questionList);
	}
	
	,_getQuestionListItem: function(id, ques) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('a', {'class': 'list-group-item', 'href': '#' + id})
				.insert({'bottom': new Element('h4', {'class': 'list-group-item-heading'})
					.insert({'bottom': ques.en.question })
				})
				.insert({'bottom': new Element('p', {'class': 'list-group-item-text'})
					.insert({'bottom': ques.zh_cn.question })
				})
				.insert({'bottom': new Element('p', {'class': 'list-group-item-text'})
					.insert({'bottom': ques.zh_tw.question })
				});
		return tmp.div;
	}
	
	,_getQuestionDiv: function(id, ques) {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div', {'class': 'panel panel-default nodefault', 'id': id})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': ques.en.question })
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'}) 
				.insert({'bottom': new Element('dl') 
					.insert({'bottom': new Element('dt').update(ques.en.question) }) 
					.insert({'bottom': new Element('dd').update(ques.en.answer) })
				})
				.insert({'bottom': new Element('dl') 
					.insert({'bottom': new Element('dt').update(ques.zh_cn.question) }) 
					.insert({'bottom': new Element('dd').update(ques.zh_cn.answer) })
				})
				.insert({'bottom': new Element('dl') 
					.insert({'bottom': new Element('dt').update(ques.zh_tw.question) }) 
					.insert({'bottom': new Element('dd').update(ques.zh_tw.answer) })
				})
			});
		return tmp.div;
	}
});