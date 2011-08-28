<?php 
	header('Content-type: text/html; Charset=UTF-8');
	include('markdown.php');
	$content =  Markdown(file_get_contents('about.md'));
?><!doctype html>
<html>
<head>
	<meta charset="UTF-8">
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
		font-family: Palatino, "Palatino Linotype", "URW Palladio L", "Book Antiqua", Georgia, serif;
		line-height: 1.45;
	}
	</style>
</head>
<body>
	<?=$content ?>
</body>
</html>
