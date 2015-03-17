"use strict";
/* global twttr */
jQuery(function() {

	var refresh_tweets = function(posts) {
		jQuery.each(posts, function(idx, post) {
			if (!jQuery('[data-id="' + post.id + '"]').length) {
				jQuery('ul').prepend('<li data-id="' + post.id + '">' + post.content + '</li>');
			}
		});
		twttr.widgets.load();
	};

	setInterval(function() {
		jQuery.post('/tweetwall/', {
			refresh: 1
		}, refresh_tweets);
	}, 2000);

	// refresh_tweets();

});