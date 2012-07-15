<?php
error_reporting(0);
 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-Type: text/html; charset=UTF-8");
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Вставка таблицы из Excel</title>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
</head>
<body>

<form enctype="multipart/form-data" method="post" <?php /* onsubmit="importexcel.insert();return false;"*/ ?> >
 
	
	<?php /*<select id="someval" name="someval">
 
	 <?php
	 $b1=array();
	 $fl=" selected ";
 $a = @opendir('../../../../scripts/modules');
 while ($b= readdir($a))
 {
 if(substr( $b,-4,4)=='.php')
 {
 $f=@fopen('../../../../scripts/modules/'.$b,'r');
 $b=substr($b,0,-4);
 $c = fread ($f,255);
 $c=substr($c,0,strpos($c,"\n",1));
 $c=str_replace('<?php//','',$c);
  $c=str_replace('?>','',$c);
  $b1[$b]=$c;
 

 fclose($f);
 }
 } 
  ////////////////////////////
  $a = @opendir('../../../../templates/modules');
 while ($b= readdir($a))
 {
 if(substr( $b,-4,4)=='.php')
 {
 $f=@fopen('../../../../templates/modules/'.$b,'r');
 $b=substr($b,0,-4);
 $c = fread ($f,255);
 $c=substr($c,0,strpos($c,"\n",1));
 $c=str_replace('<?php//','',$c);
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

 */
 //var_dump($_FILES);
 
 function Encode ( $str, $type )
{
return  $str;
  static $conv='';
  if (!is_array ( $conv ))
  {    
	  $conv=array ();
	  for ( $x=128; $x <=143; $x++ )
	  {
		$conv['utf'][]=chr(209).chr($x);
		$conv['win'][]=chr($x+112);
	  }
	  for ( $x=144; $x <=191; $x++ )
	  {
			  $conv['utf'][]=chr(208).chr($x);
			  $conv['win'][]=chr($x+48);
	  }
	  $conv['utf'][]=chr(208).chr(129);
	  $conv['win'][]=chr(168);
		$conv['utf'][]="Лљ";
	  $conv['win'][]="&deg;";
	  $conv['utf'][]=chr(209).chr(145);
	  $conv['win'][]=chr(184);
	}
	if ( $type=='w' )
		return str_replace ( $conv['utf'], $conv['win'], $str );
	elseif ( $type=='u' )
		return str_replace ( $conv['win'], $conv['utf'], $str );
	else
	  return $str;
 } 
 
 if(isset($_POST["act"])){
 
 require_once 'excel_reader2.php';
$data = new Spreadsheet_Excel_Reader($_FILES['fname']["tmp_name"]);

if($_POST["clearall"]=='clearall')
{
print "<script>importexcelDialog.clear()</script>";
}
 
print "<script>importexcelDialog.insert('<p>".str_replace("\'","\\\'", str_replace("\n","",Encode($data->dump(false,false,0,'price'),'w')))."</p>')</script>";
 
 }
 ?>
 
 
 Загрузите файл для вставки в код:
 <input type="file" name="fname">
 <input type="hidden" name="act" value="send"><br>
 <input type="checkbox" name="clearall" checked=checked value="clearall" id="clearall"><label for="clearall">Очистить содержимое перед вставкой</label>
 </br>
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="submit" id="insert" name="insert" value="Вставить" onclick="" />
		</div>

		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="Отмена" onclick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>

</body>
</html>
