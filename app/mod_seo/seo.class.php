<?php


class Seo extends UniversalSingletoneHelper
{
	private $cache = array();
	private $is_initiated = false;
	private $seo_object = false;
	private $seo_object_allsite = false;
	public $title;
	public $description;
	public $keywords;
	/*
	* Обработка HTML-кода, закрытие внешних ссылок,
	*/
	function process($html)
	{
		return $html;
	}

	function init()
	{
		$request_uri = (urldecode($_SERVER['REQUEST_URI']));
		$this->title = '';
		$this->keywords = '';
		$this->description = '';
		$this->text = '';
		$this->seo_object = d()->Seoparam->where('page_url = ?',$request_uri);
		$this->seo_object_allsite = d()->Seoparam->where('page_url = ?','*');
		var_dump($this->seo_object->to_sql);
		if(defined('MULTISITE') &&  MULTISITE==true){
			$this->seo_object->order_by('multi_domain DESC');
			$this->seo_object_allsite->order_by('multi_domain DESC');
		}
		
		if($this->seo_object->count == 0){
			$this->seo_object = $this->seo_object_allsite;
		}
		
		if($this->seo_object->title){
			$this->title = $this->seo_object->title;
		} else {
			$this->title = $this->seo_object_allsite->title;
		}
		
		if($this->seo_object->keywords){
			$this->keywords = $this->seo_object->keywords;
		} else {
			$this->keywords = $this->seo_object_allsite->keywords;
		}
		
		if($this->seo_object->description){
			$this->description = $this->seo_object->description;
		} else {
			$this->description = $this->seo_object_allsite->description;
		}

		//d()->
	}
	
}