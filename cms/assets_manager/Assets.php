<?php


class Assets
{
	
	function compile_postcss($data)
	{
		if( $curl = curl_init() ) {
			curl_setopt($curl, CURLOPT_URL, 'http://cloud.doit-cms.ru/processcss');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array("data"=>$data)));
			$out = curl_exec($curl);
			curl_close($curl);
			return $out;
		}
		return false;
	}
		
}