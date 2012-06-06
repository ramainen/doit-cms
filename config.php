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

define('MULTISITE',true);

/*
define('DB_TYPE','pgsql');
define('DB_HOST','localhost');
define('DB_NAME','varvar');
define('DB_USER','postgres');
define('DB_PASSWORD','12345');
*/

