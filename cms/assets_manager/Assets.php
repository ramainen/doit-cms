<?php


class Assets
{
	public $scss_compiler=false;
	public $less_compiler=false;
	public $minifier=false;
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
	function compile_scss($data, $path='')
	{
		if($this->scss_compiler === false){
			$this->init_scss();
		}
		if($path!=''){
			$this->scss_compiler->setImportPaths($path.'/');
		}
		return $this->scss_compiler->compile($data);
		//TODO: $scss->setImportPaths("...path.../stylesheets/");
		 
	}
	function compile_less($data)
	{
		if($this->less_compiler === false){
			$this->init_less();
		}
		return $this->less_compiler->compile($data);
		 
	}	
	function init_scss(){
		$this->scss_compiler = new \Leafo\ScssPhp\Compiler();
	}
	function init_less(){
		$this->less_compiler = new lessc();
	}
	function minify($data){
		$this->minifier =new \MatthiasMullie\Minify\CSS($sourcePath);
		$this->minifier->add($data);
		return $this->minifier->minify();
	}
}
