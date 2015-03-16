"use strict";
jQuery(function() {
	setInterval(function() {
		jQuery.post('/', {
			refresh: 1
		}, function(posts) {
			jQuery.each(posts, function(idx, post) {
				console.log(post.id);
				if (!jQuery('[data-id="' + post.id + '"]').length) {
					console.log("ADD");
				}
			});
		});
	}, 5000);
});