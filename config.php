<?php
$hostname = "127.0.0.1";
$database = "varvar";
$username = "root";
$password = "";

$prefix="vv_";
define ('ABC',$_SERVER['REQUEST_URI']);

mysql_connect($hostname,$username,$password);
mysql_select_db($database);
mysql_query ("set character_set_client='utf8'"); 
mysql_query ("set character_set_results='utf8'"); 
mysql_query ("set collation_connection='utf8_general_ci'");
    
?>
