var maps = [];
var markers = [];

function image_field_remove() {
	if (!jQuery('.image-remove-link').length) return undefined;

	jQuery('.image-remove-link').each(function() {
		var target = jQuery(this).data('target');
		if (jQuery('#' + target + '_img:hidden').length) jQuery(this).hide();
	});
	jQuery('.image-remove-link').click(function() {
		var url = '/wp-content/plugins/MahiMahi-basics/custom_types/ajax-images.php';
		var data = {
			post_id: jQuery(this).data('id'),
			field: jQuery(this).data('field'),
			action: 'delete'
		};

		var item = jQuery(this);
		var target = item.data('target');
		jQuery.post(url, data, function() {
			//cacher les éléments qui sont associés à l'affichage de l'image
			var reverse = jQuery('#reverse_url').text();
			item.siblings('.thickbox').attr('href', reverse);
			jQuery('#' + target).val('');
			item.siblings('.thickbox').find('img').fadeOut();
			item.siblings('.thickbox').find('span').fadeIn();
			item.fadeOut();
		});
	});
}

function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		results = regex.exec(location.search);
	return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

jQuery(window).load(function() {

	image_field_remove();
	jQuery('.favorite-toggle').click(function(event) {
		var favoriteInside = jQuery(this).next('.favorite-inside');
		favoriteInside.width(favoriteInside.closest('.favorite-actions').width() - 4);
		favoriteInside.slideToggle(100);
		jQuery(this).siblings('.favorite-first').toggleClass('slide-down');
	});

	//DATE
	if (jQuery.datepicker) jQuery.datepicker.setDefaults({
		dateFormat: 'yy/mm/dd',
		showAnim: 'slideDown',
		regional: "fr"
	});

	jQuery(".jquery-date").datepicker();

	jQuery(".jquery-datetime").datetimepicker({
		addSliderAccess: true,
		sliderAccessArgs: {
			touchonly: false
		}
	});

	jQuery('.custom-type.hidden').parent().parent().hide();

	// remove current file
	jQuery('button.remove').click(function() {
		jQuery(this).parent().remove();
	});

	// POST REFERENCE
	jQuery('.post_reference.autocomplete').each(function(i) {
		var url = admin_mahi_vars.autocomplete_url;
		url += "&query=" + jQuery(this).find('.autocomplete_query').eq(0).val();
		// WPML
		url += "&lang=" + getParameterByName('lang');
		console.log(url);
		jQuery(this).find('.search_field').autocomplete({
			source: url,
			select: function(event, ui) {
				post_reference_add(this, ui.item);
			}
		});
		if (typeof(jQuery(this).find('ul').sortable) != 'undefined') jQuery(this).find('ul').sortable({
			cursor: 'move'
		});
	});

	post_reference_remove = function(item) {
		jQuery(item).parent().remove();
		return false;
	};

	post_reference_add = function(input, item, isSingle) {
		var post_id = item.id;
		var post_title = item.label;
		if (post_id) {
			var slug = jQuery(input).attr('name');
			var id = jQuery(input).attr('id');
			var html = '';
			html += '<li class="post_reference" id="post_reference-' + post_id + '">';
			html += '<input type="hidden" name="' + id + '[]" value="' + post_id + '">';
			html += '<span class="link" onclick="post_reference_remove(this)">[x]</span>&nbsp;';
			html += post_title;
			html += '</li>';
			if (isSingle) jQuery(input).parent().find('ul').html(html);
			else jQuery(input).parent().find('ul').append(html);
			jQuery(input).val('');
			jQuery(input).attr('value', '');
		}
	};

	var previous_zipcode_value = null;
	UpdateZipcodeIfMissing = function(slug) {
		if (jQuery("#addresspicker_" + slug + "_zipcode").val() === previous_zipcode_value) {
			return;
		}
		previous_zipcode_value = jQuery("#addresspicker_" + slug + "_zipcode").val();

		if (jQuery("#addresspicker_" + slug + "_zipcode").val() !== 'false') {
			return;
		}

		var url = "http://maps.googleapis.com/maps/api/geocode/json?address=" + jQuery("#addresspicker_" + slug + "_lat").val() + "," + jQuery("#addresspicker_" + slug + "_lng").val() + "&sensor=false";
		jQuery.getJSON(url, function() {})
			.done(function(data) {
				for (var key in data = data.results[0].address_components) {
					if (data[key].types[0] == 'postal_code') {
						if (typeof data[key].short_name !== "undefined") {
							jQuery("#addresspicker_" + slug + "_zipcode").val(data[key].short_name);
							console.log('update zipcode');
						}
					}
				}
			});

	}

	// ADDRESS
	jQuery(".custom-type.address").each(function() {
		var slug = jQuery(this).data('slug');
		var addresspickerMap = jQuery("#addresspicker_" + slug).addresspicker({
			elements: {
				map: "#addresspicker_" + slug + "_map",
				lat: "#addresspicker_" + slug + "_lat",
				lng: "#addresspicker_" + slug + "_lng",
				postal_code: "#addresspicker_" + slug + "_zipcode",
				locality: "#addresspicker_" + slug + "_locality",
				country: "#addresspicker_" + slug + "_country"
			}
		});
		if (jQuery("#addresspicker_" + slug + "_lat").val() && jQuery("#addresspicker_" + slug + "_lng").val()) {
			var addresspickerMarker = addresspickerMap.addresspicker("marker");
			addresspickerMarker.setVisible(true);
			addresspickerMap.addresspicker("updatePosition");
		}

		window.setInterval("UpdateZipcodeIfMissing('" + slug + "')", 500);

	});

	// GEO
	if (jQuery(".geo input[id$='_title']").length) {
		jQuery(".geo input[id$='_title']").keyup(function(e) {
			jQuery(this).next().val(jQuery(this).val());
		});
	}

	geo_set = function(item) {

		text_field = jQuery(item).parent().find("input.formatted");
		slug = jQuery(text_field).attr('name');

		map = maps[slug];

		geo_locate(map, text_field, true);
	};

	geo_locate = function(map, textfield, force) {

		address = jQuery(text_field).val();
		slug = jQuery(text_field).attr('name');

		if (!address) return;

		var lat = jQuery('#geo_' + slug + '_lat').val();
		var lng = jQuery('#geo_' + slug + '_lng').val();

		loc = new google.maps.LatLng(lat, lng);

		if (force || isNaN(lat) || lat === '' || isNaN(lng) || lng === '') {

			geocoder = new google.maps.Geocoder();
			geocoder.geocode({
				address: address
			}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {

					loc = results[0].geometry.location;

					result = results[0];

					map.setCenter(loc);

					address_components = {};
					for (var idx in result.address_components) {
						if (jQuery.inArray(result.address_components[idx].types[0], ['street_number', 'route', 'postal_code', 'locality', 'country']) > -1) {
							address_components[result.address_components[idx].types[0]] = result.address_components[idx].long_name;
						}
					}
					jQuery('#geo_' + slug + '_components').val(JSON.stringify(address_components));
					jQuery('#geo_' + slug + '_lat').val(loc.lat());
					jQuery('#geo_' + slug + '_lng').val(loc.lng());
					jQuery('#geo_' + slug).val(results[0].formatted_address);

				}
				/*else {
					alert("Geocode was not successful for the following reason: " + status);
				}*/
			});
		}

		if (!markers[slug]) {
			var marker = new google.maps.Marker({
				map: map,
				position: loc
			});
			markers[slug] = marker;
		}

		markers[slug].setPosition(loc);
	};

	jQuery('p.geo').each(function(i) {
		text_field = jQuery(this).find("input.formatted");

		slug = text_field.attr('name');

		var lat = jQuery('#geo_' + slug + '_lat').val();
		var lng = jQuery('#geo_' + slug + '_lng').val();

		if (isNaN(lat) || lat === '') lat = 48.8638924;
		if (isNaN(lng) || lng === '') lng = 2.3423655;

		var latlng = new google.maps.LatLng(lat, lng);

		var myOptions = {
			zoom: 13,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		var map = new google.maps.Map(document.getElementById('geo_' + slug + '_map'), myOptions);
		google.maps.event.addListener(map, 'tilesloaded', function() {
			geo_locate(map, text_field);
		});

		maps[slug] = map;
	});

	if (jQuery('.editor_template input').length) {
		var head = jQuery(".mceIframeContainer iframe").contents().find("head");

		head.append(jQuery("<link/>", {
			rel: "stylesheet",
			id: 'editor-templates',
			href: '/wp-content/plugins/MahiMahi-basics/custom_types/css/editor-templates.css',
			type: "text/css"
		}));

		jQuery('.template-block').click(function() {
			var block = jQuery(this).attr('block');
			var current = jQuery(this).parent('ul').parent('.editor_template').children('.editor_template input');
			if (current.attr('editor-template') !== '') {
				var body = jQuery(".mceIframeContainer iframe").contents().find("body").first();
				body.append(jQuery('<div class="template-block-add">'));
				var new_add = body.children(".template-block-add").last();
				new_add.load(jQuery(current).attr('editor-template') + ' .' + block + ':last').last();
				new_add.hover(
					function() {
						jQuery(this).children().first().prepend(jQuery('<span class="tools remove"></span><span class="tools up" ></span><span class="tools down" ></span>'));
					},
					function() {
						jQuery(this).children().first().find("span.tools").remove();
					});
			}
		});

		jQuery(".mceIframeContainer iframe").contents().find('body').delegate('.remove', 'click', function() {
			action = jQuery(this).parent().parent('.template-block-add');
			answer = confirm("Etes-vous sur de vouloir supprimer ce block?");
			if (answer) action.remove();
		});

		jQuery(".mceIframeContainer iframe").contents().find('body').delegate('.up', 'click', function() {
			item = jQuery(this).parent().parent('.template-block-add');
			if (item.prev('.template-block-add'))
				item.prev('.template-block-add').before(item);
		});

		jQuery(".mceIframeContainer iframe").contents().find('body').delegate('.down', 'click', function() {
			item = jQuery(this).parent().parent('.template-block-add');
			if (item.next('.template-block-add'))
				item.next('.template-block-add').after(item);
		});

		jQuery(".mceIframeContainer iframe").contents().find(".template-block-add").hover(
			function() {
				jQuery(this).children().first().prepend(jQuery('<span class="tools remove" ></span><span class="tools up" ></span><span class="tools down" ></span>'));
			}, function() {
				jQuery(this).children().first().find("span.tools").remove();
			});

		jQuery('.template-block').each(function() {
			jQuery(this).css('cursor', 'pointer');
		});

		removeEditorStyles = function() {
			jQuery('.editor_template input').each(function() {
				head.find("#" + jQuery(this).attr('id')).remove();
			});
		};

		jQuery('.editor_template input:checked').each(function() {
			removeEditorStyles();
			if (jQuery(this).attr('editor-type') == 'style') {
				head.append(jQuery("<link/>", {
					rel: "stylesheet",
					id: jQuery(this).attr('id'),
					href: jQuery(this).attr('editor-style'),
					type: "text/css"
				}));
			} else if (jQuery(this).attr('editor-type') == 'template') {
				var body = jQuery(".mceIframeContainer iframe").contents().find("body");
				body.load(jQuery(this).attr('editor-template'));
			} else {
				head.append(jQuery("<link/>", {
					rel: "stylesheet",
					id: jQuery(this).attr('id'),
					href: jQuery(this).attr('editor-style'),
					type: "text/css"
				}));
				if (jQuery(".mceIframeContainer iframe").contents().find('body').html().length == 30) {
					var iframe_body = jQuery(".mceIframeContainer iframe").contents().find("body");
					iframe_body.load(jQuery(this).attr('editor-template'));
				}
			}
		});

		jQuery('.editor_template input').click(function() {
			var answer = confirm("Etes-vous sur de vouloir changer de template ?\n(L'intégralité du contenu de l'éditeur va être remplacé)");
			var current;
			if (answer) {
				removeEditorStyles();
				if (jQuery(this).attr('editor-type') == 'style') {
					head.append(jQuery("<link/>", {
						rel: "stylesheet",
						id: jQuery(this).attr('id'),
						href: jQuery(this).attr('editor-style'),
						type: "text/css"
					}));
				} else if (jQuery(this).attr('editor-type') == 'template') {
					current = this;
					var body = jQuery(".mceIframeContainer iframe").contents().find("body");
					jQuery.ajax({
						type: "GET",
						url: jQuery(this).attr('editor-template'),
						error: function(msg) {
							alert("Error !: " + msg);
						},
						success: function(data) {
							var div = document.createElement('div');
							div.innerHTML = data;
							jQuery(current).parent('.editor_template').children('ul').children('.template-block').each(function() {
								jQuery(div).find('.' + jQuery(this).attr('block')).each(function() {
									jQuery(this).remove();
								});
							});
							body.html(jQuery(div).html());
						}
					});
				} else {
					current = this;
					head.append(jQuery("<link/>", {
						rel: "stylesheet",
						id: jQuery(this).attr('id'),
						href: jQuery(this).attr('editor-style'),
						type: "text/css"
					}));
					var iframe_body = jQuery(".mceIframeContainer iframe").contents().find("body");
					jQuery.ajax({
						type: "GET",
						url: jQuery(this).attr('editor-template'),
						error: function(msg) {
							alert("Error !: " + msg);
						},
						success: function(data) {
							var div = document.createElement('div');
							div.innerHTML = data;
							jQuery(current).parent('.editor_template').children('ul').children('.template-block').each(function() {
								jQuery(div).find('.' + jQuery(this).attr('block')).each(function() {
									jQuery(this).remove();
								});
							});
							iframe_body.html(jQuery(div).html());
						}
					});
				}
			}
		});
	}
});