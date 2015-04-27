<?php


class Route
{
	public  $method = false;
	public $url='/news/:id+';
	public $closure=false;
	
	public function via($via=false){
		$this->method=$via;
	}
	
	public function map($url,$closure)
	{
		$this->url=$url;
		$this->closure=$closure;
	}
	public function check($url='/catalog', $method=false, $level="content"){
		$regex = $this->url;
		$regex = preg_replace(
			array('#\:[a-z_][a-zA-Z0-9_]*\+#','#\:[a-z_][a-zA-Z0-9_]*\*#','#\:[a-z_][a-zA-Z0-9_]*#')
			,array('(.+?)','(.*?)','([^\/]+?)')
		,$regex);
		if(!preg_match('#^'.$regex.'$#',$url)){
			return false;
		}
		if($method !== false && $this->method !== false){
			if(!in_array($method,$this->method)){
				return false;
			}
		}
		
		return true;
	}
	public function dispatch($url){
		$matches = array();
		$regex = $this->url;
		$regex = preg_replace(
			array('#\:[a-z_][a-zA-Z0-9_]*\+#','#\:[a-z_][a-zA-Z0-9_]*\*#','#\:[a-z_][a-zA-Z0-9_]*#'),array('(.+?)','(.*?)','([^\/]+?)')
		,$regex);
		preg_match('#^'.$regex.'$#',$url,$matches);
		unset($matches[0]);
		return call_user_func_array($this->closure,$matches);
	}
	
}
