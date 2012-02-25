<?php
	
class MailTest extends Test
{
	function test_filename_from()
	{
		$this->assertEquals(Mail::filename_from('user.doc'),'user.doc');
		$this->assertEquals(Mail::filename_from('/user.doc'),'user.doc');
		$this->assertEquals(Mail::filename_from('/user/users/tmp.3.docx'),'tmp.3.docx');
		$this->assertEquals(Mail::filename_from('/user/use.rs/tmp.3.docx'),'tmp.3.docx');
		$this->assertEquals(Mail::filename_from('docx'),'docx');
		$this->assertEquals(Mail::filename_from('.docx'),'.docx');
		$this->assertEquals(Mail::filename_from('/.docx'),'.docx');
	}
}
 
?>