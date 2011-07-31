<?php

//Класс Active Record, обеспечивающий простую добычу данных
class ar
{
	public $options;
	private $_data;
	static $_p_to_o=array(
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
	static $_o_to_p=array(
		'man' => 'men',
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

	//Преобразование множественного числа в единственное
	/*
		Существительные, оканчивающиеся на "-f/-fe", во множественном числе пишутся с "-ves".
		Если слово в единственном числе оканчивается на "-о", то к нему во множественном числе прибавляется суффикс "-es".
		Если слово оканчивается на "-y" с предшествующим согласным, то во множественном числе к нему прибавляется суффикс "-es", а буква "y" переходит в "i" 
		
		
		lea f - lea ves 
		лист - листья 

		lif e - li ves 
		
		
		жизнь - жизни 

		tomato - tomatoes 
		помидор - помидоры 

		Negro - Negroes 
		негр - негры 

		army - armies 
		армия - армии 

		family - families 
		семья - семьи.
		
	*/
	
	//Функция определение единсвенного числа на основе написания множественного.
	function plural_to_one ($string)
	{
		//Слова - исключения
		if(isset($_p_to_o[$string])) {
			return $_p_to_o[$string];
		}
	}
	
	function __construct($options=array())
	{
		$this->_data=array();
		//Опции по умолчанию и переданные
		$this->options=$options;
		$this->options['queryready']=false;  //Сбрасывается при смене параметров запроса, при true запросы не выполняются
		//поле, по которому получаем данные. Для текттовых страниц это URL, для товаров это id, для пользователей это username или login и так далее.
		//в подавляющем случае это автоинкрементное числовое поле id
		$this->options['onerow']=true;
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
		if($_line = mysql_fetch_array(mysql_query("select * from `".$this->options['table']."` where `".$this->options['idfield']."`='". mysql_real_escape_string ($id)."' limit 1"))) {
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
		if($name=='all') {
			if ($this->options['queryready']==false) {
				$this->fetch_data_now();
			}
			return $this->_data;
		}
		if($name=='one') {
		 
			if ($this->options['queryready']==false) {
				$this->fetch_data_now();
			}
			if(isset($this->_data[0])) {
				
				return $this->_data[0];
			}
			return array();
		}
		
	}
}

function activerecordwrapper($_modelname)
{
	return new ar(array('table'=>strtolower($_modelname)));
}

