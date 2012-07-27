<!DOCTYPE html>
<html>
<head>
	<title>Вставка модуля</title>
	<meta charset="utf-8">
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
</head>
<body>

<form onsubmit="ExampleDialog.insert();return false;" action="#">
	<p>Выберите модуль для вставки в aas.</p>
	<select id="someval" name="someval">
 
	 <?php
	 $b1=array();
	 $fl=" selected ";
 $a = @opendir('../../../../functions/modules');
 while ($b= readdir($a))
 {
 if(substr( $b,-4,4)=='.php' && substr( $b,0,1)!='#'  && substr( $b,0,1)!='-'  && substr( $b,0,6)!='class.')
 {
 $f=@fopen('../../../../functions/modules/'.$b,'r');
 $b=substr($b,0,-4);
 $c = fread ($f,255);
 $c=substr($c,0,strpos($c,"\n",1));
 $c=str_replace('<?php//','',$c);
  $c=str_replace('<?php //','',$c);
  $c=str_replace('?>','',$c);
  $b1[$b]=$c;
 

 fclose($f);
 }
 } 
  ////////////////////////////
  $a = @opendir('../../../../templates/modules');
 while ($b= readdir($a))
 {
 if(substr( $b,-4,4)=='.php'  && substr( $b,0,1)!='#'  && substr( $b,0,1)!='-' && substr( $b,0,6)!='class.')
 {
 $f=@fopen('../../../../templates/modules/'.$b,'r');
 $b=substr($b,0,-4);
 $c = fread ($f,255);
 $c=substr($c,0,strpos($c,"\n",1));
 $c=str_replace('<?php//','',$c);
 $c=str_replace('<?php //','',$c);
  $c=str_replace('?>','',$c);
  $b1[$b]=$c;
 

 fclose($f);
 }
 }
 foreach($b1 as $key =>$value)
 {
 print "<option value='$key' $fl >$value</option>"; $fl="";
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
