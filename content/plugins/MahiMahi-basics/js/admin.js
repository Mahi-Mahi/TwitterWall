jQuery(document).ready(function() {

	jQuery('.tablenav .actions input.bigdrop').each(function(idx, elt) {
		jQuery(elt).select2({
			placeholder: jQuery(this).data('title'),
			minimumInputLength: 3,
			allowClear: true,
			ajax: {
				url: ajaxurl,
				quietMillis: 100,
				data: function(term, page) { // page is the one-based page number tracked by Select2
					return {
						action: 'mahi_wp_dropdown',
						taxonomy: jQuery(this).data('taxonomy'),
						q: term, //search term
						page_limit: 10, // page size
						page: page, // page number
					};
				},
				results: function(data, page) {
					var more = (page * 10) < data.length; // whether or not there are more results available
					// notice we return the value of more so Select2 knows if more results can be loaded
					return {
						results: data,
						more: more
					};
				}
			},
			dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
			escapeMarkup: function(m) {
				return m;
			},
			change: function(e) {
			},
		}).select2('data', jQuery(this).data('selected') ? {id: jQuery(this).data('selected'), text: jQuery(this).data('selected-title')} : null);
	});

});


