<?php

if ( function_exists('icl_object_id') ):

	add_action('init', 'wpml_setlocale');
	function wpml_setlocale(){
		setlocale(LC_ALL, wpml_get_locale());
	}

	if ( ! function_exists('wpml_get_language')):
		// GET CURRENT LANGUAGE
		function wpml_get_language() {
			global $sitepress;
			$current_language = 'lang-'.$sitepress->get_current_language();
			return $current_language;
		}
	endif;

	function wpml_get_locale() {
		global $sitepress;
		$locale = $sitepress->locale();
		return $locale;
	}

	function wpml_other_lang(){
		global $sitepress;
		$current_language = $sitepress->get_current_language();
		if ( $current_language == 'en')
			$lang = 'fr';
		else
			$lang = 'en';
		return $lang;
	}


	function icl_filter($s) {
		$s = preg_replace("#\s*@\w+$#", '', $s);
		return preg_replace("#\s*@\w+$#", '', $s);
	}

	function wpml_page_path($path) {

		// logr("wpml_page_path($path)");
		// logr(get_page_by_path($path)->post_name);
		// logr(icl_object_id(get_page_by_path($path)->ID, 'page', true));

		return get_permalink(icl_object_id(get_page_by_path($path)->ID, 'page', true));

	}


	function wpml_term($term_id, $taxonomy, $language = null) {
		global $sitepress;

		if ( ! $language)
			$language = $sitepress->get_current_language();

		$translated_term_id = icl_object_id($term_id, $taxonomy, true, $language);

		$translated_term_object = get_term_by('id', $translated_term_id, $taxonomy);

		return $translated_term_object;
	}


	function wpml_post($post_id, $language = null) {
		global $sitepress;

		if ( ! $post_id )
			$post_id = get_the_ID();

		if ( ! $language)
			$language = $sitepress->get_current_language();

		$translated_post_id = icl_object_id($post_id, get_post_type($post_id), true, $language);

		return $translated_post_id;

		$translated_post_object = get_post('id', $translated_post_id, get_post_type($post_id));

		return $translated_post_object;
	}



	// DEBUG

	add_action('plugins_loaded', 'mahi_wpml_after_startup');
	function mahi_wpml_after_startup() {
		if ( is_local() ):
			global $sitepress;
			$language_domains = $sitepress->get_setting('language_domains');
			foreach($language_domains as $lang => $url)
				$language_domains[$lang] = str_replace('.web-staging.com', '.localhost', $url);
			$sitepress->set_setting('language_domains', $language_domains);
		endif;
	}


	add_filter( 'option_icl_sitepress_settings', 'mahi_wpml_option_icl_sitepress_settings');
	function mahi_wpml_option_icl_sitepress_settings($settings) {
		if ( is_local() ):
			foreach($settings['language_domains'] as $lang => $url)
				$settings['language_domains'][$lang] = str_replace('.web-staging.com', '.localhost', $url);
		endif;
		return $settings;
	}

endif;
