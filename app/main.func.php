<?php
function main()
{
	d()->content = d()->content();
	d()->main = d()->main_tpl();
	return d()->render('main');
}
