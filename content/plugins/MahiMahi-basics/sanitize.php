<?php

$mahi_sanitize_characters = array(
	'common' => array(
		'lower' => array(
			'à' => 'a',  'ô' => 'o',  'ď' => 'd',  'ḟ' => 'f',  'ë' => 'e',  'š' => 's',  'ơ' => 'o',
			'ă' => 'a',  'ř' => 'r',  'ț' => 't',  'ň' => 'n',  'ā' => 'a',  'ķ' => 'k',
			'ŝ' => 's',  'ỳ' => 'y',  'ņ' => 'n',  'ĺ' => 'l',  'ħ' => 'h',  'ṗ' => 'p',  'ó' => 'o',
			'ú' => 'u',  'ě' => 'e',  'é' => 'e',  'ç' => 'c',  'ẁ' => 'w',  'ċ' => 'c',  'õ' => 'o',
			'ṡ' => 's',  'ø' => 'o',  'ģ' => 'g',  'ŧ' => 't',  'ș' => 's',  'ė' => 'e',  'ĉ' => 'c',
			'ś' => 's',  'î' => 'i',  'ű' => 'u',  'ć' => 'c',  'ę' => 'e',  'ŵ' => 'w',  'ṫ' => 't',
			'ū' => 'u',  'č' => 'c',  'ö' => 'oe', 'è' => 'e',  'ŷ' => 'y',  'ą' => 'a',  'ł' => 'l',
			'ų' => 'u',  'ů' => 'u',  'ş' => 's',  'ğ' => 'g',  'ļ' => 'l',  'ƒ' => 'f',  'ž' => 'z',
			'ẃ' => 'w',  'ḃ' => 'b',  'å' => 'a',  'ì' => 'i',  'ï' => 'i',  'ḋ' => 'd',  'ť' => 't',
			'ŗ' => 'r',  'ä' => 'ae', 'í' => 'i',  'ŕ' => 'r',  'ê' => 'e',  'ü' => 'u',  'ò' => 'o',
			'ē' => 'e',  'ñ' => 'n',  'ń' => 'n',  'ĥ' => 'h',  'ĝ' => 'g',  'đ' => 'd',  'ĵ' => 'j',
			'ÿ' => 'y',  'ũ' => 'u',  'ŭ' => 'u',  'ư' => 'u',  'ţ' => 't',  'ý' => 'y',  'ő' => 'o',
			'â' => 'a',  'ľ' => 'l',  'ẅ' => 'w',  'ż' => 'z',  'ī' => 'i',  'ã' => 'a',  'ġ' => 'g',
			'ṁ' => 'm',  'ō' => 'o',  'ĩ' => 'i',  'ù' => 'u',  'į' => 'i',  'ź' => 'z',  'á' => 'a',
			'û' => 'u',  'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',  'ĕ' => 'e',  'ı' => 'i',
		),
		'upper' => array(
			'À' => 'A',  'Ô' => 'O',  'Ď' => 'D',  'Ḟ' => 'F',  'Ë' => 'E',  'Š' => 'S',  'Ơ' => 'O',
			'Ă' => 'A',  'Ř' => 'R',  'Ț' => 'T',  'Ň' => 'N',  'Ā' => 'A',  'Ķ' => 'K',  'Ĕ' => 'E',
			'Ŝ' => 'S',  'Ỳ' => 'Y',  'Ņ' => 'N',  'Ĺ' => 'L',  'Ħ' => 'H',  'Ṗ' => 'P',  'Ó' => 'O',
			'Ú' => 'U',  'Ě' => 'E',  'É' => 'E',  'Ç' => 'C',  'Ẁ' => 'W',  'Ċ' => 'C',  'Õ' => 'O',
			'Ṡ' => 'S',  'Ø' => 'O',  'Ģ' => 'G',  'Ŧ' => 'T',  'Ș' => 'S',  'Ė' => 'E',  'Ĉ' => 'C',
			'Ś' => 'S',  'Î' => 'I',  'Ű' => 'U',  'Ć' => 'C',  'Ę' => 'E',  'Ŵ' => 'W',  'Ṫ' => 'T',
			'Ū' => 'U',  'Č' => 'C',  'Ö' => 'OE', 'È' => 'E',  'Ŷ' => 'Y',  'Ą' => 'A',  'Ł' => 'L',
			'Ų' => 'U',  'Ů' => 'U',  'Ş' => 'S',  'Ğ' => 'G',  'Ļ' => 'L',  'Ƒ' => 'F',  'Ž' => 'Z',
			'Ẃ' => 'W',  'Ḃ' => 'B',  'Å' => 'A',  'Ì' => 'I',  'Ï' => 'I',  'Ḋ' => 'D',  'Ť' => 'T',
			'Ŗ' => 'R',  'Ä' => 'AE', 'Í' => 'I',  'Ŕ' => 'R',  'Ê' => 'E',  'Ü' => 'UE', 'Ò' => 'O',
			'Ē' => 'E',  'Ñ' => 'N',  'Ń' => 'N',  'Ĥ' => 'H',  'Ĝ' => 'G',  'Đ' => 'D',  'Ĵ' => 'J',
			'Ÿ' => 'Y',  'Ũ' => 'U',  'Ŭ' => 'U',  'Ư' => 'U',  'Ţ' => 'T',  'Ý' => 'Y',  'Ő' => 'O',
			'Â' => 'A',  'Ľ' => 'L',  'Ẅ' => 'W',  'Ż' => 'Z',  'Ī' => 'I',  'Ã' => 'A',  'Ġ' => 'G',
			'Ṁ' => 'M',  'Ō' => 'O',  'Ĩ' => 'I',  'Ù' => 'U',  'Į' => 'I',  'Ź' => 'Z',  'Á' => 'A',
			'Û' => 'U',  'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'İ' => 'I'
		)
	),
	'cyrillic' => array(
		'lower' => array(
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g','д' => 'd', 'ё' => 'jo',
			'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'ye', 'к' => 'k', 'л' => 'l',
			'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
			'ф' => 'f', 'х' => 'ch', 'ц' => 'z', 'ч' => 'tch', 'ш' => 'sch', 'щ' => 'stch', 'ы' => 'y', 'э' => 'e'
		),
		'upper' => array(
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'JE', 'Ё' => 'JO', 'Ж' => 'ZH', 'З' => 'Z',
			'И' => 'I', 'Й' => 'YE', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
			'У' => 'U', 'Ф' => 'F', 'Х' => 'CH', 'Ц' => 'Z', 'Ч' => 'TCH', 'Ш' => 'SCH', 'Щ' => 'STCH', 'Э' => 'E', 'Ю' => 'JU', 'Я' => 'JU'
		)
	),
	'hebrew' => array(
		'lower' => array(
			'״' => 'u', 'ת' => 't', 'ש' => 'f', 'ר' => 'r', 'ק' => 'q', 'צ' => 'c', 'פ' => 'p', 'ע' => 'e',
			'ס' => 's', 'נ' => 'n', 'מ' => 'm', 'ל' => 'l', 'כ' => 'k', 'י' => 'i', 'ט' => 'j', 'ח' => 'x',
			'ז' => 'z', 'ו' => 'w', 'ה' => 'h', 'ד' => 'd', 'ג' => 'g', 'ב' => 'b', 'א' => 'a'
		),
		'upper' => array()
	),
	'spanish' => array(
		'lower' => array(
			'á' => 'a', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'º' => 'o', 'ª' => 'a'
		),
		'upper' => array(
			'Á' => 'A', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N'
		)
	),
	'german' => array(
		'lower' => array(
			'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss'
		),
		'upper' => array(
			'Ä' => 'EA', 'Ö' => 'OE', 'Ü' => 'UE', 'ẞ' => 'SS'
		)
	)

);

function mahi_sanitize_clean($string, $type, $case) {

	if ($type === 'media') {
		list($dirname, $basename, $file_extension, $string) = array_values(pathinfo($string));
	}

	// Transliterate non-ASCII characters
	$string = mahi_sanitize_to_ascii($string, $case);

	// Remove all characters that are not the separator, a-z, 0-9, or whitespace
	$string = preg_replace('![^'.preg_quote('-').'a-z0-_9\s]+!', '', strtolower($string));

	// Replace all separator characters and whitespace by a single separator
	$string = preg_replace('!['.preg_quote('-').'\s]+!u', '-', $string);

	// Trim separators from the beginning and end
	$string = trim($string, '-');

	// Add back file extension, if applicable
	if ($type === 'media') $string = implode('.', array($string, $file_extension));

	// Return filtered string
	return $string;

}

function mahi_sanitize_to_ascii($string, $case = 0) {
	global $mahi_sanitize_characters;

	if ($case <= 0)
	{
		$characters = array_merge(
			$mahi_sanitize_characters['common']['lower'],
			$mahi_sanitize_characters['cyrillic']['lower'],
			$mahi_sanitize_characters['hebrew']['lower'],
			$mahi_sanitize_characters['spanish']['lower'],
			$mahi_sanitize_characters['german']['lower']
		);
		$string = str_replace(array_keys($characters), array_values($characters), $string);
	}

	if ($case >= 0)
	{
		$characters = array_merge(
			$mahi_sanitize_characters['common']['upper'],
			$mahi_sanitize_characters['cyrillic']['upper'],
			$mahi_sanitize_characters['hebrew']['upper'],
			$mahi_sanitize_characters['spanish']['upper'],
			$mahi_sanitize_characters['german']['upper']
		);
		$string = str_replace(array_keys($characters), array_values($characters), $string);
	}

	return $string;
}

function mahi_sanitize_title($title, $title_case = false) {
	return mahi_sanitize_clean($title, 'title', $title_case);
}

function mahi_sanitize_file_name($name, $media_case = false) {
	return mahi_sanitize_clean($name, 'media', $media_case);
}

add_filter('sanitize_title', 'mahi_sanitize_title', 0);
add_filter('sanitize_file_name', 'mahi_sanitize_file_name', 0);
