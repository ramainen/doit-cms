<?php


class AuthTest extends Test
{
	function test_coverage()
	{
		$this->assertCoverage('auth.class.php');
		
	}
	
	function test_exist()
	{
		$this->assertEquals(get_class(d()->Auth,'Auth'));
	}
	
	function test_login()
	{
		$_tmp=$_SESSION;
		unset($_SESSION['auth']);
		$auth = new Auth();
		$this->assertEquals($auth->is_guest, true);
		$this->assertEquals($auth->is_authorised, false);
		
		$auth->login(4);
		$this->assertEquals($auth->is_guest, false);
		$this->assertEquals($auth->is_authorised, true);
		
		$auth->logout();
		
		$this->assertEquals($auth->is_guest, true);
		$this->assertEquals($auth->is_authorised, false);
		
		$auth->login(74);
		$this->assertEquals($auth->id, 74);
		
		$_SESSION=$_tmp;
	}
}
