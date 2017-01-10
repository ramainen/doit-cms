<?php
// URL translit - элемент управления для вывода кнопки транслитерации URL

d()->route('/admin/urltranslit',function(){
	if(!iam()){
		print 'ERROR';
		exit;
	}
	$table = $_POST['table'];
	$field = $_POST['field'];
	$title = $_POST['title'];
	$id = $_POST['id'];
	if(trim($title) == ''){
		print '';
		exit;
	}
	if(transliterate_url(trim($title)) == ''){
		print '';
		exit;
	}
	$urls_set = array();
	if(is_numeric($id)){
	//	$data = d()->db->query('SELECT distinct `'.et($field).'` from `'.et($table).'` where id != '.e($id))->fetchAll(PDO::FETCH_ASSOC);
	}else{
	//	$data = d()->db->query('SELECT distinct `'.et($field).'` from `'.et($table).'`')->fetchAll(PDO::FETCH_ASSOC);
	}
	foreach($data as $key=>$value){
		if(isset($value[ $field ]) && $value[ $field ]!=''){
			$urls_set[$value[ $field ]] = $value[ $field ];
		}
	}
	 
	
	$url = transliterate_url($title);
	$checked_urls = array();
	if(is_numeric($id)){
	 	$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where id != '.e($id). ' AND `'.et($field).'` = '.e($url).' limit 1')->fetchAll(PDO::FETCH_ASSOC);
	}else{
	 	$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where  `'.et($field).'` = '.e($url).  ' limit 1')->fetchAll(PDO::FETCH_ASSOC);
	}
	$check_first_url = (count($data)!=0);
	
	if ($check_first_url) {
		$checked_urls[$url] = true;
		$url .= '-2';
		
		if(is_numeric($id)){
			$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where id != '.e($id). ' AND `'.et($field).'` = '.e($url).' limit 1')->fetchAll(PDO::FETCH_ASSOC);
		}else{
			$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where  `'.et($field).'` = '.e($url).  ' limit 1')->fetchAll(PDO::FETCH_ASSOC);
		}
		$check_first_url = (count($data)!=0);
		
		while ($check_first_url  && !isset($checked_urls[$url])) {
			
			if(is_numeric($id)){
				$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where id != '.e($id). ' AND `'.et($field).'` = '.e($url).' limit 1')->fetchAll(PDO::FETCH_ASSOC);
			}else{
				$data = d()->db->query('SELECT  `'.et($field).'` from `'.et($table).'` where  `'.et($field).'` = '.e($url).  ' limit 1')->fetchAll(PDO::FETCH_ASSOC);
			}
			$check_first_url = (count($data)!=0);
			
			$checked_urls[$url] = true;
			$suffix = ltrim(strrchr($url, '-'), '-');
			 
			$url = substr($url, 0, - (strlen($suffix)+1)) . '-' . (1 * $suffix + 1);

		}
		
	}
	print $url;
	
	exit;
	
});