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

	function attach_file_contents($file_contents,$file_name='')
	{

		$this->options['file_contents']=$file_contents;
		$this->options['file_name']=$file_name;
		
	}
	function set_smtp($adress,$port,$login,$password){
		$this->options['smtp']=$adress;
		$this->options['port']=$port;
		$this->options['login']=$login;
		$this->options['password']=$password;

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

				$mail->SMTPAuth   = true;                  // enable SMTP authentication
				$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
				$mail->Host       = $this->options['smtp'];      // sets GMAIL as the SMTP server
				$mail->Port       = $this->options['port'];                   // set the SMTP port for the GMAIL server
				$mail->Username   = $this->options['login'];  // GMAIL username
				$mail->Password   = $this->options['password'];            // GMAIL password

				$mail->SetFrom($this->options['login'], $this->options['login']);


				$mail->AddAddress($this->options['to'], $this->options['to']);
				$mail->Subject    = $this->options['subject'];
				
				if(isset($this->options['file_adress'])){
					$mail->AddAttachment($this->options['file_adress'], $this->options['file_name']);
				}
				
				if(isset($this->options['file_contents'])){
					$mail->AddStringAttachment(($this->options['file_contents']), $this->options['file_name']);
				}
				
 
				$mail->MsgHTML($this->options['message']);
				$mail->Send();			
	}
	
	function send_pure_mail()
	{
	
		if(!isset($this->options['file_adress']) && !isset($this->options['file_contents'])){
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
			if(isset($this->options['file_contents'])){
				$data = chunk_split(base64_encode($this->options['file_contents']));
			}else{
				$data = chunk_split(base64_encode(file_get_contents($this->options['file_adress'])));
			}
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


