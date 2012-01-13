<?php


class Auth extends UniversalSingletoneHelper
{	
	private $_id=0;
	function is_guest()
	{
		if(!isset($_SESSION['auth']) || $_SESSION['auth']==''){
			return true;
		}
		return false;
	}
	function is_authorised()
	{
		if(!isset($_SESSION['auth']) || $_SESSION['auth']==''){
			return false;
		}
		return true;
	}
	
	function login($userid=0){
		$_SESSION['auth']=$userid;
		$this->_id=$userid;
	}
	
	function logout(){
		unset($_SESSION['auth']);
		$this->_id=false;
	}
	
	function id()
	{
		return $_SESSION['auth'];
	}
	

}