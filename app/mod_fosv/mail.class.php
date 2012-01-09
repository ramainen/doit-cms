<?php
/**
* Использование: 
* d()->Mail->to('user@example.com');
* d()->Mail->subject('Сообщение');
* d()->Mail->message('Текст сообщения');
* d()->Mail->send();
* 
*/
class Mail extends UniversalSingletoneHelper
{
	private $options=array();
	
	function to($to)
	{
		$this->options['to']=$to;
	}
	
	function subject($subject)
	{
		$this->options['subject']=$subject;
	}
	
	function message($message)
	{
		$this->options['message']=$message;
	}
	
	function attach($file_adress,$file_name='')
	{
		if(is_array($file_adress)){
			$file_name=$file_adress['name'];
			$file_adress=$file_adress['tmp_name'];
		}
		/*вот тут получение имени файла из первого параметра*/
		if($file_name==''){
			//TODO: mime типы
			$file_name=self::filename_from($file_adress);
		}
		$this->options['file_adress']=$file_adress;
		$this->options['file_name']=$file_name;
		
	}
	
	function send()
	{
	
		if(!isset($this->options['file_adress'])){
			$result =  mail($this->options['to'],"=?UTF-8?B?".base64_encode( $this->options['subject'])."?=",
				$this->options['message'],
				"Content-Type: text/html; charset=\"UTF-8\"");
			$this->options=array();
			return $result;
		} else {
			 
			$headers = "";
			$semi_rand = md5(time().rand()); 
			$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
			$headers .= "MIME-Version: 1.0\n"."Content-Type: multipart/mixed;\n"." boundary=\"{$mime_boundary}\""; 

			$email_message = "This is a multi-part message in MIME format.\n\n"."--{$mime_boundary}\n"."Content-Type:text/html; charset=\"UTF-8\"\n"."\n". $this->options['message'] ."\n\n";
			$data = chunk_split(base64_encode(file_get_contents($this->options['file_adress'])));

			$email_message .= "--{$mime_boundary}\n"."Content-Type: application/octet-stream;\n"." name=\"".$this->options['file_name']."\"\n"."Content-Transfer-Encoding: base64\n\n".$data."\n\n"."--{$mime_boundary}--\n"; 
			 
			
			$result =  mail($this->options['to'],"=?UTF-8?B?".base64_encode( $this->options['subject'])."?=",
				$email_message,
				$headers);
			$this->options=array();
			return $result;
			
		}
	}
	
	
	static function filename_from($str){
		$str=explode('/',$str);
		$str=$str[count($str)-1];
		return $str;
	}
}


