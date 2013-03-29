<?php
/*

	DoIt! CMS and VarVar framework
	Copyright (C) 2011 Fakhrutdinov Damir (aka Ainu)

	*      This program is free software; you can redistribute it and/or modify
	*      it under the terms of the GNU General Public License as published by
	*      the Free Software Foundation; either version 2 of the License, or
	*      (at your option) any later version.
	*
	*      This program is distributed in the hope that it will be useful,
	*      but WITHOUT ANY WARRANTY; without even the implied warranty of
	*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*      GNU General Public License for more details.
	*
	*      You should have received a copy of the GNU General Public License
	*      along with this program; if not, write to the Free Software
	*      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
	*      MA 02110-1301, USA.
 
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
define('SQL_NULL','CONST'.md5(time()).'MYSQL_NULL_CONST'.rand());
	
//Класс Active Record, обеспечивающий простую добычу данных
abstract class ActiveRecord implements ArrayAccess, Iterator, Countable //extends ArrayIterator
{
	public static $_columns_cache=array();
	public $_options;
	public $_data;
	private $_get_by_id_cache = false;
	public $insert_id=false;
	private $_used_tree_branches;
	private $_shift = 0;
	private $_known_columns=array();
	private $_count_rows = 0;
	private $_future_data=array();
	private $_cursor=0;
	public $current_page=0;
	public $per_page=10;
	private $_is_sliced=false;
	private $_revinded=0;
	private $_count=0;
	private $_must_revind=false;
	private $_slice_size=5;
	private $_objects_cache=array();
	private $_safe_mode = false;
	//TODO: Выполняет limit 1 SQL запрос
	//DEPRECATED: Это не Rails, тут всё гораздо проще.
	function first()
	{
		return $this;
	}
	

	static function plural_to_one ($string)
	{
		$_p_to_o=array(
			'criteria' => 'criteria',
			'men' => 'man',
			'women' =>	'woman',
			'mice' =>'mouse',
			'konkurses' =>'konkurs',
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
			'/(^.+)statuses$/'=>'$1status',
			'/(^.+)konkurses$/'=>'$1konkurs',
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
			'criteria' => 'criteria',
			'man' => 'men',
			'pike' => 'pike',
			'woman' => 'women',
			'mouse' => 'mice',
			'tooth' => 'teeth',
			'konkurs' =>'konkurses',
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
			'/(^.+)status$/'=>'$1statuses',
			'/(^.+)konkurs$/'=>'$1konkurses',
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
	
	public function __toString()
	{
		return $this->show();
	}
	
	function init()
	{
		
	}
	function __construct($_options=array())
	{

		//Опции по умолчанию и переменные


		if(is_array($_options)){
			$this->_options=$_options;
		} else {
			$this->_options=array();
		}


		
	//	$this->_options['queryready']=false;  //Сбрасывается при смене параметров запроса, при true запросы не выполняются
		
		$this->_options['onerow']=true;
		
		//поле, по которому получаем данные. Для текстовых страниц это URL, для товаров это id, для пользователей это username или login и так далее.
		//в подавляющем случае это автоинкрементное числовое поле id
		if(!isset($this->_options['idfield'])) {
			$this->_options['idfield']='id';
		}
		
		if(!isset($this->_options['namefield'])) {
			$this->_options['namefield']='url';
		}
		
		if(!isset($this->_options['condition'])) {
			$this->_options['condition']=array();
		}

		if(!isset($this->_options['select'])) {
			$this->_options['select']=' * ';
		}
		
		if(!isset($this->_options['limit'])) {
			$this->_options['limit']='';
		}

		if(!isset($this->_options['order_by'])) {
			$this->_options['order_by']=' ORDER BY '.DB_FIELD_DEL . 'sort'.DB_FIELD_DEL . ' ';
		}
		if(!isset($this->_options['group_by'])) {
			$this->_options['group_by']=' ';
		}
		
 
		
		if(!isset($this->_options['new'])) {
			$this->_options['new']=false;
		}
		
		if(!isset($this->_options['tree'])) {
			$this->_options['tree']=false;
		}
		
		if(!isset($this->_options['calc_rows'])) {
			$this->_options['calc_rows']=false;
		}
		
		//TODO: брать таблицу из родительского объекта
		if(!isset($this->_options['table'])) {			
			$_current_class = get_class($this);
			if(substr($_current_class,-5)=='_safe'){
				$_current_class = substr($_current_class,0,-5);
				$this->_safe_mode = true;
			}
			$this->_options['table']=self::one_to_plural(strtolower($_current_class));
		}else{
			if(substr($this->_options['table'],-5)=='_safe'){
				$this->_options['table'] = substr($this->_options['table'],0,-5);
			}
		}
		
		
		if(isset($this->_options['data'])) {
			$this->_options['queryready']=true;
			$this->_data=$this->_options['data'];
			$this->_count=count($this->_options['data']);
			if($this->_count>0){
				if (!isset (doitClass::$instance->datapool['columns_registry'])) {
					doitClass::$instance->datapool['columns_registry'] = array();
					doitClass::$instance->datapool['_known_fields'] = array();
				}

				//if (!isset (doitClass::$instance->datapool['columns_registry'][$this->_options['table']])) {
					doitClass::$instance->datapool['_known_fields'][$this->_options['table']]	=array_keys($this->_data[0]);
					doitClass::$instance->datapool['columns_registry'][$this->_options['table']] =array_keys($this->_data[0]);
				//}
			}
			$this->_data[0]['_only_count'] = count($this->_options['data']);
		} else {
			$this->_options['queryready']=false;
			$this->_data=array();	
		}		
		
		
		if(!isset($this->_options['plural_to_one'])) {
			$this->_options['plural_to_one']=self::plural_to_one($this->_options['table']);
		}

		if(count($_options)==1 && is_numeric($_options[0])){
			$this->find($_options[0]);
		}
		
		$this->init();
	}


	/**
	 * Функция возвращает сссылку на сам объект, для запросов вроде doitClass::$instance->User->me[3]
	 */
	public function me()
	{
		return $this;
	}

	//альтернативная функция бстрого получения данных
	public function getRow($id)
	{
		return $this->find($id)->me[0];
	}
	
	public function init_seo()
	{
		d()->Seo->from_object($this);
	}
	//Функция find указывает на то, что необходимо искать нечто по полю ID
	public function find($id)
	{
		if (is_numeric($id)) {
			$this->_options['id']=(int)$id;
			$id = 1 * $id;
			$this->find_by('id',(int)$id);
		} else {
			$this->find_by($this->_options['namefield'], $id);
		}
		$this->limit(1);
		return $this;
	}
	
	public function find_by($by,$what)
	{
		$this->_options['queryready']=false;
		if(defined('MULTISITE') &&  MULTISITE==true && ($this->_options['namefield'] == $by || $by=='id')){
			$this->order_by('multi_domain DESC');
		}elseif ($this->_options['namefield'] == $by || $by=='id'){
			$this->order_by('');
		}
		$this->_options['condition'] = array("( ".DB_FIELD_DEL .$by. DB_FIELD_DEL . " = ".doitClass::$instance->db->quote($what)." )");
		return $this;
	}
	
	function __call($name,$arguments)
	{
		if(substr($name,0,8)=='find_by_') {
			$by=substr($name,8);
			$what=$arguments[0];
			$this->find_by($by,$what);
		}
		if($name == 'new'){
			$this->_options['new']=true;
			$this->_future_data = array();
			return $this;
		}
		return $this;
	}
	
	public function sql($query)
	{
		$this->_options['queryready']=true;
		$this->_data=doitClass::$instance->db->query($query)->fetchAll();
		$this->_count = count($this->_data);
		return $this;
	}
	
	public function slice($pieces=2)
	{
		$this->_is_sliced=true;
		$this->_slice_size=$pieces+2;	
		return $this;
	}
	
	public function where()
	{
		//TODO: переписать на preg_replace с исполльзованием последнего параметра
		$this->_options['queryready']=false;
		$args = func_get_args();
		$_condition=$args[0];
		$_conditions=explode('?',' '.$_condition.' ');
		$_condition='';
		for ($i=1; $i<= count($_conditions)-1; $i++) {
			$param=$args[$i];
			if(is_array($param)){
				if(count($param)==0){
					$param = ' false ';
				}else{
					if(is_object($param)){
						$newparam=array();
						foreach ($param as $key=>$value){
							$newparam[$key] = doitClass::$instance->db->quote($param[$key]->id);
						}
						$param=implode(", ",$newparam);
						
					}else{
						foreach ($param as $key=>$value){
							$param[$key] = doitClass::$instance->db->quote($param[$key]);
						}
						$param=implode(", ",$param);
					}
				}
			}else{
				$param = doitClass::$instance->db->quote($param);
			}
			$_condition .= $_conditions[$i-1]. " ".$param." "  ;
		}
		$_condition .= $_conditions[$i-1];
		$this->_options['condition'][] = '('.$_condition.')';
		return $this;
	}
	
	
	public function paginate($per_page=10,$current=false)
	{
		if($current===false){
			//Если в контроллере забыли передать это подразумевающееся понятие, поможем контроллеру
			if(isset($_GET['page'])){
				$current=(int)$_GET['page'];
			}else{
				$current=0;
			}
		}
		$this->calc_rows();
		$this->limit($current*$per_page,$per_page);
		$this->current_page=$current;
		$this->per_page=$per_page;
		return $this;
	}
	
	public function paginator($activeclass=false)
	{
		$paginator = d()->Paginator;
		if($activeclass!==false){
			$paginator->setActive($activeclass);
		}
		return $paginator->generate($this);
	}
	
	public function calc_rows()
	{
		$this->_options['calc_rows']=true;
		return $this;
	}

	/**
	 * Указывает LIMIT для будущего SQL запроса, возвращает объект для дальнейшего использования
	 *
	 * @param $limit первый параметр в директиве LIMIT (количество или отступ от начала). Может быть строкой с запятой.
	 * @param bool $count второй параметр (необязательный) в дирекиве LIMIT (количество)
	 * @return ar текущий экземплятр объекта
	 */
	public function limit($limit, $count=false)
	{
		$this->_options['queryready']=false;
		$limitstr=strtolower(trim($limit));
		if($count!==false) {
			$limit = $limit.", ".$count;
		}
		if($limitstr!='') {
			if(substr($limitstr,0,5)=='limit') {
				$this->_options['limit'] = ' '.$limit.' ';
			} else {
				$this->_options['limit'] = ' LIMIT '.$limit.' ';
			}
		} else {
			$this->_options['limit'] = '';
		}
		return $this;
	}	
	
	private function calc_many_to_many_table_name($table1,$table2){
		if($table1 > $table2){
			return $table2.'_to_'.$table1;
		}
		return $table1.'_to_'.$table2;
	}
	
	public function order_by($order_by)
	{
		$this->_options['queryready']=false;
		if(trim($order_by)!='') {
			$this->_options['order_by'] = ' ORDER BY '.$order_by.' ';
		} else {
			$this->_options['order_by'] = '';
		}
		return $this;
	}
	public function group_by($group_by)
	{
		$this->_options['queryready']=false;
		if(trim($group_by)!='') {
			$this->_options['group_by'] = ' GROUP BY '.$group_by.' ';
		} else {
			$this->_options['group_by'] = '';
		}
		return $this;
	}
	public function order($order_by)
	{
		$this->_options['queryready']=false;
		if(trim($order_by)!='') {
			$this->_options['order_by'] = ' ORDER BY '.$order_by.' ';
		} else {
			$this->_options['order_by'] = '';
		}
		return $this;
	}

	public function select($select)
	{
		$this->_options['queryready']=false;
		if(trim($select)!='') {
			$this->_options['select'] = $select;
		} else {
			$this->_options['select'] = ' * ';
		}
		return $this;
	}
	
	//Общее количество строк в таблице
	function all_rows_count()
	{
		$_count_result = doitClass::$instance->db->query("SELECT COUNT(id) as counting FROM ".$this->_options['table'])->fetch();
		return $_count_result ['counting'];
	}
	
	//Количество строк в найденном запросе
	//TODO: что по поводу LIMIT?
	function found_rows()
	{
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		
		if($this->_options['calc_rows']) {
			return $this->_count_rows;
		} else {
			return count($this->_data);
		}	
	}
	
	function search()
	{
		$args = func_get_args();
		if(count($args)==0){
			return $this;
		}
		if(count($args)==1){
			$args[1]='title';
			$args[2]='text';
		}
		
		$this->_options['queryready']=false;

		$param = doitClass::$instance->db->quote('%'.$args[count($args)-1].'%');
		$_pieces=array();
		
		//Вот тут стоит остановиться, и подумать о map-reduce.
		for ($i=0; $i<= count($args)-2; $i++) {
			$_pieces[] = " ".DB_FIELD_DEL.$args[$i].DB_FIELD_DEL." LIKE  ".$param." ";
		}
		
		//Вот тут стоит остановиться, и подумать о map-reduce.
		$_condition=implode(' OR ',$_pieces);
		$this->_options['condition'][] = '('.$_condition.')';
		return $this;
	}
	
	function order_by_userdate($order='DESC')
	{
		$this->order_by('CONCAT(SUBSTR('.DB_FIELD_DEL.'date'.DB_FIELD_DEL.', 7, 4), SUBSTR('.DB_FIELD_DEL.'date'.DB_FIELD_DEL.', 4, 2), SUBSTR('.DB_FIELD_DEL.'date'.DB_FIELD_DEL.', 1, 2) ) '.$order);
		return $this;
	}
	
	function to_sql()
	{
		$_query_string='SELECT ';
		if($this->_options['calc_rows']) {
			$_query_string .= ' SQL_CALC_FOUND_ROWS ';
		}
		$_query_string .= ' ' . $this->_options['select'] . ' FROM '.DB_FIELD_DEL.''.$this->_options['table'].''.DB_FIELD_DEL.' ';
		
		if(defined('MULTISITE') &&  MULTISITE==true){
			$this->_options['condition'][] =   "( multi_domain = '". SERVER_NAME ."' or multi_domain = '' or multi_domain is null )";
		}
		
		if(count($this->_options['condition'])>0) {
			$_condition = implode(' AND ',$this->_options['condition']);
			$_query_string .= 'WHERE '.$_condition;
		}
		
		if($this->_options['group_by']!='') {
			$_query_string .=  ' '.$this->_options['group_by'].' ';
		}
		if($this->_options['order_by']!='') {
			$_query_string .=  $this->_options['order_by'];
		}

		if($this->_options['limit']!='') {
			$_query_string .=  $this->_options['limit'];
		}

		return $_query_string;
	}

	function fetch_data_now()
	{
		$this->_options['queryready'] = true;
		
		/*
		$result = doitClass::$instance->db->query($this->to_sql());
		if(!$result){
			print $this->to_sql();
		}else{
			$this->_data =  $result->fetchAll(PDO::FETCH_ASSOC);
			$this->_count = count($this->_data);
		}
		*/
		
		$db_result = doitClass::$instance->db->query($this->to_sql());
		if( d()->db->errorCode()=='42S02' || d()->db->errorCode()=='42S22'){
			d()->bad_table = $this->_options['table'];
		}
		$this->_data =  $db_result ->fetchAll(PDO::FETCH_ASSOC);
		
		
		
		$this->_count = count($this->_data);
		
		
		if($this->_count>0){
			if (!isset (doitClass::$instance->datapool['columns_registry'])) {
				doitClass::$instance->datapool['columns_registry'] = array();
				doitClass::$instance->datapool['_known_fields'] = array();
			}

			if (!isset (doitClass::$instance->datapool['columns_registry'][$this->_options['table']])) {
				doitClass::$instance->datapool['_known_fields'][$this->_options['table']]	=array_keys($this->_data[0]);
				doitClass::$instance->datapool['columns_registry'][$this->_options['table']] =array_keys($this->_data[0]);
			}
		}
		if ($this->_options['calc_rows']) {
			$_countrows_line = doitClass::$instance->db->query('SELECT FOUND_ROWS()')->fetch();
			$this->_count_rows = $_countrows_line[0];
		}
	}
	//CRUD
	public function delete()
	{
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
			
		if(isset($this->_data[0])){
			$_query_string='delete from '.DB_FIELD_DEL.''.$this->_options['table'] . DB_FIELD_DEL." where ".DB_FIELD_DEL."id".DB_FIELD_DEL." = '".$this->_data[0]['id']."'";
			doitClass::$instance->db->exec($_query_string);
		}
		return $this;
	}

	function save_connecton_array($id,$table,$rules){
		//Сохранение каждого из списка элементов. Если это не массив, сделать его таким
		foreach($rules as $key=>$data){
			if(!is_array($data)){
				if($data==''){
					$data = array();
				}else{
					$data=explode(',',$data);
				}
			}
			$second_table =substr($key,3);
			
			$first_field = to_o($table).'_id';
			$second_field = to_o($second_table).'_id';

			$many_to_many_table = $this->calc_many_to_many_table_name($table,$second_table);

			
			//0. проверяем наличие таблицы, при её отсуствии, создаём её
			if(false == $this->columns($many_to_many_table)){
				//таблицы many_to_many не существует  - создаем автоматически
				$one_element=to_o($many_to_many_table);
				d()->Scaffold->create_table($many_to_many_table,$one_element);
				
				d()->Scaffold->create_field($many_to_many_table,$second_field);
				d()->Scaffold->create_field($many_to_many_table,$first_field);
			}
			$columns_names=array_flip($this->columns($many_to_many_table));
			if(!isset($columns_names[$first_field])){
				d()->Scaffold->create_field($many_to_many_table,$first_field);
			}
			if(!isset($columns_names[$second_field])){
				d()->Scaffold->create_field($many_to_many_table,$second_field);
			}
			
			//1.удаляем существующие данные из таблицы
			if(count($data)>0){
				$_query_string='delete from '.DB_FIELD_DEL.''.$many_to_many_table . DB_FIELD_DEL." where ".DB_FIELD_DEL. $second_field .DB_FIELD_DEL." NOT IN (". implode(', ',$data) .") AND ".DB_FIELD_DEL. $first_field .DB_FIELD_DEL." =  ". e($id) . "";
			}else{
				$_query_string='delete from '.DB_FIELD_DEL.''.$many_to_many_table . DB_FIELD_DEL." where ".DB_FIELD_DEL. $first_field .DB_FIELD_DEL." =  ". e($id) . "";
			}
			doitClass::$instance->db->exec($_query_string);
			//2.добавляем нове записи в таблицу
			$exist = doitClass::$instance->db->query("SELECT ".DB_FIELD_DEL.''.$second_field . DB_FIELD_DEL." as cln FROM ".DB_FIELD_DEL.''.$many_to_many_table . DB_FIELD_DEL."  where ".DB_FIELD_DEL. $first_field .DB_FIELD_DEL." =  ". e($id) . "")->fetchAll(PDO::FETCH_COLUMN);
			$exist = array_flip($exist);

			foreach($data as $second_id){
				if(!isset($exist[$second_id])){
					$_query_string='insert into '.DB_FIELD_DEL. $many_to_many_table .DB_FIELD_DEL." (".DB_FIELD_DEL. $first_field .DB_FIELD_DEL.", ".DB_FIELD_DEL. $second_field .DB_FIELD_DEL." ) values (". e($id) . ",". e( $second_id) . " )";
					doitClass::$instance->db->exec($_query_string);
				}
			}
		}
	}
	
	public function save()  //CrUd - Create & Update
	{
		$to_array_cache=array();
		$tmp_future_data = array();
		$current_id=0;
		foreach($this->_future_data as $key=>$value){
			if(substr($key,0,3)=='to_'){
				$to_array_cache[$key]=$value;
			}else{
				$tmp_future_data[$key]=$value;
			}
		}
		$this->_future_data = $tmp_future_data;
		
		if($this->_options['new']==true) {
			$this->insert_id=false;
			//Тут идёт вставка
			if(count($this->_future_data)>0) {
				$fields=array();
				$values=array();
				foreach($this->_future_data as $key => $value) {
					$fields[]=" ".DB_FIELD_DEL . $key . DB_FIELD_DEL." ";
					
					if(SQL_NULL === $value || (substr($key,-3)=='_id' && $value==='')){
						$values[]=" NULL ";
					}else{
						$values[]=" ". doitClass::$instance->db->quote ($value)." ";
					}
					
				}
				$fields_string=implode (',',$fields);
				$values_string=implode (',',$values);
				$_query_string='insert into '.DB_FIELD_DEL.$this->_options['table'].DB_FIELD_DEL.' ('.$fields_string.') values ('.$values_string.')';
			}else{
				$_query_string='insert into '.DB_FIELD_DEL.$this->_options['table'].DB_FIELD_DEL.' () values ()';
			}
		} else {
			if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
			}
			if(isset($this->_data[0])){
				$current_id = $this->_data[0]['id'];
			}
			//Тут проверка на апдейт
			if(isset($this->_data[0]) && (count($this->_future_data)>0)){
				$attributes=array();
				foreach($this->_future_data as $key => $value) {

					if(SQL_NULL === $value  || (substr($key,-3)=='_id' && $value==='')){
						$attributes[]=" ". DB_FIELD_DEL . $key. DB_FIELD_DEL ." = NULL ";
					}else{
						$attributes[]=" ". DB_FIELD_DEL . $key. DB_FIELD_DEL ." = ". doitClass::$instance->db->quote($value)." ";
					}
					
				}
				$attribute_string=implode (',',$attributes);
				$_query_string='update '.DB_FIELD_DEL.$this->_options['table'].DB_FIELD_DEL.' set '.$attribute_string.", ". DB_FIELD_DEL ."updated_at". DB_FIELD_DEL ." = NOW()  where ". DB_FIELD_DEL ."id". DB_FIELD_DEL ." = '".$this->_data[0]['id']."'";
			}
		}
		doitClass::$instance->db->exec($_query_string);
		
		
		
		$error_code=doitClass::$instance->db->errorInfo();
		$error_code=$error_code[1];

		if (1054 == $error_code) {
			$list_of_existing_columns=$this->columns();
			foreach($this->_future_data as  $value=>$key){
				if(!in_array($value,$list_of_existing_columns)){
					doitClass::$instance->Scaffold->create_field($this->_options['table'],$value);
				}
			}
			foreach(array('sort','created_at','updated_at') as  $value){
				if(!in_array($value,$list_of_existing_columns)){
					doitClass::$instance->Scaffold->create_field($this->_options['table'],$value);
				}
			}
			doitClass::$instance->db->exec($_query_string);
		}
		
		
		if($this->_options['new']==true) {
			$this->insert_id = doitClass::$instance->db->lastInsertId();
			$current_id = $this->insert_id;
			$_query_string='update '.DB_FIELD_DEL.$this->_options['table'].DB_FIELD_DEL.' set '.
			DB_FIELD_DEL ."sort". DB_FIELD_DEL ." = '".$this->insert_id."', ".
			DB_FIELD_DEL ."created_at". DB_FIELD_DEL ." = NOW(), ".
			DB_FIELD_DEL ."updated_at". DB_FIELD_DEL ." = NOW() ".
			"where ". DB_FIELD_DEL ."id". DB_FIELD_DEL ." = '".$this->insert_id."'";
			doitClass::$instance->db->exec($_query_string);
			
 
			$error_code=doitClass::$instance->db->errorInfo();
			$error_code=$error_code[1];
			
			if (1054 == $error_code) {
				$list_of_existing_columns=$this->columns();
				foreach(array('sort','created_at','updated_at') as  $value){
					if(!in_array($value,$list_of_existing_columns)){
						doitClass::$instance->Scaffold->create_field($this->_options['table'],$value);
					}
				}
				
				doitClass::$instance->db->exec($_query_string);
			}
			
		}
		$this->_future_data=array();
		
		//Сохранение связей
		if(count($to_array_cache)!=0){
			$this->save_connecton_array($current_id,$this->_options['table'],$to_array_cache);
		}
		return $this;
	}

	public function create($params=array())  //Crud - Create
	{
		//Более быстрый вариант $this->new
		$this->_options['new']=true;
		$this->_future_data = array();
		foreach($params as $key => $value){
			$this->{$key} = $value;
		}
		$this->save();
		return $this;
	}

	public function one()
	{
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		return $this;
	}
	
	public function all_of($field = 'titles') {
		
	
		$result_array=array();
		$field = to_o($field);
		$array  = $this->all;
		foreach($array  as $value) {
			$result_array[]= $value->{$field};
		}
		return $result_array;
	}
	
	public function all()
	{
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}

		$_tmparr=array();
		$_class_name = get_class($this);
		foreach($this->_data as $element){
			$_tmparr[] = new  $_class_name (array('table'=>$this->_options['table'],'data'=>array( $element ) ));
		}
		  
		return $_tmparr;
	}

	//Итератор
	function count()
	{
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
		return $this->_count;
	}
	//ОН сука медленный
	function only_count()
	{
		if ($this->_options['queryready']==false) {
			$this->select('count(id) as _only_count');
			$this->fetch_data_now();
		}
		return $this->_data[0]['_only_count'];
	}

	function current()
	{
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
		return $this;
	}

	function next()
	{
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		if(!$this->_is_sliced){
			$this->_cursor++;
			return;
		}
		if(!$this->_is_sliced){
			$this->_cursor++;
			return;
		}
		if(!$this->_must_revind){
			$this->_cursor++;
		}
	}

	function valid()
	{
		
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
		if(!$this->_is_sliced){
			return !($this->_cursor >= $this->_count);
		}
		 
		if($this->_cursor >= $this->_count){
			return false;
		}else{
			$this->_revinded++;
			if($this->_revinded % $this->_slice_size == 0){
				$this->_must_revind=true;
				return false;
			}
		}
		return true;	
	}

	function key()
	{
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
		return $this->_cursor;
	}
	function rewind()
	{
	
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
		
		if(!$this->_is_sliced){
			$this->_cursor=0;
			return;
		}
		
		if($this->_must_revind){
			$this->_must_revind=false;
		}else{
			$this->_cursor=0;
		}
		
	}
	function offsetGet( $index )
	{
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
		if(is_numeric($index)){
			$this->_cursor = $index;
			return $this;
		}else{
			return $this->{$index};
		}
	}

	function offsetExists($offset) {
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
	   return isset($this->_data[$this->_cursor]);
	}
	
	function offsetSet($offset, $value) {
        if (is_null($offset)) {
            //ничего пока не делать
        } else {
	        $this->{$offset} = $value;
        }
    }

	function seek ($position)
	{
		if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
		}
		$this->_cursor = $position;
	}
	function offsetUnset($offset) {
		//unset($this->_data[$this->_cursor]);
		//Я этого делать не буду. Пока.
	}
	//Получение шаблона и вывод
	public function show()
	{
		if($this->template!=''){
			doitClass::$instance->this = $this;
			return doitClass::$instance->call($this->template);
		}
		return '';
	}
	
	public static function show_columns_fast($tablename){
	
		if(isset(ActiveRecord::$_columns_cache [$tablename])){
			return ActiveRecord::$_columns_cache [$tablename];
		}
	
		//Функция получает имя таблицы. Если таблицы не существует, она возвращает false
		$_res=doitClass::$instance->db->query('SELECT * FROM '.DB_FIELD_DEL.$tablename.DB_FIELD_DEL.' LIMIT 0');
		if ($_res!==false) {
			$columns  = array();
			$columns_count =  $_res->columnCount();
			for($i=0;$i<=$columns_count-1;$i++){
				$column = $_res->getColumnMeta($i);
				$columns[]=$column['name'];
			}
			ActiveRecord::$_columns_cache[$tablename] = $columns;
			return $columns;
		}
		ActiveRecord::$_columns_cache[$tablename] = false;
		return false;
	}
	
	public function columns($tablename='')
	{
		if($tablename=='') {
			$tablename = $this->_options['table'];
		}
		
		return ActiveRecord::show_columns_fast($tablename);
		
		if(!isset (doitClass::$instance->datapool['columns_registry'])) {
			doitClass::$instance->datapool['columns_registry']=array();
			doitClass::$instance->datapool['_known_fields']=array();
		}
		//FIXME: ложные срабатывания
		if(false && isset (doitClass::$instance->datapool['columns_registry'][$tablename])) {
			return doitClass::$instance->datapool['columns_registry'][$tablename];
		}
		if ($tablename=='template') {
			//template - ключевое частозапрашиваемое поле, такой таблицы не существует
			return doitClass::$instance->datapool['columns_registry'][$tablename]=false;
		}
		
		$_res=doitClass::$instance->db->query('SHOW COLUMNS FROM '.DB_FIELD_DEL.$tablename.DB_FIELD_DEL);
		if ($_res===false) {
			//Если таблицы не существует
			return doitClass::$instance->datapool['columns_registry'][$tablename]=false;
		}
		
		$result_array=array();
		foreach ($_res->fetchAll(PDO::FETCH_NUM) as $_tmpline) {
			$result_array[] = $_tmpline[0];
		}
		return  doitClass::$instance->datapool['columns_registry'][$tablename] = $result_array;
	}

	
	//Рекурсивная функция для быстрой сортировки дерева
	private function get_subtree($id)
	{
		$_tmparr=array();
		$_class_name = get_class($this);
		foreach($this->_data as $element){
 			if(isset($element[$this->_options['plural_to_one']."_id"]) && $element[$this->_options['plural_to_one']."_id"] == $id) {
				if(empty($this->_used_tree_branches[$element['id']])){
					$this->_used_tree_branches[$element['id']]=true;
					$_tmparr[] = new  $_class_name (array('table'=>$this->_options['table'], 'data'=>array( $element ),'tree'=>$this->get_subtree($element['id'])));
				}
 			}
		}
		return $_tmparr;
	}
	
	public function tree($root=false)
	{
		//Если ленивый запрос ещё не произошёл - самое время.
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		
		//Если при создании объекта заранее указали его дерево - возвращаем его
		if ($this->_options['tree']!==false) {
			return $this->_options['tree'];
		}		
		$_tmparr=array();
		$_class_name = get_class($this);
		if (is_object($root)) {
			$root=$root->id;
		}
		$this->_used_tree_branches=array();
		
		if($root === false) {
			foreach($this->_data as $element){
				//Если данный элемент корневой, родительских элементов нет, поле element_id пустое
				if(!isset($element[$this->_options['plural_to_one']."_id"])) {
					//В опцию tree записываем рекурсивно полученные дочерние элементы
					if(empty($this->_used_tree_branches[$element['id']])){
						$this->_used_tree_branches[$element['id']]=true;
						$_tmparr[] = new  $_class_name (array('table'=>$this->_options['table'], 'data'=>array( $element ),'tree'=>$this->get_subtree($element['id'])));
					}	
				}
			}
		} else {
		 
			foreach($this->_data as $element){
				//Если данный элемент корневой, родительских элементов нет, поле element_id == root
				if(isset($element[$this->_options['plural_to_one']."_id"]) && ($element[$this->_options['plural_to_one']."_id"]== $root )) {
					//В опцию tree записываем рекурсивно полученные дочерние элементы
					if(empty($this->_used_tree_branches[$element['id']])){
						$this->_used_tree_branches[$element['id']]=true;
						$_tmparr[] = new  $_class_name (array('table'=>$this->_options['table'], 'data'=>array( $element ),'tree'=>$this->get_subtree($element['id'])));
					}	
				}
			}
		}
		
		$this->_used_tree_branches=array();
		return $_tmparr;
	}
	
	public function is_empty()
	{
		if ($this->_options['queryready']==false) {
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
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		return count($this->_data);
	}
	
	public function table()
	{
		return $this->_options['table'];
	}
	public function override()
	{
		return '';
	}
	public function expand()
	{
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		if(isset($this->_data[0])) {
			foreach( $this->_data[0] as $_key=>$_value) {
				doitClass::$instance->{$_key} = $_value;
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
		doitClass::$instance->{$varname} = $this->all;
		return $this;
	}
	
	public function expand_to($varname)
	{
		doitClass::$instance->{$varname} = $this->one;
		return $this;
	}
	
	function to_array()
	{
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		foreach ($this->_data as $key=>&$value){
			$value['table'] = $this->_options['table'];
		}
		return $this->_data;
	}

	function to_json()
	{
		return json_encode($this->to_array);
	}
	function __set($name,$value)
	{	
		if(method_exists($this,'set_'.$name)) {
			$this->{'set_'.$name}($value);
		} else {
			$this->set_variable_value($name,$value);
		}
	}

	function set($name,$value)
	{
		//Если создаём, то проверяем, был ли уже new
		//Если редактируем, то предварительно надо сбрасывать в ноль
		$this->_future_data[$name]=$value;
	}
	
	function set_variable_value($name,$value)
	{
		//Если создаём, то проверяем, был ли уже new
		//Если редактируем, то предварительно надо сбрасывать в ноль
		$this->_future_data[$name]=$value;
	}
	
	function get_variable_value($name)
	{
		return $this->get($name);
	}

	function only($field)
	{
		return $this->where(DB_FIELD_DEL . "is_{$field}".DB_FIELD_DEL . " = 1");
	}
	
	/**
	 * Возвращает ключ массива (курсор, для обращения как к элементу массива), по id объекта
	 *
	 * @param $id ID Объекта
	 * @return int искомый ключ
	 */
	function get_cursor_key_by_id($id)
	{
		$key=0;
		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		if($this->_get_by_id_cache===false){
			$this->_get_by_id_cache=array();
			foreach ($this->_data as $key=>$value){
				$this->_get_by_id_cache[$value['id']]=$key;
			}
			if(isset($this->_get_by_id_cache[$id])){
				return $this->_get_by_id_cache[$id];
			}
		}else{
			if(isset($this->_get_by_id_cache[$id])){
				return $this->_get_by_id_cache[$id];
			}
		}
		return $key;
	}

	function __get($name)
	{

		//Item.something
		if (method_exists($this, $name)) {
			return $this->{$name}();
		}

		//Item.ml_title
		if (substr($name, 0, 3) == 'ml_') {
			$lang = doitClass::$instance->lang;
			if ($lang != '') {
				return $this->{$lang.substr($name,2)};
            }
		}

		//Item.new
		if ($name=='new') { // Crud - create
			$this->_options['new']=true;
			$this->_future_data = array();
			return $this;
		}
		//Item.expand_to_page
		//DEPRECATED: в дальнейшем будет удалена
		/*
		if (substr($name,0,10)=='expand_to_') {
			return $this->expand_to(substr($name,10));
		}
		*/
		
		if (substr($name,0,4)=='all_') {
			return $this->all_of(substr($name,4));
		}
		
		if (substr($name,0,3)=='to_') {
			if ($this->_options['queryready']==false) {
				$this->fetch_data_now();
			}
			$many_to_many_table = $this->calc_many_to_many_table_name(substr($name,3),$this->_options['table']);	
			$column = ActiveRecord::plural_to_one(strtolower(substr($name,3))).'_id';
			$current_column = $this->_options['plural_to_one']."_id";
			if(isset($this->_data[0])){
				$result = doitClass::$instance->db->query("SELECT " . DB_FIELD_DEL . $column . DB_FIELD_DEL . " FROM ".et($many_to_many_table )." WHERE ". DB_FIELD_DEL . $current_column . DB_FIELD_DEL ." = ". e($this->_data[$this->_cursor]['id']))->fetchAll(PDO::FETCH_COLUMN);
				return implode(',',$result);
			}else{
				return '';
			}
			
		}
		
		//d()->User->groups_throw_roles
		$throw_substr= strpos($name,'_throw_');
		if($throw_substr!==false) {
		
			$first_word = substr($name,0,$throw_substr);
			$second_word = substr($name,$throw_substr+7);
			return $this->{$second_word}->all_of($first_word);
		}
		
		//Item.expand_all_to_pages
		//DEPRECATED: в дальнейшем будет удалена
		if (substr($name,0,14)=='expand_all_to_') {
			return $this->expand_all_to(substr($name,14));
		}
 		
		return $this->get($name,true);
	}
	
	/* 
	Получение переменных напрямую
	В случае необходимости получения в модели непосредственно значения переменной
	Например
	class User extends ar {
		function title()
		{
			return '<b>'.$this->get('title').'</b>';
		}
	}
	print doitClass::$instance->User->find(1)->title;
	*/
	public function get($name, $mutilang=false)
	{

		if ($this->_options['queryready']==false) {
			$this->fetch_data_now();
		}
		
		if(isset($this->_future_data[$name])){
			return $this->_future_data[$name];
		}
		
		if($mutilang && doitClass::$instance->lang != '' && doitClass::$instance->lang!=''){
			if (isset($this->_data[$this->_cursor]) && isset($this->_data[$this->_cursor][doitClass::$instance->lang.'_'.$name]) && $this->_data[$this->_cursor][doitClass::$instance->lang.'_'.$name]!='') {	
				return $this->get(doitClass::$instance->lang.'_'.$name);
			}
		}
		
		if (isset($this->_data[$this->_cursor])) {
			//Item.title         //Получение одного свойства
			if (isset($this->_data[$this->_cursor][$name])) {
				if(isset($this->_data[$this->_cursor]['admin_options']) &&  ($this->_data[$this->_cursor]['admin_options']!='') && $this->_safe_mode === false  ){
					$admin_options = unserialize( $this->_data[$this->_cursor]['admin_options']);
					
					if(isset($admin_options[$name])){
						return preg_replace_callback(
							'/\<img\ssrc=\"\/cms\/external\/tiny_mce\/plugins\/mymodules\/module\.php\?([\@\-\_0-9a-zA-Z\&]+)\=([\-\_0-9a-zA-Z\&]+)\"\s\/\>/',
							create_function(
								 
								'$matches',
								'if(isset(d()->plugins[str_replace("@","#",$matches[1])])){return d()->call(str_replace("@","#",$matches[1]),array($matches[2]));};return "";'
							),
							$this->_data[$this->_cursor][$name]
						);
						 
						
					}
				}
				return $this->_data[$this->_cursor][$name];
			}

			if(!isset(doitClass::$instance->datapool['_known_fields'][$this->_options['table']][$name])){


				//Item.user          //Получение связанного объекта
				$_is_column_exists=false;
				if (isset($this->_data[$this->_cursor][$name.'_id'])) {
					$_is_column_exists=true;
				} else {
					//Проверка на факт наличия столбца $name.'_id'
					$columns = $this->columns();
					if($columns !== false) {
						$columns = array_flip($columns);//TODO: возможно, array_keys будет быстрее
						if (isset($columns[$name.'_id'])) {
							$_is_column_exists=true;
						}
					}
				}

				if($_is_column_exists==true){
					if(!isset($this->_objects_cache[$name])){
						/* кеш собранных массивов */
						$ids_array=array();
						foreach($this->_data as $key=>$value){
							$ids_array[$value[$name.'_id']]=true;
						}
						$ids_array=array_keys($ids_array);
						$this->_objects_cache[$name] =  activerecord_factory_from_table(ActiveRecord::one_to_plural($name))->order('')->where(' '.DB_FIELD_DEL . id . DB_FIELD_DEL. ' IN (?)',$ids_array);
					}
					$cursor_key=$this->_objects_cache[$name]->get_cursor_key_by_id($this->_data[$this->_cursor][$name.'_id']);
					return $this->_objects_cache[$name][$cursor_key];
				}



				//Item.users
				//1. Поиск альтернативных подходящих столбцов

				//TODO: удалить позже
				$foundedfield = false;


				//ищем поле item_id в таблице users
				//??щем таблицу с названием $name (например, users)
				$columns = $this->columns($name);

				if ($columns===false && $name=='template') {
					return ''; //template - ключевое частозапрашиваемое поле, данный оборот ускорит работу
				}

				/*
				 DEPRECATED - лишние запросы
				if ($columns===false) {

					$_tmpael  = activerecord_factory_from_table($this->_options["table"]);
					return $_tmpael->find_by('url',$name);
				}
				*/
				
				//при запросе users возможны несколько случаев
				//Четрые варианта: 1. есть И user_id, 2. и (3. или) users_to_groups, 4. только вспомогательная таблица
				//При запросе users_over_memberships преобразуем $name в users
				
				$over_position = strpos($name,'_over_');
				if($over_position!==false){
					$over_method = substr($name, $over_position+6);
					$name = substr($name, 0, $over_position);
					$_tmpael  = activerecord_factory_from_table($name);
					$second_table_column = ActiveRecord::plural_to_one(strtolower($name)).'_id';
					//Проверка на факт наличия таблицы users_to_groups
					$ids_array = $this->{$over_method}->select($second_table_column)->to_array;
					$ids = array();
					foreach($ids_array as $key=>$value){
						$ids[] = $value[$second_table_column];
					}
					return $_tmpael->where("`id` IN (?)", $ids);
					
					
				}else{
					$many_to_many_table = $this->calc_many_to_many_table_name($name,$this->_options['table']);
					$many_to_many_table_columns = $this->columns($many_to_many_table);
				}
				


				foreach($columns as $key=>$value) {
					if ($value == $this->_options['plural_to_one']."_id") {
						$_tmpael  = activerecord_factory_from_table($name);
						//Проверка на факт наличия таблицы users_to_groups
						if($many_to_many_table_columns !=false){
							$many_to_many_table_columns  = array_flip($many_to_many_table_columns);
							$first_table_column = $this->_options['plural_to_one']."_id"; //group_id
							$second_table_column = ActiveRecord::plural_to_one(strtolower($name)).'_id'; //user_id
							if(isset($many_to_many_table_columns[$first_table_column]) && isset($many_to_many_table_columns[$second_table_column])){
								//Таблица users_to_groups существует, и нужные столбцы есть в наличии
								return $_tmpael->where($this->_options['plural_to_one']."_id = ? OR `id` IN (SELECT {$second_table_column} FROM ".et($many_to_many_table)." WHERE {$first_table_column} =  ?)",$this->_data[$this->_cursor]['id'],$this->_data[$this->_cursor]['id']);								
							}
						}
						return $_tmpael->where($this->_options['plural_to_one']."_id = ?",$this->_data[$this->_cursor]['id']);
					}
				}
				
				//Третий вариант: есть только users_to_groups
				$_tmpael  = activerecord_factory_from_table($name);
				//Проверка на факт наличия таблицы users_to_groups
				if($many_to_many_table_columns !=false){
					$many_to_many_table_columns  = array_flip($many_to_many_table_columns);
					$first_table_column = $this->_options['plural_to_one']."_id"; //group_id
					$second_table_column = ActiveRecord::plural_to_one(strtolower($name)).'_id'; //user_id
					if(isset($many_to_many_table_columns[$first_table_column]) && isset($many_to_many_table_columns[$second_table_column])){
						//Таблица users_to_groups существует, и нужные столбцы есть в наличии
						return $_tmpael->where("`id` IN (SELECT {$second_table_column} FROM ".et($many_to_many_table)." WHERE {$first_table_column} =  ?)",$this->_data[$this->_cursor]['id']);								
					}
				}
			}
			return '';
		} else {
			//Item.ramambaharum_mambu_rum
			return '';
		}
	}
}

function activerecord_factory($_modelname)
{
	if(is_array($_modelname)) {
		$_modelname=$_modelname[0];
	}
	return new $_modelname ();
	//return new ar(array('table'=>ar::one_to_plural(strtolower($_modelname))));
}
function activerecord_factory_from_table($_tablename, $suffix = '')
{
	if(is_array($_tablename)) {
		$_tablename=$_tablename[0];
	}
	
	$_modelname=ActiveRecord::plural_to_one(strtolower($_tablename));
	$_first_letter=strtoupper(substr($_modelname,0,1));
	$_modelname = $_first_letter.substr($_modelname,1) . $suffix;

	return new $_modelname ();
	//return new ar(array('table'=>ar::one_to_plural(strtolower($_modelname))));
}

//DEPRECATED - Для совместимости
class ar extends ActiveRecord
{
	
}
