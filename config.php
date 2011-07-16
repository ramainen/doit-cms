<?php
$hostname = "localhost";
$database = "varvar";
$username = "root";
$password = "root";
$prefix="vv_";

    mysql_connect($hostname,$username,$password);
    mysql_select_db($database);
 mysql_query ("set character_set_client='utf8'"); 
mysql_query ("set character_set_results='utf8'"); 
mysql_query ("set collation_connection='utf8_general_ci'");
    
?>
