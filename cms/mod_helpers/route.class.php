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
		if($url != $this->url){
			return false;
		}
		if($method !== false && $this->method !== false){
			if(!in_array($method,$this->method)){
				return false;
			}
		}
		return true;
	}
	
}
