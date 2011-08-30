<?php
error_reporting(E_ALL);
$start_time = microtime();
$start_array = explode(" ",$start_time);
$start_time = $start_array[1] + $start_array[0];


include_once ('config.php');

$status = explode('  ', mysql_stat());
$status = (explode(' ', $status[2]));
$na4xtat =  $status[1];


include_once ('cms/cms.php');
header('Content-type: text/html; Charset=UTF-8');

print doit()->main(); 


$end_time = microtime();
$end_array = explode(" ",$end_time);
$end_time = $end_array[1] + $end_array[0];
$time = $end_time - $start_time;
	
	$status = explode('  ', mysql_stat());
	$status = (explode(' ', $status[2]));
	$na4xtat = ( 1 * $status[1])-$na4xtat;
	
	
  printf("<!-- %f seconds, %d bytes, %d queries -->",$time, memory_get_usage(true),$na4xtat);
?>