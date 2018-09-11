<?php 
	$start_time = microtime(true);
	error_reporting(E_ALL);
	header('Content-type: text/html; Charset=UTF-8');
	$content = "<ul>";
	$f = opendir('.');
	while($file = readdir($f)) {
		if (substr($file,-3) == '.md') {
			$link = '?file=' . substr($file,0,-3);
			$title = file($file);
			$title=$title[0];
			$content .= "<li><a href='{$link}'>{$title}</a></li>";
		}
	}
	$content .= "</ul>";
	
	$file='about';
	if (isset($_GET['file'])) {
		$file = str_replace('/','',$_GET['file']);
		$file = str_replace('.','',$file);	
	}
	if(!file_exists($file.".md")) {
		$file='about';
	}
	include('markdown.php');
	$content .=  Markdown(file_get_contents($file.'.md'));

?><!doctype html>
<html>
<head>
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<meta charset="UTF-8">
	  <script src="http://yandex.st/highlightjs/8.0/highlight.min.js"></script>
	<link rel="stylesheet" href="http://yandex.st/highlightjs/8.0/styles/railscasts.min.css">
	<script>

$(function(){

	$('pre code:contains("d()->")').addClass('language-php')
	var regex = new RegExp(";[а-яА-Я]"); 
	$("pre code").filter(function () {
		if( regex.test($(this).text())){
			$(this).addClass('language-ini')
		}		
	});
hljs.configure({'languages':['php','javascript','html','ini']})
hljs.initHighlightingOnLoad();

})
</script>
<div class="row">


	<script>hljs.initHighlightingOnLoad();</script>
	<title>Документация по системе doit cms</title>
	<style>
	html
	{
		background:#eeeeee;
		margin-top:0;
		padding-top:0;
	}
	body
	{
		background:white;
		padding:30px;
		width:800px;
		margin:auto;
		color: black;
		font-family:   Georgia, serif;
		line-height: 1.5em;
		font-size: 17px;
	}
	pre
	{
		line-height: 1.3em;
		background-color: #F6F6F6;
		overflow: auto;
		border: #CCC solid 1px;
	}
	code
	{
		font-size: .8em;
	}
	h2
	{
		border-bottom:1px solid #ccc;
	}
	</style>
</head>
<body>
	<?php print $content ?>
	<?php 
$exec_time = microtime(true) - $start_time;
printf("<!-- %f seconds -->",$exec_time );
	?>
</body>
</html>
