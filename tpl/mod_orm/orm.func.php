<?php

	
	
	
//Функция определения множественной формы написания слова на основе написания единственной.
	
	
	
	
//Класс Active Record, обеспечивающий простую добычу данных
class ar
{
	public $options;
	private $_data;
	private $_known_columns=array();
	
	
	//Выполняет limit 1 SQL запрос
	function first()
	{
		return $this;
	}
	
	//<< Получение дочерних элементов (comments)
	
	
	static function one_to_plural ($string)
	{
		$_p_to_o=array(
			'men' => 'man',
			'women' =>	'woman',
			'mice' =>'mouse',
			'teeth' =>	'tooth',
			'feet' => 'foot',
			'children' =>'child',
			'oxen' => 'ox',
			'geese' =>	'goose',
			'sheep' =>	'sheep',
			'deer' => 'deer',
			'swine' => 'swine'
		);
		$_o_to_p=array(
			'man' => 'men',
			'pike' => 'pike',
			'woman' => 'women',
			'mouse' => 'mice',
			'tooth' => 'teeth',
			'foot' => 'feet',
			'child' => 'children',
			'ox' => 'oxen',
			'goose' => 'geese',
			'sheep' => 'sheep',
			'deer' => 'deer',
			'swine' => 'swine'
		);
		$_arr_p=array(
			'/(^.*)x$/'=>'$1xes',
			'/(^.*)ch$/'=>'$1ches',
			'/(^.*)ss$/'=>'$1sses',
			'/(^.*)quy$/'=>'$1quies',
			'/(^.*[bcdfghklmnpqrstvxz])y$/'=>'$1ies',
			'/(^.*)fe$/'=>'$1ves',
			'/(^.*)lf$/'=>'$1lves',
			'/(^.*)rf$/'=>'$1rves',
			'/(^.+)person$/'=>'$1people',
			'/(^.*)man$/'=>'$1man',
			'/(^.*)sis$/'=>'$1ses',
			'/(^.*)tum$/'=>'$1ta',
			'/(^.*)ium$/'=>'$1ia',
			'/(^.*)child$/'=>'$1children',
			'/(^.*)$/'=>'$1s'
		);
		
		//Слова - исключения
		if(isset($_o_to_p[$string])) {
			return $_o_to_p[$string];
		}

		//TODO: (.*s) -> $1;
		foreach($_arr_p as $key=>$value) {
			$new=preg_replace($key,$value,$string);
			if($new != $string) {
				break;
			}
		}
		return $new;
	}
	
	
	function __construct($options=array())
	{
		$this->_data=array();
		//Опции по умолчанию и переданные
		$this->options=$options;
		$this->options['queryready']=false;  //Сбрасывается при смене параметров запроса, при true запросы не выполняются
		
		$this->options['onerow']=true;
		
		//поле, по которому получаем данные. Для текстовых страниц это URL, для товаров это id, для пользователей это username или login и так далее.
		//в подавляющем случае это автоинкрементное числовое поле id
		if(!isset($this->options['idfield'])) {
			$this->options['idfield']='id';
		}
		
		if(!isset($this->options['condition'])) {
			$this->options['condition']='';
		}
		
		if(!isset($this->options['table'])) {
			if(doit()->table!='') {
				$this->options['table']=doit()->table;
			} else {
				$this->options['table']='data';
			}
		}
	}
	
	//альтернативная функция бстрого получения данных
	public function getRow($id)
	{
		if ($_line = mysql_fetch_array(mysql_query("select * from `".$this->options['table']."` where `".$this->options['idfield']."`='". mysql_real_escape_string ($id)."' limit 1"))) {
			return $_line;
		} else {
			return false;
		}
	}
	
	//Функция find указывает на то, что необходимо искать нечто по полю ID
	public function find($id)
	{
		$this->options['id']=$id;
		$this->options['queryready']=false;
		$id = 1 * $id;
		$this->options['condition'] = ' id = '.$id.' ';
		return $this;
	}
	
	function __call($name,$arguments)
	{
		if(substr($name,0,8)=='find_by_') {
			$by=substr($name,8);
			$this->options['queryready']=false;
			$this->options['condition'] = " `".$by."` = '".mysql_real_escape_string($arguments[0])."' ";
			return $this;
		}
	}
	
	public function where()
	{
		//TODO: переписать на preg_replace с исполльзованием последнего параметра
		$this->options['queryready']=false;
		$args = func_get_args();
		$_condition=$args[0];
		$_conditions=explode('?',' '.$_condition.' ');
		$_condition='';
		for ($i=1; $i<= count($_conditions)-1; $i++) {
			$_condition .= $_conditions[$i-1].   " '".mysql_real_escape_string($args[$i])."' "  ;
		}
		$_condition .= $_conditions[$i-1];
		$this->options['condition'] = $_condition.' ';
		return $this;
	}
	
	
	function fetch_data_now()
	{
		$this->options['queryready']=true;
		$this->_data = array();
		$_query_string='select * from `'.$this->options['table'].'` ';
		if($this->options['condition']!='') {
			$_query_string .= 'where '.$this->options['condition'];
		}
		$_result=mysql_query($_query_string);
		while ($line=mysql_fetch_array($_result,MYSQL_ASSOC)) {
			$this->_data[]=$line;
		}
	}
	
	function __get($name)
	{
		//Item.all            //Получение массива с элементами
		if($name=='all') {
			if ($this->options['queryready']==false) {
				$this->fetch_data_now();
			}
			return $this->_data;
		}
		//Item.one           //Получение одного элемента
		if($name=='one') {
		 
			if ($this->options['queryready']==false) {
				$this->fetch_data_now();
			}
			if(isset($this->_data[0])) {
				return $this->_data[0];
			}
			return array();
		}
		
		if ($this->options['queryready']==false) {
				$this->fetch_data_now();
		}
		
		//Item.title         //Получение одного свойства
		if (isset($this->_data[0][$name])) {
			return $this->_data[0][$name];
		}
		
		//Item.user          //Получение связанного объекта
		if (isset($this->_data[0][$name.'_id'])) {
			$_tmp = new ar(array('table'=>ar::one_to_plural($name)));
			return $_tmp->find($this->_data[0][$name.'_id']);
		}
		
		//Item.ramambaharum_mambu_rum
		return '';
	}
}

function activerecordwrapper($_modelname)
{
	if(is_array($_modelname)) {
		$_modelname=$_modelname[0];
	}
	return new ar(array('table'=>ar::one_to_plural(strtolower($_modelname))));
}

