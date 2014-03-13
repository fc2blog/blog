/**
 * jQuery fc2Accordion
 * http://fc2.com/
 * 
 * Copyright 1999-2013 FC2 inc.
 * Author smith
 * ver 0.1.1 Aug 2013
 *
 */
(function($) {
	$.fn.fc2Accordion = function(config) {
		var defaults = {
			contents: '.accordion_contents',
			classOpen: 'open',
			toggle: function(event) {
				var isOpen = $(this).hasClass(options.classOpen);
				$(this).toggleClass(options.classOpen, !isOpen);
				$(options.contents).eq(event.data.index).toggle(!isOpen);
			}
		};

		var options = $.extend(defaults, config);

		return this.each(function(i) {
			$(this).bind('click', {'index': i}, options.toggle);
		});
	};
})(jQuery);