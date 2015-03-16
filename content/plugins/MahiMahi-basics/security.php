<?php

if ( ! defined('ALLOW_FILE_MODS') && ! defined('DISALLOW_FILE_MODS') )
	define('DISALLOW_FILE_MODS',true);

/*

http://codex.wordpress.org/Administration_Over_SSL

  define('FORCE_SSL_ADMIN', true);


// if apache is something like nginx-cached :
define('FORCE_SSL_ADMIN', true);
define('FORCE_SSL_LOGIN', true);
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
       $_SERVER['HTTPS']='on';

*/