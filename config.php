<?php

if($_SERVER["SERVER_ADDR"]=='127.0.0.1' || $_SERVER["SERVER_ADDR"]=='192.168.0.1') {
  define('DB_HOST','127.0.0.1');
	define('DB_NAME','varvar');
	define('DB_USER','root');
	define('DB_PASSWORD','');
} else {
	define('DB_HOST','localhost');
	define('DB_NAME','varvar');
	define('DB_USER','root');
	define('DB_PASSWORD','');
}

/*
//Образец код настройки
$_ENV["DOIT_OPTIMIZE_IMAGES"] = true;
$_ENV["DOIT_OPTIMIZE_IMAGES_EXTEND"]=array(
	'PNG'=>'pngquant --quality=60-90 - < #SOURCE# > #DEST#',  //https://pngquant.org/
	'JPG'=>'E:\OpenServer\modules\php\PHP-5.6\ext\cjpeg -quality 70 #SOURCE# > #DEST#',   // https://mozjpeg.codelove.de/binaries.html
);
*/
//define('MULTISITE',true);

/*
define('DB_TYPE','pgsql');
define('DB_HOST','localhost');
define('DB_NAME','varvar');
define('DB_USER','postgres');
define('DB_PASSWORD','12345');
*/
