<?php
function main()
{
	////
	//print ' колво '.count(d()->Client  );
	//print "\n";
	//$arr= d()->Client  ;
	//print d()->Client->id;

	//print "\nи айдишка ";
	//print $arr[3]->id;
	//print "\nи массив ";
//	foreach (d()->Client->limit(3000)  as $key => $client) {
//		print  $client->client;
//	}


	/*
	 * 	foreach (d()->Client->limit(3000)->all  as $key => $client) {
	 //		print $client->id." ".$key;
	 $n = $client->id;
	 	}
	76 секунды было
	 *
	 *
	 * */
	d()->content = d()->content();
	print d()->render('main_tpl');
}
