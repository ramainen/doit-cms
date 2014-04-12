<?php
	chdir($_SERVER['DOCUMENT_ROOT']);
	include_once ($_SERVER['DOCUMENT_ROOT'].'/config.php');
	include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/cms.php');
	$doit = d();
?><!DOCTYPE html>
<html>
<head>
	<title>Вставка модуля</title>
	<meta charset="utf-8">
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
</head>
<body>

<form onsubmit="ExampleDialog.insert();return false;" action="#">
	<p>Выберите модуль для вставки на страницу.</p>
	<?php
	if(!is_array(d()->plugins)){
		d()->plugins=array(d()->plugins);
	}
	 
	?>
	<select id="someval" name="someval">
  <?php
 foreach(d()->plugins as $key =>$value)
 {
 $key=str_replace('#','@',$key);
 print "<option value='$key'   >$value</option>";  
 }
 ?>
	</select>
 <br>

 
 </br>
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="insert" name="insert" value="Вставить" onclick="mymodulesDialog.insert();" />
		</div>

		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="Отмена" onclick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>

</body>
</html>
