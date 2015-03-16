<?php

function isAjax() {
  return defined('IS_AJAX');
}

if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
	define('IS_AJAX', true);

if ( !function_exists('get_caller')):
function get_caller() {
	// requires PHP 4.3+
	if ( !is_callable('debug_backtrace') )
		return '';

	$bt = debug_backtrace();
	$caller = array();

	$bt = array_reverse( $bt );
	foreach ( (array) $bt as $call ) {
		if ( isset($call['function']) ) {
			$function = $call['function'];
			if ( isset($call['file']) )
				$function .= " (".str_replace($_SERVER['DOCUMENT_ROOT'], '', $call['file']);
			if ( isset($call['line']) )
				$function .= ",".$call['line'].")";
		}
		if ( isset( $call['class'] ) )
			$function = $call['class'] . "->$function";
		$caller[] = $function;
	}
	$caller = join( ', ', $caller );

	return $caller;
}
endif;

if ( ! function_exists('show_select')):
function show_select($args = array()) {

		$default_args = array(
							'id'       => false,
							'class'    => false,
							'name'     => false,
							'options'  => array(),  // array(13 => 'toto', ... );
							'selected' => false
		);

		extract(array_merge($default_args, $args));
		?>
		<select <?php if($name) echo ' name="'.$name.'"' ?><?php if($class)echo ' '.$class;?><?php if($id)echo ' id="'.$id.'"';?>>
			<?php foreach($options as $value => $content):?>
				<option value="<?php echo $value;?>"<?php if($selected==$value)echo ' selected="selected"';?>>
					<?php echo $content;?>
				</option>
			<?php endforeach;?>
		</select>
		<?php
}
endif;



if ( ! function_exists('trace')):
function trace($s = null) {

	if ( !is_callable('debug_backtrace') )
		return '';

	$bt = debug_backtrace();
	$call = current($bt);
	if ( isset($call['function']) ) {
		if ( isset($call['file']) )
			$function .= str_replace($_SERVER['DOCUMENT_ROOT'], '', $call['file']);
		if ( isset($call['line']) )
			$function .= " : ".$call['line'];
	}
	logr($function.($s?' : '.$s:'').' - '.$_SERVER['REQUEST_URI']);
}
endif;

if ( ! function_exists('describe')):
function describe($s) {
	if ( is_array($s) ):
		return print_r(array_keys($s));
	else:
		if ( is_object($s) ) :
			return print_r(get_object_vars($s), true);
		else:
			return print_r($s, true);
		endif;
	endif;
}
endif;

define('CRON_LOG', '/tmp/'.$_SERVER['SERVER_NAME'].'-cron-log');

if ( !function_exists('logr')):
	function logr($s, $custom_log = null) {
		if ( is_array($s) or is_object($s) or is_resource($s)):
			logr(print_r($s, true));

			if ( defined('DEBUG_LOGR')):

				$bt = debug_backtrace();
				$call = current($bt);
				if ( isset($call['function']) ) {
					if ( isset($call['file']) )
						$function .= str_replace($_SERVER['DOCUMENT_ROOT'], '', $call['file']);
					if ( isset($call['line']) )
						$function .= " : ".$call['line'];
				}
				error_log($function.($s?' : '.$s:'').' - '.$_SERVER['REQUEST_URI']);

			endif;
		else:
			if ( defined('WP_CLI') ):
				print $s."\n";
			else:
//				if ( $custom_log && defined($custom_log) )
//					error_log(date('[Y-m-d H:i:s] ').$s."\n", 3, constant($custom_log));
//				else
					// error_log(get_caller());
					error_log($s);
			endif;
		endif;
	}
endif;

if ( !function_exists('xmpr')):
	function xmpr($s) {
		if ( is_dev() || is_admin() || defined('WP_CLI') ):
			if ( is_array($s) or is_object($s)):
				xmpr(print_r($s, true));
			else:
				if ( defined('WP_CLI') )
					print $s."\n";
				else
					print "<xmp style='word-wrap:break-word;'>".$s."</xmp>";
			endif;
		else: // prod
			logr($s);
		endif;
  }
endif;

if ( !function_exists('txtmr')):
  function txtmtr($s) {
	print "<a href='txtmt:".$s."'>".$s."</a>";
  }
endif;

if ( !function_exists('commentr')):
  function commentr($s) {
		print "\n<!--\n";
		print_r($s);
		print "\n-->\n";
  }
endif;

if ( !function_exists('is_dev')):
function is_dev() {
	if ( defined('IS_LOCAL') || defined('IS_DEV'))
		return true;
	$suffix = array(".localhost", ".staging.mahi-mahi.fr", ".staging-1.mahi-mahi.fr", ".staging-2.mahi-mahi.fr", ".mahi-mahi.fr", ".web-staging.com", "dev.", "dev-", "preprod.", "preprod-");
	foreach($suffix as $s)
		if ( preg_match("#".$s."#", $_SERVER['SERVER_NAME']) )
			return $s;
}
endif;

if ( !function_exists('is_staging')):
function is_staging() {
	$suffix = array(".web-staging.com");
	foreach($suffix as $s)
		if ( preg_match("#".$s."#", $_SERVER['SERVER_NAME']) )
			return $s;
}
endif;

if ( !function_exists('is_local')):
function is_local() {
	if ( defined('IS_LOCAL'))
		return true;
	$suffix = array(".localhost");
	if ( preg_match("#^(192|127)\.#", $_SERVER['SERVER_NAME']) )
		return true;
	foreach($suffix as $s)
		if ( strstr($_SERVER['SERVER_NAME'], $s) )
			return $s;
}
endif;


// better use wp_list_pluck
if ( !function_exists('collect')):
function collect($stuff, $slug) {
	return wp_list_pluck($stuff, $slug);
	// $res = array();
	// foreach($stuff as $s)
	//   if (is_array($s))
	// 	  $res[] = $s[$slug];
	//   else
	// 	  $res[] = $s->$slug;
	// return $res;
}
endif;

if ( !function_exists('index_by')):
function index_by($array, $key) {
  $res = array();

  foreach($array as $elt) {
	if ( is_array($elt) )
	  $k = $elt[$key];
	if ( is_object($elt) )
	  $k = $elt->$key;
	$res[$k] = $elt;
  }

  return $res;
}
endif;

function basics_the_time($post_id = null, $d = null) {
	if ( $post_id ):
		$post_date = get_post($post_id)->post_date;
	else:
		global $post;
		$post_date = $post->post_date;
	endif;
	print strftime('<time class="published updated" datetime="%Y-%m-%d">'.$d.'</time>', strtotime($post->post_date));
}

// do a strptime and return a timestamp ( in place of an array )
function strparsetime($s, $f) {
	$t = strptime($s, $f);
	return mktime($t['tm_hour'], $t['tm_min'], $t['tm_sec'], $t['tm_mon'] + 1, $t['tm_mday'], $t['tm_year'] + 1900);
}


if(!function_exists('neat_trim')) :
function neat_trim($text, $len = 50, $delim = '...', $force = false) {

	if( (mb_strlen($text) > $len) ) {

		$whitespaceposition = mb_strpos($text," ",$len)-1;

				if ($force)
					$whitespaceposition =  $len-1;

		if( $whitespaceposition > 0 ) {
			$chars = count_chars(mb_substr($text, 0, ($whitespaceposition+1)), 1);
			if ($chars[ord('<')] > $chars[ord('>')])
				$whitespaceposition = mb_strpos($text,">",$whitespaceposition)-1;
			$text = mb_substr($text, 0, ($whitespaceposition+1));
		}

		// close unclosed html tags
		if( preg_match_all("|<([a-zA-Z]+)|",$text,$aBuffer) ) {

			if( !empty($aBuffer[1]) ) {

				preg_match_all("|</([a-zA-Z]+)>|",$text,$aBuffer2);

				if( count($aBuffer[1]) != count($aBuffer2[1]) ) {

					foreach( $aBuffer[1] as $index => $tag ) {

						if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag)
							$text .= '</'.$tag.'>';
					}
				}
			}
		}
				$text .= $delim;
	}
	return $text;
}
endif;

function array_qsort (&$array, $column=0, $order=SORT_ASC, $first=0, $last= -2)
{
  // $array  - the array to be sorted
  // $column - index (column) on which to sort
  //          can be a string if using an associative array
  // $order  - SORT_ASC (default) for ascending or SORT_DESC for descending
  // $first  - start index (row) for partial array sort
  // $last  - stop  index (row) for partial array sort
  // $keys  - array of key values for hash array sort

  if (!is_array($array)) return array();

  $keys = array_keys($array);
  if($last == -2) $last = count($array) - 1;
  if($last > $first) {
   $alpha = $first;
   $omega = $last;
   $key_alpha = $keys[$alpha];
   $key_omega = $keys[$omega];
   $guess = $array[$key_alpha][$column];
   while($omega >= $alpha) {
	 if($order == SORT_ASC) {
	   while($array[$key_alpha][$column] < $guess) {$alpha++; $key_alpha = $keys[$alpha]; }
	   while($array[$key_omega][$column] > $guess) {$omega--; $key_omega = $keys[$omega]; }
	 } else {
	   while($array[$key_alpha][$column] > $guess) {$alpha++; $key_alpha = $keys[$alpha]; }
	   while($array[$key_omega][$column] < $guess) {$omega--; $key_omega = $keys[$omega]; }
	 }
	 if($alpha > $omega) break;
	 $temporary = $array[$key_alpha];
	 $array[$key_alpha] = $array[$key_omega]; $alpha++;
	 $key_alpha = $keys[$alpha];
	 $array[$key_omega] = $temporary; $omega--;
	 $key_omega = $keys[$omega];
   }
   array_qsort ($array, $column, $order, $first, $omega);
   array_qsort ($array, $column, $order, $alpha, $last);
  }
}

function get_user_id() {
	global $current_user;

	if ( $current_user->ID )
		return $current_user->ID;

	if ( isset($_COOKIE['user_id']) )
		return $_COOKIE['user_id'];

	$user_id = uniqid();

	@setcookie('user_id', $user_id, time()+(3600*24*365), '/', $_SERVER['SERVER_NAME']);

	return $user_id;
}
//add_action('init', 'get_user_id');

function get_cookie_user_id() {
	if ( isset($_COOKIE['user_id']) )
		return $_COOKIE['user_id'];

	$user_id = uniqid();

	@setcookie('user_id', $user_id, time()+(3600*24*365), '/', $_SERVER['SERVER_NAME']);

	return $user_id;
}


function diff($old, $new){
	foreach($old as $oindex => $ovalue){
		$nkeys = array_keys($new, $ovalue);
		foreach($nkeys as $nindex){
			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
				$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
			if($matrix[$oindex][$nindex] > $maxlen){
				$maxlen = $matrix[$oindex][$nindex];
				$omax = $oindex + 1 - $maxlen;
				$nmax = $nindex + 1 - $maxlen;
			}
		}
	}
	if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
	return array_merge(
		diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
		array_slice($new, $nmax, $maxlen),
		diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}

function htmlDiff($old, $new){
	$diff = diff(explode(' ', $old), explode(' ', $new));
	foreach($diff as $k){
		if(is_array($k))
			$ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
			(!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
		else $ret .= $k . ' ';
	}
	return $ret;
}

function LevenshteinDistance($s1, $s2)
{
  $sLeft = (strlen($s1) > strlen($s2)) ? $s1 : $s2;
  $sRight = (strlen($s1) > strlen($s2)) ? $s2 : $s1;
  $nLeftLength = strlen($sLeft);
  $nRightLength = strlen($sRight);
  if ($nLeftLength == 0)
	return $nRightLength;
  else if ($nRightLength == 0)
	return $nLeftLength;
  else if ($sLeft === $sRight)
	return 0;
  else if (($nLeftLength < $nRightLength) && (strpos($sRight, $sLeft) !== FALSE))
	return $nRightLength - $nLeftLength;
  else if (($nRightLength < $nLeftLength) && (strpos($sLeft, $sRight) !== FALSE))
	return $nLeftLength - $nRightLength;
  else {
	$nsDistance = range(1, $nRightLength + 1);
	for ($nLeftPos = 1; $nLeftPos <= $nLeftLength; ++$nLeftPos)
	{
	  $cLeft = $sLeft[$nLeftPos - 1];
	  $nDiagonal = $nLeftPos - 1;
	  $nsDistance[0] = $nLeftPos;
	  for ($nRightPos = 1; $nRightPos <= $nRightLength; ++$nRightPos)
	  {
		$cRight = $sRight[$nRightPos - 1];
		$nCost = ($cRight == $cLeft) ? 0 : 1;
		$nNewDiagonal = $nsDistance[$nRightPos];
		$nsDistance[$nRightPos] =
		  min($nsDistance[$nRightPos] + 1,
			  $nsDistance[$nRightPos - 1] + 1,
			  $nDiagonal + $nCost);
		$nDiagonal = $nNewDiagonal;
	  }
	}
	return $nsDistance[$nRightLength];
  }
}

function br2nl($s) {

	preg_replace("#<br[^>]*>#", "\n", $s);

	return $s;
}

// S or NOT (affichage du s)
function sornot($value, $s = 's') {
	if ($value > 1) return $s;
}

function plural($nb, $zero, $one, $many, $show = true) {
	switch($nb):
		case 0:
			$s = $zero;
		break;
		case 1:
			$s = $one;
		break;
		default:
			$s = sprintf($many, $nb);
		break;
	endswitch;
	if ( $show )
		print $s;
	return $s;
}

// dist is in meters
function rounded_distance($dist) {
	$dist = intval($dist);

	if ( $dist < 150 ):
		return "Moins de 150m";
	elseif( $dist < 500 ):
		return "Moins de 500m";
	elseif ( $dist < 1000 ):
		return "Moins d'un kilomètre";
	else:
		$dist = intval($dist / 1000);
		if ( $dist < 20 )
			return $dist." kms";
		elseif ( $dist < 50 )
			return "Moins de ".roundTo($dist, 5)." kms";
		elseif ( $dist < 100 )
			return "Moins de ".roundTo($dist, 10)." kms";
		elseif ( $dist < 250 )
			return "Moins de ".roundTo($dist, 20)." kms";
		elseif ( $dist < 500 )
			return "Moins de ".roundTo($dist, 50)." kms";
		elseif ( $dist < 1000 )
			return "environ ".roundTo($dist, 100)." kms";
		elseif ( $dist < 2500 )
			return "Environ ".roundTo($dist, 250)." kms";
		elseif ( $dist < 5000 )
			return "Environ ".roundTo($dist, 500)." kms";
		else
			return "Environ ".roundTo($dist, 1000)." kms";
	endif;
}
function roundTo($number, $to){
//    return round($number/$to, 0)* $to;
	$to = 1 / $to;
	return (ceil($number * $to) / $to);
}

function shuffle_assoc(&$array) {
	$keys = array_keys($array);

	shuffle($keys);

	foreach($keys as $key) { $new[$key] = $array[$key]; }

	$array = $new;

	return true;
}

function httpize($s) {
	if ( ! preg_match("#^https?://#", $s))
		$s = "http://".$s;
	return $s;
}


function strlpad( $s, $l, $p = ' ' ) { return str_pad( $s, $l, $p, STR_PAD_LEFT ); }


function html2a ( $html ) {
  if ( !preg_match_all( '
@
\<\s*?(\w+)((?:\b(?:\'[^\']*\'|"[^"]*"|[^\>])*)?)\>
((?:(?>[^\<]*)|(?R))*)
\<\/\s*?\\1(?:\b[^\>]*)?\>
|\<\s*(\w+)(\b(?:\'[^\']*\'|"[^"]*"|[^\>])*)?\/?\>
@uxis', $html = trim($html), $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) )
	return $html;
  $i = 0;
  $ret = array();
  foreach ($m as $set) {
	if ( strlen( $val = trim( substr($html, $i, $set[0][1] - $i) ) ) )
	  $ret[] = $val;
	$val = $set[1][1] < 0
	  ? array( 'tag' => strtolower($set[4][0]) )
	  : array( 'tag' => strtolower($set[1][0]), 'val' => html2a($set[3][0]) );
	if ( preg_match_all( '
/(\w+)\s*(?:=\s*(?:"([^"]*)"|\'([^\']*)\'|(\w+)))?/usix
', isset($set[5]) && $set[2][1] < 0
  ? $set[5][0]
  : $set[2][0]
  ,$attrs, PREG_SET_ORDER ) ) {
	  foreach ($attrs as $a) {
		$val['attr'][$a[1]]=$a[count($a)-1];
	  }
	}
	$ret[] = $val;
	$i = $set[0][1]+strlen( $set[0][0] );
  }
  $l = strlen($html);
  if ( $i < $l )
	if ( strlen( $val = trim( substr( $html, $i, $l - $i ) ) ) )
	  $ret[] = $val;
  return $ret;
}

function sanitize($s) {
	$synonym = unhtmlentities($s);
	$synonym = iconv("UTF-8", "ASCII//TRANSLIT", $synonym);
	$synonym = preg_replace("#[^\w\d\ \-\.\&]#", '', $synonym);
	$synonym = strtolower($synonym);
	return $synonym;
}

function sanitize_js_content($content) {
	$content = preg_replace("#[\r\n]#", " ", $content);
	$content = preg_replace("#>\s+<#", "> <", $content);
	return $content;
}

function unhtmlentities($string) {
	// replace numeric entities
	$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	$string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
	// replace literal entities
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($string, $trans_tbl);
}

function get_mime_type($file) {
	if ( function_exists('finfo_open') ):
		$finfo = finfo_open(FILEINFO_MIME);//, "/usr/share/file/magic.mgc");
		$mime = finfo_file($finfo, $file);
		finfo_close($finfo);
	else:
		$mime = shell_exec("file -bI '".$file."'");
	endif;
	$mime = preg_replace('|;.*$|', '', $mime);
	return $mime;
}

function get_mime_type_extension($mime) {

  $mime_types = array(

	  'txt' => 'text/plain',
	  'htm' => 'text/html',
	  'html' => 'text/html',
	  'php' => 'text/html',
	  'css' => 'text/css',
	  'js' => 'application/javascript',
	  'json' => 'application/json',
	  'xml' => 'application/xml',
	  'swf' => 'application/x-shockwave-flash',
	  'flv' => 'video/x-flv',

	  // images
	  'png' => 'image/png',
	  'jpg' => 'image/jpeg',
	  'jpeg' => 'image/jpeg',
	  'jpe' => 'image/jpeg',
	  'gif' => 'image/gif',
	  'bmp' => 'image/bmp',
	  'ico' => 'image/vnd.microsoft.icon',
	  'tiff' => 'image/tiff',
	  'tif' => 'image/tiff',
	  'svg' => 'image/svg+xml',
	  'svgz' => 'image/svg+xml',

	  // archives
	  'zip' => 'application/zip',
	  'rar' => 'application/x-rar-compressed',
	  'exe' => 'application/x-msdownload',
	  'msi' => 'application/x-msdownload',
	  'cab' => 'application/vnd.ms-cab-compressed',

	  // audio/video
	  'mp3' => 'audio/mpeg',
	  'qt' => 'video/quicktime',
	  'mov' => 'video/quicktime',

	  // adobe
	  'pdf' => 'application/pdf',
	  'psd' => 'image/vnd.adobe.photoshop',
	  'ai' => 'application/postscript',
	  'eps' => 'application/postscript',
	  'ps' => 'application/postscript',

	  // ms office
	  'doc' => 'application/msword',
	  'rtf' => 'application/rtf',
	  'xls' => 'application/vnd.ms-excel',
	  'ppt' => 'application/vnd.ms-powerpoint',

	  // open office
	  'odt' => 'application/vnd.oasis.opendocument.text',
	  'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
  );

	foreach($mime_types as $k => $v)
		if ( $mime == $v)
			return $k;

}

function array_flatten($a, $f = array() ) {
  if ( ! $a || ! is_array($a) )
		return '';
  foreach($a as $k => $v) {
	if ( is_array($v) )
			$f = array_flatten($v, $f);
	else
			$f[$k] = $v;
  }
  return $f;
}

function flatten($a) {
    foreach( $a as $k => $v )
    	$a[$k] = (array) $v;
    return call_user_func_array(array_merge, $a);
}

/* PHP 5.3 only
function flatten(array $array) {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}
*/

function wp_remote($url, $args = null, $cache = true, $expire = 3600) {
	if ( $cache ):
		$md5 = md5($url . serialize($args));
		if ( false === ( $content = get_transient($md5) ) ) :
			$request = wp_remote_request($url, $args);
			$content = wp_remote_retrieve_body($request);
			set_transient($md5, $content, $expire);
		endif;
		return $content;
	else:
		$request = wp_remote_request($url, $args);
		return wp_remote_retrieve_body($request);
	endif;
}

function url_exists($url) {
	// Version 4.x supported
	$handle   = curl_init($url);
	if (false === $handle):
		logr("curl error");
		return false;
	endif;
	curl_setopt($handle, CURLOPT_HEADER, false);
	curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
	curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") ); // request as if Firefox
	curl_setopt($handle, CURLOPT_NOBODY, true);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
	$connectable = curl_exec($handle);
	curl_close($handle);
	return $connectable;
}

function unidecode($s) {
	$s = preg_replace_callback("#(u0[a-f0-9]{3})#", create_function('$tmp', 'return utf8_encode(chr( ( hexdec($tmp[1][1] . $tmp[1][2]) * 256) + hexdec($tmp[1][3] . $tmp[1][4])));'), $s);

	return $s;
}

function hex_dump($data, $newline="\n") {
  static $from = '';
  static $to = '';

  static $width = 32; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
	for ($i=0; $i<=0xFF; $i++)
	{
	  $from .= chr($i);
	  $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
	}
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

  $offset = 0;
	print '<xmp>';
  foreach ($hex as $i => $line)
  {
	echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
	$offset += $width;
  }
	print '</xmp>';
}

function is_fibonacci_element($fib, $count, $limit = 100) {

	foreach($fib as $elt)
		if ( $count == $elt )
			return true;

	$idx = count($fib) - 1;
	while ($count > $fib[$idx] && $idx < 50) {
		$fib[] = $fib[$idx] + $fib[$idx - 1];
		$idx = count($fib) - 1;
		if ( $count == $fib[$idx] )
			return true;
	}

	return false;

}

function to_string($s) {
	return (string)$s;
}

class SimpleObject {};


/**
 * Determines the difference between two timestamps.
 *
 * The difference is returned in a human readable format such as "1 hour",
 * "5 mins", "2 days".
 *
 * @since 1.5.0
 *
 * @param int $from Unix timestamp from which the difference begins.
 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
 * @param int $limit Optional. The number of unit types to display (i.e. the accuracy). Defaults to 1.
 * @return string Human readable time difference.
 */
function MahiMahi_human_time_diff( $from, $to = '', $limit = 1 ) {
	// Since all months/years aren't the same, these values are what Google's calculator says
	$units = apply_filters( 'time_units', array(
			31556926 => array( '%s an',  '%s ans' ),
			2629744  => array( '%s mois', '%s mois' ),
			604800   => array( '%s semaine',  '%s semaines' ),
			86400    => array( '%s jour',   '%s jours' ),
			3600     => array( '%s heure',  '%s heures' ),
			60       => array( '%s minute',   '%s minutes' ),
	) );

	if ( empty($to) )
		$to = time();

	$from = (int) $from;
	$to   = (int) $to;
	$diff = (int) abs( $to - $from );

	$items = 0;
	$output = array();

	foreach ( $units as $unitsec => $unitnames ) {
			if ( $items >= $limit )
					break;

			if ( $diff < $unitsec )
					continue;

			$numthisunits = floor( $diff / $unitsec );
			$diff = $diff - ( $numthisunits * $unitsec );
			$items++;

			if ( $numthisunits > 0 )
					$output[] = sprintf( _n( $unitnames[0], $unitnames[1], $numthisunits ), $numthisunits );
	}

	// translators: The seperator for human_time_diff() which seperates the years, months, etc.
	$seperator = ', ';

	if ( !empty($output) ) {
			return implode( $seperator, $output );
	} else {
			$smallest = array_pop( $units );
			return sprintf( $smallest[0], 1 );
	}
}

function distance($lat1, $lon1, $lat2, $lon2, $unit = 'K') {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
	return ($miles * 1.609344);
  } else if ($unit == "N") {
	  return ($miles * 0.8684);
	} else {
		return $miles;
	  }
}

if (!function_exists('cal_days_in_month')) {
	function cal_days_in_month($calendar, $month, $year) {
		return date('t', mktime(0, 0, 0, $month, 1, $year));
	}
}
if (!defined('CAL_GREGORIAN'))
	define('CAL_GREGORIAN', 1);


function convert_smart_quotes($text) {
	$text = str_replace(
		array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
		array("'", "'", '"', '"', '-', '--', '...'),
		$text);
	// Next, replace their Windows-1252 equivalents.
	$text = str_replace(
		array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
		array("'", "'", '"', '"', '-', '--', '...'),
		$text);
	return $text;
}

function request_scheme() {
	return 'http'.($_SERVER['HTTPS']?'s':'');
}

