<?php



class Validator
{
	 public $is_valid=true;
	 public $value = '';
	 public $name = '';
	 public static $notices=array();

	 public function __construct($value=null, $name='data'){
	 	$this->value = $value;
	 	$this->name = $name;
	 	return $this;
	 }

	 public static function create($value=null, $name='data'){
	 	return new Validator($value,$name);
	 }

	 public function get()
	 {
		return $this->value;
	 }

	 public function to_string()
	 {
		return "{$this->value}";
	 }

	 public function toString()
	 {
		return "{$this->value}";
	 }

	 public function __toString()
	 {
		return "{$this->value}";
	 }

	 public function to_int()
	 {
	 	return (int) $this->value;
	 }

	 public function toInt()
	 {
	 	return (int) $this->value;
	 }

	 public function default_value($value)
	 {
	 	if(empty($this->value)){
	 		$this->value=$value;
	 	}
	 	return $this;
	 }

	 public function check()
	 {
	 	$args = func_get_args();
	 	foreach ($args as $callable){
	 		//call ($callable)
	 	}
	 	return $this;
	 }

	 public function required($alert=''){
	 	if(empty($this->value)){
	 		self::$notices[$this->name]=$alert;
	 	}
		return $this;
	 }
	 
	 function __get($name)
	{

		//Item.something
		if (method_exists($this, $name)) {
			return $this->{$name}();
		}
		return $this;
	}
	
	public function __callStatic($name, $param)
	{
		return Validator::create($_POST[$name], $name);
	}
	
	 public  static function add_notice($text, $element=false)
	 {
	 	//if (get_called_class()==get_class() &&   !is_null($this) ){
	 	if (!is_object($this) && $this instanceof Validator ){
			self::$notices[$this->name]=$text;
	 	}else{
		 	if($element==false){
		 		self::$notices[]=$text;
		 	}else{
		 		self::$notices[$element]=$text;
		 	}
		 }
		//var_dump($this);	 	
	 }

	/* public function add_notice($text='')
	 {
	 	self::$notices[$this->name]=$text;
	 	return $this;
	 }
*/
	 public static function notices()
	 {
		return self::$notices;
	 }

	 
	 
	 public function valid_email($alert=''){
		$value=strtolower($this->value);
		if ( 1 != preg_match(
			'/^[-a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~]+(?:\.[-a-z0-9!' .
				'\#\$\%\&\'\*\+\/\=\?\^\_\`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*'.
				'(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/' ,$value)){
			self::$notices[$this->name]=$alert;
		}
		return $this;
	 }

	 public function antispam($alert='')
	 {
		$msg=strtolower($this->value);
		if ( strpos( $msg,'<a')!==false  ||  strpos( $msg,'лbных клиентов')!==false ||  strpos( $msg,'лbных клиентoв')!==false ||  strpos( $msg,'пoтенциалbных клиентов')!==false  ||  strpos( $msg,'базу данных потенциальных клиентов')!==false ||  strpos( $msg,'база данных потенциальных клиентов')!==false  ||  strpos( $msg,'база дaнныx пoтенциальных клиентов')!==false  ||  strpos( $msg,'базу дaнныx пoтенциальных клиентов')!==false  ||  strpos( $msg,'потeнциaлbных клиентoв')!==false  ||  strpos( $msg,'клиeнтская бaзa')!==false ||  strpos( $msg,'клиeнтская база')!==false || strpos( $msg,'клиeнтcкие базы')!==false || strpos( $msg,'ские бaзы')!==false || strpos( $msg,'ские базы')!==false ||strpos( $msg,'cкие бaзы')!==false  ||strpos( $msg,'cкие базы')!==false ||strpos( $msg,'скиe бaзы')!==false ||strpos( $msg,'скиe базы')!==false ||strpos( $msg,'cкиe бaзы')!==false ||strpos( $msg,'cкиe базы')!==false ||   strpos( $msg,'клиeнтские бaзы')!==false  ||  strpos( $msg,'клиeнтские базы')!==false || strpos( $msg,'ентсkи')!==false || strpos( $msg,'prodawez')!==false || strpos( $msg,'kлиент')!==false ||  strpos( $msg,'[url')!==false || strpos( $msg,'http:/')!==false || strpos( $msg,'sex')!==false || strpos( $msg,'poker')!==false || strpos($msg,'casino')!==false    )  {
			self::$notices[$this->name]=$alert;
		}
		return $this;
	 }

	 public function valid_phone($alert='')
	 {
		$value=strtolower($this->value);
		if ( 1 != preg_match(
			'/^((8|\+7)[\- ]?)?(\(?\d{3,5}\)?[\- ]?)?[\d\- ]{7,10}$/' ,$value)){
			self::$notices[$this->name]=$alert;
		}
		return $this;
	 }

	 public function confirmation($alert='')
	 {

	 }

	 public function exists($alert='')
	 {

	 }

	 /**
	 * Для скрытых антиспам полей, которые нельзя заполнять
	 *
	 */
	 public function valid_empty($alert='')
	 {

	 }

	 public function not_empty($alert='')
	 {

	 }


	 public function valid_int($alert='')
	 {

	 }

	 public function filter()
	 {
	 	$args = func_get_args();
	 	foreach ($args as $callable){
	 		//call ($callable)
	 	}
	 	return $this;
	 }

	 public static function is_valid()
	 {
	 	return count(self::$notices)==0;
	 }

	 /*
	 
	 array(4) {
	  ["phone"]=>
	  string(0) ""
	  ["title"]=>
	  string(0) ""
	  ["email"]=>
	  string(0) "email invalid"
	  [0]=>
	  string(46) "Ваще запрет на все номера"
	  [1]=>
	  string(49) "Зарегистрироваться нельзя"
	}
	 */
	 public static function ajax_result()
	{
		
		if(! count(self::$notices)==0){
			$response.=  "$('.error, .has-error').removeClass('error').removeClass('has-error');\n";
			if ($_POST['_action'] == htmlspecialchars($_POST['_action'])){
				$response.=  "var _tmp_form = _current_form[0];\n";
			}else{
				$response.=  "var _tmp_form = $($('form')[0]);\n";
			}
			
			
			$first_element=array();
			foreach(array_keys(self::$notices) as $key=>$input){
				if(isset($_POST['_is_simple_names']) && $_POST['_is_simple_names']=='1'){
					$element_name = "'*[name=\"".$input."\"]'";
				}else{
					$element_name = "'*[name=\"".$_POST['_element'].'['.$input.']'."\"]'";
				}
				$response .=  '$('.$element_name.', _tmp_form).parent().parent().addClass("error").addClass("has-error");'."\n";
				if(isset($_POST['_is_simple_names']) && $_POST['_is_simple_names']=='1'){
					$first_element[] = "*[name=\"".$input."\"]" ;
				}else{
					$first_element[] = "*[name=\"".$_POST['_element'].'['.$input.']'."\"]" ;	
				}
				
			}
			if ($first_element != ''){
				$response .=  "$($('".implode(', ',$first_element)."',  _tmp_form)[0]).focus();"."\n";
			}
			
		}
		return $response;
	}
	
	public static function ajax_notice()
	{
		return self::ajax_result();
	}
	
	public static function ajax_notices()
	{
		return self::ajax_result();
	}
	public static function result()
	{
		if(AJAX){
			return self::ajax_result();	
		}else{
			return self::plain_result();
		}
		
	}
}

 