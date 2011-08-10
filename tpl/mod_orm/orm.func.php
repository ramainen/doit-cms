<?php
/*

На будущее - итератор объектов и объект-массив
	class ResultIterator extends ArrayIterator {
	private $count=3;
		function key()
		{
			return  "!!";
		}
		function append ($str)
		{
			print "#".$str;
		}
		function offsetGet ($str)
		{
			print "#".$str;
		}
		
		function count()
		{
			return 3;
		}
		function next()
		{
		print "2";
		return "2";
		}
		function valid()
		{
			print "1";
			$this->count--;
			if($this->count > 0){
				return true;
			}
		}
	}
	class ResultElement extends ArrayObject {
		private $linktoobject;
		function __construct(&$object)
		{
			$this->linktoobject = &$object;
		}
		public function getIterator()
		{
			return new ResultIterator(    );
		}
		function offsetGet($index) {		
			return $this->linktoobject->shift_to($index);
		}
		function count()
		{
			return $this->linktoobject->size();
		}
	}
 
*/
	
//Функция определения множественной формы написания слова на основе написания единственной.
	
/*

find_by_name_or_author
find_by_name_and_author
search_by_author
search_by_title

search_in_text_or_title

new
create(attributes)
find(id_or_array)
destroy(id_or_array)
destroy_all
delete(id_or_array)
delete_all
update(ids, updates)
update_all(updates)
exists?
where (:conditions)
having (:conditions)
select // select author,comments - соединения разных форм и моделей
group
order
limit
offset
joins
includes (:include)
lock
readonly
from
first
last
all
preload
eager_load
average
minimum
maximum
sum
calculate

*/	
	
	
//Класс Active Record, обеспечивающий простую добычу данных
class ar
{
	public $edit_button;
	public $options;
	public $_data;
	private $_shift = 0;
	private $_known_columns=array();
	private $_future_data=array();
	
	//Выполняет limit 1 SQL запрос
	function first()
	{
		return $this;
	}
	
	//<< Получение дочерних элементов (comments)
	
	static function plural_to_one ($string)
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
			'swine' => 'swine',
			'news' => 'news'
		);
		$_arr_p=array(
			'/(^.*)xes$/'=>'$1x',
			'/(^.*)ches$/'=>'$1ch',
			'/(^.*)sses$/'=>'$1ss',
			'/(^.*)quies$/'=>'$1quy',
			'/(^.*)ies$/'=>'$1y',
			'/(^.*)lves$/'=>'$1lf',
			'/(^.*)rves$/'=>'$1rf',
			'/(^.*)ves$/'=>'$1fe',
			'/(^.*)men$/'=>'$1man',
			'/(^.+)people$/'=>'$1person',
			'/(^.*)ses$/'=>'$1sis',
			'/(^.*)ta$/'=>'$1tum',
			'/(^.*)ia$/'=>'$1ium',
			'/(^.*)children$/'=>'$1child',
			'/(^.*)s$/'=>'$1'
		);
		
		//Слова - исключения
		if(isset($_p_to_o[$string])) {
			return $_p_to_o[$string];
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
	
	static function one_to_plural ($string)
	{
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
			'swine' => 'swine',
			'news' => 'news'
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
			'/(^.*)man$/'=>'$1men',
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
		
		//Опции по умолчанию и переменные
		$this->options=$options;
		
		if(isset($this->options['data'])) {
		 
			$this->options['queryready']=true;
			$this->_data=$this->options['data'];
		} else {
			$this->options['queryready']=false;
			$this->_data=array();	
		}
		
	//	$this->options['queryready']=false;  //Сбрасывается при смене параметров запроса, при true запросы не выполняются
		
		$this->options['onerow']=true;
		
		//поле, по которому получаем данные. Для текстовых страниц это URL, для товаров это id, для пользователей это username или login и так далее.
		//в подавляющем случае это автоинкрементное числовое поле id
		if(!isset($this->options['idfield'])) {
			$this->options['idfield']='id';
		}
		
		if(!isset($this->options['condition'])) {
			$this->options['condition']='';
		}
		
		if(!isset($this->options['new'])) {
			$this->options['new']=false;
		}
		
		if(!isset($this->options['table'])) {
			if(doit()->table!='') {
				$this->options['table']=doit()->table;
			} else {
				$this->options['table']='datas';
			}
		}
		
		if(!isset($this->options['plural_to_one'])) {
			$this->options['plural_to_one']=self::plural_to_one($this->options['table']);
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
		if(isset($_SESSION['admin'])) {
			$this->edit_button = '<a href="/admin/edit/'.$this->options['table'].'/'.$this->options['id'].'" target="_blank" ><img style="border:none;" src="/cms/internal/gfx/edit.png"></a>';
		}
		
		
		
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
		
		//Говнокодик
		if(count($this->_data)>0) {
			$this->edit_button = '<a href="/admin/edit/'.$this->options['table'].'/'.$this->_data[0]['id'].'" target="_blank" ><img style="border:none;" src="/cms/internal/gfx/edit.png"></a>';
		}
	}
	public function save()
	{
		print "!!!";
		return $this;
	}
	public function is_empty()
	{
		if ($this->options['queryready']==false) {
			$this->fetch_data_now();
		}
		if(isset($this->_data[0])) {
			return false;
		}
		return true;
	}
	//Возвращает размер массива
	public function size()
	{
		if ($this->options['queryready']==false) {
			$this->fetch_data_now();
		}
		return count($this->_data);
	}
	
	public function expand()
	{
		if ($this->options['queryready']==false) {
			$this->fetch_data_now();
		}
		if(isset($this->_data[0])) {
			foreach( $this->_data[0] as $_key=>$_value) {
				d()->{$_key} = $_value;
			}
			return true;
		}
		return false;
	}

	
	public function shift_to($_shift)
	{
		$this->_shift=$_shift;
		return $this;
	}
	
	public function expand_all_to($varname)
	{
		d()->{$varname} = $this->all;
		return $this;
	}
	
	public function expand_to($varname)
	{
		d()->{$varname} = $this->one;
		return $this;
	}
	
	function __set($name,$value)
	{
		$_future_data=array();
	}
	
	function __get($name)
	{
	
		//Item.new
		if($name=='new') {
			$this->options['new']=true;
			$_future_data=array();
			return $this;
		}
		
		//Item.expand
		if($name=='expand') {
			return $this->expand();
		}
		
		
		//Item.size
		if($name=='size') {
			return $this->size();
		}
		
		
		//Item.is_empty
		if($name=='is_empty') {
			return $this->is_empty();
		}
		
		//Item.expand_to_page
		if(substr($name,0,10)=='expand_to_') {
			return $this->expand_to(substr($name,10));
		}
		
		//Item.expand_all_to_pages
		if(substr($name,0,14)=='expand_all_to_') {
			return $this->expand_all_to(substr($name,14));
		}
		
		
		//Item.save
		if($name=='save') {
			return $this->save();
		}
		
		//Item.all            //Получение массива с элементами
		if($name=='all') {
			if ($this->options['queryready']==false) {
				$this->fetch_data_now();
			}
	
			$_tmparr=array();
			
			foreach($this->_data as $element){
				$_tmparr[] = new ar(array('table'=>$this->options['table'], 'data'=>array( $element ) ));
			}
			  
			return $_tmparr;
			
			//Старый подход
			foreach($this->_data as $_key => $_value) {
				return $this->_data;
			}
			
		}
		//Item.one           //Получение одного элемента
		if($name=='one') {
			if ($this->options['queryready']==false) {
				$this->fetch_data_now();
			}
			return $this;
		}

		
		
		if ($this->options['queryready']==false) {
				$this->fetch_data_now();
		}
		
		if(isset($this->_data[0])) {
			//Item.title         //Получение одного свойства
			if (isset($this->_data[0][$name])) {
				return $this->_data[0][$name];
			}
			
			//Item.user          //Получение связанного объекта
			if (isset($this->_data[0][$name.'_id'])) {
				$_tmp = new ar(array('table'=>ar::one_to_plural($name)));
				return $_tmp->find($this->_data[0][$name.'_id']);
			}
			
			//Item.users
			//1. Поиск альтернативных подходищх столбцов
			$foundedfield = false;
			//ищем поле item_id в таблице users
			$_res=mysql_query('SHOW COLUMNS FROM `'.$name.'`');
			while(($_tmpline = mysql_fetch_array($_res) )&& ($foundedfield == false)){
				if ($_tmpline[0]== $this->options['plural_to_one']."_id") {
					$foundedfield = true;
				}
			}
			if($foundedfield==true) {
				$_tmpael  = new ar(array('table'=>$name));
				
				return $_tmpael->where($this->options['plural_to_one']."_id = ?",$this->_data[0]['id'])->all;
					
			}
			return '';
		} else {
			//Item.ramambaharum_mambu_rum
			return '';
		}
		
		
		
		 
		
	}
}

function activerecordwrapper($_modelname)
{
	if(is_array($_modelname)) {
		$_modelname=$_modelname[0];
	}
	return new ar(array('table'=>ar::one_to_plural(strtolower($_modelname))));
}



/*==================================================================================*/
/*
Объектно-ориентированный helper input
*/
class ar_input
{


}