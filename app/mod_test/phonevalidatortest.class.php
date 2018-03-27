<?php 

class PhonevalidatorTest extends Test
{
	function test_phone()
	{
	
		
		
		$this->assertEquals(d()->convert_phone('+79876543210'),'79876543210');
		$this->assertEquals(d()->convert_phone('79876543210'),'79876543210');
		$this->assertEquals(d()->convert_phone('9876543210'),'79876543210');
		$this->assertEquals(d()->convert_phone('89876543210'),'79876543210');
		$this->assertEquals(d()->convert_phone('89876543(210)'),'79876543210');
		
		
		$this->assertEquals(d()->convert_phone('+79047640961'),'79047640961');
		$this->assertEquals(d()->convert_phone('9047640961'),'79047640961');
		$this->assertEquals(d()->convert_phone('79047640961'),'79047640961');
		$this->assertEquals(d()->convert_phone('89047640961'),'79047640961');
		$this->assertEquals(d()->convert_phone('8(904)7640961'),'79047640961');
		$this->assertEquals(d()->convert_phone('8(904)764-0-961'),'79047640961');
		$this->assertEquals(d()->convert_phone('+7(904) 764-09-61'),'79047640961');
		
		
		$this->assertEquals(d()->convert_phone_human('+79047640961'),'+7(904) 764-09-61');
		$this->assertEquals(d()->convert_phone_human('9047640961'),'+7(904) 764-09-61');
		$this->assertEquals(d()->convert_phone_human('79047640961'),'+7(904) 764-09-61');
		$this->assertEquals(d()->convert_phone_human('89047640961'),'+7(904) 764-09-61');
		$this->assertEquals(d()->convert_phone_human('8(904)7640961'),'+7(904) 764-09-61');
		$this->assertEquals(d()->convert_phone_human('8(904)764-0-961'),'+7(904) 764-09-61');
		$this->assertEquals(d()->convert_phone_human('+7(904) 764-09-61'),'+7(904) 764-09-61');
		
		
		$this->assertEquals(d()->convert_phone_clean('+7(904) 764-09-61'),'9047640961');
		
		$this->assertEquals(d()->convert_phone_plus('+7(904) 764-09-61'),'+79047640961');
		
		
		$this->assertEquals(d()->validate_phone('+79047640961'),true);
		$this->assertEquals(d()->validate_phone('9047640961'),true);
		$this->assertEquals(d()->validate_phone('79047640961'),true);
		$this->assertEquals(d()->validate_phone('89047640961'),true);
		$this->assertEquals(d()->validate_phone('8(904)7640961'),true);
		$this->assertEquals(d()->validate_phone('8(904)764-0-961'),true);
		$this->assertEquals(d()->validate_phone('+7(904) 764-09-61'),true);

		
		
		
		$this->assertEquals(d()->validate_phone('+790476409611'),false);
		$this->assertEquals(d()->validate_phone('+7904764096111111'),false);
		$this->assertEquals(d()->validate_phone('90476409611'),false);
		$this->assertEquals(d()->validate_phone('790476409611'),false);
		$this->assertEquals(d()->validate_phone('8904764096'),false);
		$this->assertEquals(d()->validate_phone('8(904)764096'),false);
		$this->assertEquals(d()->validate_phone('8(904)764-0-96'),false);
		$this->assertEquals(d()->validate_phone('+7(904)'),false);
		$this->assertEquals(d()->validate_phone('+'),false);
		
	}
}