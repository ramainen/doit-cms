<?php
function show_page()
{
	
	d()->page = d('Text')->find_by_url(url(1))->one;
	 
	  
	if (d()->page->is_empty) {
		print "Страница не существует";
		d()->newurl=url(1);
		d()->table='texts';
		print d()->addbutton();
		d()->stop_next_chains();
		
	}

}