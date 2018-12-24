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
	public $error_info='';
	private $options=array();
	
	function to($to)
	{
		$this->options['to']=strtolower($to);
	}
	function from($from,$name=false)
	{
		$longfrom = $from;
		if($name!==false){
			if(substr($from,-1) !='>'){
				$longfrom = ' <'.$from.'>';
			}

			$name = "=?UTF-8?B?".base64_encode( $name )."?=";
			$longfrom = $name . $longfrom;
		}
		$this->options['from']=$from;
		$this->options['longfrom']=$longfrom;
		
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
		$this->options['file_adress'][]=$file_adress;
		$this->options['file_name'][]=$file_name;
		
	}

	function attach_file_contents($file_contents,$file_name='')
	{

		$this->options['file_contents']=$file_contents;
		$this->options['file_name']=$file_name;
		
	}
	function set_smtp($adress,$port,$login,$password,$ssl='ssl'){
		$this->options['smtp']=$adress;
		$this->options['port']=$port;
		$this->options['login']=$login;
		$this->options['password']=$password;
		$this->options['ssl']=$ssl;
		$this->options['use_smtp']=true;
		 
	}
	
	function send()
	{
		if(isset($this->options['use_smtp']) && ($this->options['use_smtp']==true)){
			return $this->send_phpmailer();
		}else{
			return $this->send_pure_mail();
		}
	}
	
	function send_phpmailer()
	{
		
		include_once('cms/mod_helpers/vendors/class.phpmailer.php');
		
		$mail             = new PHPMailer();
		$mail->CharSet    = 'UTF-8';
		$mail->IsSMTP();
		$mail->PluginDir  = 'cms/mod_helpers/vendors/';

		$mail->SMTPAuth   = true;
		$mail->SMTPSecure = $this->options['ssl'];
		$mail->Host       = $this->options['smtp'];
		$mail->Port       = $this->options['port'];
		$mail->Username   = $this->options['login']; 
		$mail->Password   = $this->options['password']; 

		if($this->options['from']){
			$mail->SetFrom($this->options['from'], $this->options['from']);
		}else{
			$mail->SetFrom($this->options['login'], $this->options['login']);
		}

		$mail->AddAddress($this->options['to'], $this->options['to']);
		$mail->Subject    = $this->options['subject'];
		
		
		foreach ($this->options['file_adress'] as $key => $val) {
			$mail->AddAttachment($this->options['file_adress'][$key], $this->options['file_name'][$key]);
		}
		
		
		if(isset($this->options['file_contents'])){
			$mail->AddStringAttachment(($this->options['file_contents']), $this->options['file_name']);
		}
		
		$this->options['file_adress']=array ();
		$this->options['file_name']=array ();

		$mail->MsgHTML($this->options['message']);
		$result =  $mail->Send();
		if(!$result){
			$this->error_info = $mail->ErrorInfo;
		}
		return $result;
	}
	
	function send_pure_mail()
	{
		if (count($this->options['file_adress']) > 0) {
			$adr = $this->options['file_adress'][count($this->options['file_adress']) - 1];
		} 
		if(!isset($adr) && !isset($this->options['file_contents'])){
			$headers = '';
			if($this->options['longfrom']){
				$headers = "From: ".$this->options['longfrom']."\r\n";
			}
			$headers .= "Content-Type: text/html; charset=\"UTF-8\"";
			$result =  mail($this->options['to'],"=?UTF-8?B?".base64_encode( $this->options['subject'])."?=",
				$this->options['message'],
				$headers);
			$this->options=array();
			return $result;
		} else {
			 
			$headers = "";
			
			
			
			$semi_rand = md5(time().rand()); 
			$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
			$headers .= "MIME-Version: 1.0\n"."Content-Type: multipart/mixed;\n"." boundary=\"{$mime_boundary}\""; 

			$email_message = "This is a multi-part message in MIME format.\n\n"."--{$mime_boundary}\n"."Content-Type:text/html; charset=\"UTF-8\"\n"."\n". $this->options['message'] ."\n\n";
			if(isset($this->options['file_contents'])){
				$data = chunk_split(base64_encode($this->options['file_contents']));
			}else{
				$data = chunk_split(base64_encode(file_get_contents($adr)));
			}
			$email_message .= "--{$mime_boundary}\n"."Content-Type: application/octet-stream;\n"." name=\"".$this->options['file_name'][count($this->options['file_name']) - 1]."\"\n"."Content-Transfer-Encoding: base64\n\n".$data."\n\n"."--{$mime_boundary}--\n"; 
			 
			
			
				
			
			 
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

