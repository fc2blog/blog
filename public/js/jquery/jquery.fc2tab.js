/**
 * jQuery tab menu
 * http://fc2.com/
 * 
 * Copyright 1999-2013 FC2 inc.
 * Author smith
 * ver 0.1.1 Aug 2013
 */
(function($) {
	$.extend({
		fc2Tab: function(config) {
			var defaults = {
				menu: '.tab-menu',
				contents: '.tab-contents',
				classSelected: 'tab-selected',
				init: function() {
					$(options.menu).click(options.click);
				},
				click: function(event) {
					var index = $(options.menu).index(this);
					$(options.menu).removeClass(options.classSelected).eq(index).addClass(options.classSelected);
					$(options.contents).hide().eq(index).show();
				}
			};
			var options = $.extend({}, defaults, config);
			options.init();
		}
	});
})(jQuery);
