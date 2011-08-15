<?php
$start_time = microtime();
$start_array = explode(" ",$start_time);
$start_time = $start_array[1] + $start_array[0];
$countq=0; 
include_once ('config.php');
include_once ('cms/cms.php');
header('Content-type: text/html; Charset=UTF-8');

print doit()->main();

$end_time = microtime();
$end_array = explode(" ",$end_time);
$end_time = $end_array[1] + $end_array[0];
$time = $end_time - $start_time;
  printf("<br>Страница сгенерирована за %f секунд, память: %f и запросов было %f",$time, memory_get_usage(),$countq);
?>
