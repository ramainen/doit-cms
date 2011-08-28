<?php 
	header('Content-type: text/html; Charset=UTF-8');
	include('markdown.php');
	$content =  Markdown(file_get_contents('about.md'));
?><!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<script src="http://yandex.st/highlightjs/6.0/highlight.min.js"></script>
	<link rel="stylesheet" href="http://yandex.st/highlightjs/6.0/styles/default.min.css">
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
	</style>
</head>
<body>
	<?=$content ?>
</body>
</html>
