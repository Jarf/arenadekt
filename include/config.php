<?php
// Config
define('DB_HOST', 'localhost');
define('DB_NAME', 'arenadekt');
define('DB_USER', 'arenadekt');
define('DB_PASS', 'x4wPW}![X<t5LErMKsR9#2');
define('DB_TYPE', 'mysql');

// File Paths
define('DIR_ROOT', dirname(__DIR__) . '/');
define('DIR_VENDOR', DIR_ROOT . 'vendor/');
define('DIR_CSS', DIR_ROOT . 'css/');
define('DIR_INCLUDE', DIR_ROOT . 'include/');
define('DIR_CLASSES', DIR_ROOT . 'classes/');
define('DIR_CLASSES_ABSTRACT', DIR_CLASSES . 'abstract/');

// Site Paths
define('SITE_CSS', '/css/');
define('SITE_JS', '/js/');

// Site Vars
$servername = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'arenadekt.jarfjam.co.uk';
$https = false;
if(isset($_SERVER['HTTPS'])){
	$https = true;
}elseif(isset($_SERVER['SERVER_PROTOCOL']) && stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0){
	$https = true;
}
$dev = 0;
define('SITE_ROOT', ($https ? "https" : 'http') . "://{$servername}/");
if(isset($_SERVER['HTTP_HOST'])){
	switch ($_SERVER['HTTP_HOST']) {
		case 'arenadekt.local':
			$dev = 1;
			break;
	}
}
if(php_sapi_name() === 'cli'){
	$dev = gethostname() === 'localhost' ? 0 : 1;
}
define('ISDEV', $dev);

define('HASH', trim(substr(md5(file_get_contents(sprintf(DIR_ROOT . '.git/refs/heads/%s', 'master'))), 0, 8)));
?>