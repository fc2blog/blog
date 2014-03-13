/**
 * jQuery slideMenu
 * fc2 mobile
 * http://fc2.com/
 *
 * Copyright 1999-2013 FC2 inc.
 * Author umi
 * June 2013
 */
(function($){
	var options;
	var defaults = {
		name: 'slidemenu',
		left_menu: '#left_menu',
		right_menu: '#right_menu',
		left_menu_btn: '#left_menu_btn',
		right_menu_btn: '#right_menu_btn',
		main_contents: '#wrapper',
		overlay: '#overlay',
		left_slide_class: 'show_left',
		right_slide_class: 'show_right',
		support: {
			touch: ('ontouchstart' in window)
		},
		touchEndEvent: ('ontouchend' in window) ? 'touchend': 'mouseup',
		resize: function(event){
			var _height = 0;
			$(event.data.obj).children().each(function(i, val){_height += $(val).outerHeight()});
			$(options.main_contents).css({height:Math.max(_height, $(event.currentTarget).height())});
		},
		open: function(event){
			$(options.left_menu).add(options.right_menu).add(options.main_contents).add(options.overlay).addClass(event.data.class);
			$(window).bind('resize.'+options.name, {obj: event.data.obj}, options.resize).trigger('resize.'+options.name);
			event.preventDefault();
			event.stopPropagation();
			return false;
		},
		close: function(event){
			$(options.left_menu).add(options.right_menu).add(options.overlay).add(options.main_contents).removeClass(options.left_slide_class+' '+options.right_slide_class);
			$(options.main_contents).css({height: 'auto'});
			$(window).unbind('resize.'+options.name);
			event.preventDefault();
			event.stopPropagation();
			return false;
		}
	};

	$.extend({
		slideMenu: function(_opt){
			options = $.extend({}, defaults, _opt);
			$(options.left_menu).after($('<div>', {id: options.overlay.slice(1)}));
			$(document).off('.'+options.name)
			           .on('click.'+options.name, options.left_menu_btn, {'class': options.left_slide_class, obj: options.left_menu}, options.open)
			           .on('click.'+options.name, options.right_menu_btn, {'class': options.right_slide_class, obj: options.right_menu}, options.open)
			           .on('click.'+options.name, options.overlay, options.close);

			return this;
		}
	});
})(jQuery);