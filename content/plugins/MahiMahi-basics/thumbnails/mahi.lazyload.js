"use strict";
var lazy_version;
var mahi_lazy_load;

jQuery(function() {

	if (window.innerWidth <= 767) {
		lazy_version = 'mobile';
	} else if (window.innerWidth <= 991) {
		lazy_version = 'tablet';
	} else if (window.innerWidth <= 1224) {
		lazy_version = null;
	} else {
		lazy_version = 'big';
	}

	mahi_lazy_load = function() {
		if (jQuery("img.lazy").length) {
			jQuery("img.lazy").lazyload({
				data_attribute: 'src',
				threshold: 1000,
				effect: "fadeIn",
				version: lazy_version,
				skip_invisible: false,
				appear: function() {
					jQuery(this).removeClass('lazy');
				}
			});
		}
	};

	mahi_lazy_load();

});