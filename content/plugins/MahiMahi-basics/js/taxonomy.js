
attachment_taxonomy_remove = function(item) {
	ids = [];
	input = jQuery(item).parent().parent().parent().find('.attachment_taxonomy');
	jQuery(item).parent().remove();
	input.parent().find('li').each(function(){
		ids.push(jQuery(this).data('slug'));
	});
	input.next().val(ids.join(','));
	return false;
};

attachment_taxonomy_add = function(input, item) {
	input = jQuery(input);
	var term_id = item.id;
	var term_title = item.label;
	if ( term_id ) {
		var slug = input.attr('name');
		var id = input.attr('id');
		/*
		var html = '';
		html += '<li style="display: block;" id="attachment_products-'+term_id+'" data-slug="'+item.slug+'">';
		html += '<span class="link" onclick="attachment_taxonomy_remove(this)">[x]</span>&nbsp;';
		html += term_title;
		html += '</li>';
		input.parent().append(html);
		*/
		input.val('');
		input.attr('value', '');
		ids = [];
		input.parent().find('li').each(function(){
			ids.push(jQuery(this).data('slug'));
		});
		input.next().val(ids.join(','));
	}
};

checkNewMediaItems = function() {
	// console.log("checkNewMediaItems");
	jQuery('.compat-attachment-fields input[type="text"]:not(.ui-autocomplete-input)').each(function(){
		taxonomy = jQuery(this).data('taxonomy');
		jQuery(this).autocomplete({
			source: "/wp-content/plugins/MahiMahi-basics/autocomplete.php?taxonomy=" + taxonomy,
			select: function( event, ui ) {
				attachment_taxonomy_add(this, ui.item);
			}
		});

	});

};

jQuery(document).ready(function(){

//	jQuery(document).ajaxComplete(checkNewMediaItems);

//	checkNewMediaItems();


});
