<?php
	

	include_once ('config.php');
	$start_time = microtime(true);
	include_once ('cms/cms.php');
	
	//$status = explode('  ', mysql_stat());
	//$status = (explode(' ', $status[2]));
	//$na4xtat =  $status[1];
	
	header('Content-type: text/html; Charset=UTF-8');

	print d()->main(); 

	
	$exec_time = microtime(true) - $start_time;
	//$status = explode('  ', mysql_stat());
	//$status = (explode(' ', $status[2]));
	//$na4xtat = ( 1 * $status[1])-$na4xtat;
	
	printf("<!-- %f seconds, %d bytes -->",$exec_time, memory_get_usage(true));
