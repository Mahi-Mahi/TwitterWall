<?php

function mahibasics_load_plugins($plugins) {

	// 'safari','inlinepopups','spellchecker','paste','wordpress','media','fullscreen','wpeditimage','wpgallery','tabfocus',
	$mahi_plugins = array('nonbreaking','table', 'iframe');

	$plugins_path = MahiMahiBasics_URL . '/tinymce/plugins/';

	foreach( $mahi_plugins as $mahi_plugin )
		$plugins[$mahi_plugin] = $plugins_path . $mahi_plugin . '/editor_plugin.js';

	return $plugins;
}
add_filter( 'mce_external_plugins', 'mahibasics_load_plugins', 999 );


function mahibasics_tiny_mce_before_init( $initArray ) {
	if ( version_compare($wp_version, '3.8', '>') )
		return;


	//@see http://wiki.moxiecode.com/index.php/TinyMCE:Control_reference
	//   $initArray['theme_advanced_buttons1'] = 'forecolorpicker,styleselect,formatselect,bold,italic,|,bullist,numlist,|,image,|,link,unlink,|,pasteword,|,blockquote,|,wp_more';
	$initArray['theme_advanced_buttons1'] = 'styleselect,formatselect,bold,italic,|,bullist,numlist,|,image,|,link,unlink,|,pasteword,|,blockquote,|,wp_more,wp_page,|,iframe,wp_adv';
	$initArray['theme_advanced_buttons2'] = 'tablecontrols';
	// olivM: cette ligne désactive la fonction de liens internes de WP3.1 TODO: trouver pourquoi
	// inlinePopups fait planter le JS
	// media n'existe plus
	global $wp_version;
	if ( version_compare($wp_version, '3.8', '>') ):
		$initArray['plugins'] .= ',tabfocus,paste,media,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,-table,-nonbreaking,-iframe,wpfullscreen,paste,wordpress,fullscreen,wplink,wpeditimage,wpgallery,tabfocus,nonbreaking,table';
	else:
		$initArray['plugins'] .= ',inlinepopups,spellchecker,tabfocus,paste,media,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,-table,-nonbreaking,-iframe,wpfullscreen,safari,spellchecker,paste,wordpress,fullscreen,wplink,wpeditimage,wpgallery,tabfocus,nonbreaking,table';
	endif;
	$initArray['extended_valid_elements'] = "video[class|width|height|controls|preload|autoplay],iframe[align|longdesc|name|width|height|frameborder|scrolling|marginheight|marginwidth|src],div[style|class]";

	return $initArray;
}

if ( defined('MAHI_FIX_TINYMCE') ):
	// DEBUT CORRECTION DES SUPPRESSIONS DES BALISES P ET BR
	function MahiMahi_mce_options($init) {
		$init['apply_source_formatting'] = true;
		return $init;
	}
	add_filter( 'tiny_mce_before_init', 'MahiMahi_mce_options' );

	function MahiMahi_htmledit($c) {
		$c = str_replace( array('&amp;', '&lt;', '&gt;'), array('&', '<', '>'), $c );
		$c = wpautop($c);
		$c = htmlspecialchars($c, ENT_NOQUOTES);
		return $c;
	}
	add_filter('htmledit_pre', 'MahiMahi_htmledit', 999);

	function MahiMahi_tmce_replace() {
		?>
		<script type="text/javascript">
		if ( typeof(jQuery) != 'undefined' ) {
			jQuery('body').bind('afterPreWpautop', function(e, o){
				o.data = o.unfiltered
				.replace(/caption\]\[caption/g, 'caption] [caption')
				.replace(/<object[\s\S]+?<\/object>/g, function(a) {
					return a.replace(/[\r\n]+/g, ' ');
				});
			}).bind('afterWpautop', function(e, o){
				o.data = o.unfiltered;
			});
		}
		</script>
		<?php
	}
	add_action( 'after_wp_tiny_mce', 'MahiMahi_tmce_replace' );
	// FIN CORRECTION DES SUPPRESSIONS DES BALISES P ET BR
endif;



function mahi_the_content_tabs($the_content) {

	$content = preg_split("#<p class=\"tab\">#", $the_content);

	$the_content = array_shift($content) . PHP_EOL;

	foreach($content as $tab):

		if ( $tmp = preg_split("#<p><span id=\"more-\d+\"></span>\s*</p>#", $tab) ):
			$tab = $tmp[0];
			$after_tabs = $tmp[1];
		endif;

		$tmp = preg_split("#</p>#", $tab, 2);

		$tab_title = $tmp[0];
		$tab_slug = sanitize_title($tmp[0]);
		$tab_content = $tmp[1];

		$tabs[] = '<li class="tab-item"><a href="#tab-'.$tab_slug.'">'.$tab_title.'</a></li>';
		$tabs_content[] = '<div class="tab-pane" id="tab-'.$tab_slug.'">'.$tab_content.'</div><!-- #'.$tab_slug.' -->';

	endforeach;

	if (count($tabs) ):
		$the_content .= '<div class="content-tab">'.PHP_EOL.'<ul class="navbar" id="navbar-content">'.PHP_EOL.implode(PHP_EOL, $tabs).'</ul>'.PHP_EOL.'<div class="tab-content">'.PHP_EOL.implode(PHP_EOL, $tabs_content).'</div><!-- .tab-content --></div><!-- .content-tab -->'.PHP_EOL;
	endif;

	$the_content .= PHP_EOL;


	return $the_content.$after_tabs;
}

