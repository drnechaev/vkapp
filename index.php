<?php
header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); 

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
define('nwDS', DIRECTORY_SEPARATOR); 

require_once("include".nwDS."vk_core.php");

$vkCore = new vk_core();

$user = &vk_Core::getUser();

?>
﻿<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<link href="/app/css/style.css" rel="stylesheet" type="text/css" />

<script src="http://vkontakte.ru/js/api/xd_connection.js?2" type="text/javascript"></script>
<?php //<script type='text/javascript' src='/app/js/jquery-1.4.2.js'></script> ?>
<script type='text/javascript' src='/app/js/jquery.pack.js'></script>
<script type='text/javascript' src='/app/js/jquery.timers.js'></script>
<script type='text/javascript' src='/app/js/jquery.aslideshow.pack.js'></script>
<?php 
//var sesname = '".session_name()."';
echo "<script type='text/javascript'>
	var sesid = '".session_id()."';
	</script>";

	echo "<script type='text/javascript' src='/app/js/bjs.js'></script>";
	//echo "<script type='text/javascript' src='/app/js/pvks.js'></script>";
?>

<script type='text/javascript'>
jQuery(document).ready(function(){
<?php 
	$appType = $user->getAppType();

	if($appType=='VK')
		echo 'VK.init(function() {initApp();});';
	else
		echo "initApp();";
?>

});
</script>


</head>

<?php 

require_once("include".nwDS."vk_JSON.php");

$json = new vk_JSON(1);

//$v->id=0;
//$v = $json->getData("DATA",$v);

$json->type='DATA';
$v = $json->getCategories(0);

$menu ="<ul>";
foreach($v as $c)
{
	$menu .= "<li><a href='/categories?cat=".$c->categories_id."'>".$c->categories_name."</a>";

	$man = $json->getManufacturers($c->categories_id);

	if(isset($man) && $man!='' && count($man)>0)
	{
		$menu.="<ul>";
		foreach($man as $m)
			$menu.="<li><a href='/categories?cat=".$c->categories_id."&man=".$m->manufacturers_id."' >".$m->manufacturers_name."</a></li>";

		$menu.="<li class='clear'> </li></ul>";
	}
	$menu .="</li>";
}

$menu .="</ul>";


$cart = &vk_Core::getCart();



if($cart->getTotalQty()!=0)
{
	$crt = "В корзине ".$cart->getTotalQty()."<br/>На сумму ".$cart->getTotal()."<br/><span style='float:right'><a href='/cart'>Смотреть</a></span>";
	$crtDsp='block';
}
else
{
	$crt = 'Корзина пуста';
	$crtDsp='none';
}

include ("template/index.php"); ?>

</html>

