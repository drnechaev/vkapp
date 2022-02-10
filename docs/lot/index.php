<?
/**
by Dostelon aka Rutrum
icq: 577366
**/

header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); 
session_start();

$timerok = microtime();

include ("config.php");
include ("func.php"  );
include ("vkapi.php" );

$api_secret = "lXjncEkLacrxBSJASDdC";
$api_id     = "2836854";
$api        = new VKapi($api_secret, $api_id);



if (!$_SESSION['id'])
{
	$auth_key   = $_GET['auth_key'];
	$viewer_id  = $_GET['viewer_id'];
	if(!md5($api_id."_".$viewer_id."_".$api_secret)==$auth_key) exit('key incorrect');
	$_SESSION['id'] = $viewer_id;
	header ('Location: index.php');
	exit();
}

connect_db($mysql);

$now  = mysql_fetch_assoc(mysql_query("SELECT * FROM stages ORDER BY id DESC LIMIT 1"));

if ($_GET['action'] == 'buy')
{
	$status = $api->withdrawVotes($_SESSION['id'], 100);
	if ($status['response']['transferred'] == 100)
	{
		$all  = mysql_fetch_assoc(mysql_query("SELECT count(*) AS count FROM tickets WHERE sid = '$now[id]'"));
		$all['count']++;
		mysql_query("INSERT INTO tickets (sid, uid, nid, timer) VALUES ('$now[id]', '$_SESSION[id]', '$all[count]', '".time()."')");
		header ('Location: index.php');
		exit();
	}
}

$last = mysql_fetch_assoc(mysql_query("SELECT * FROM stages ORDER BY id DESC LIMIT 1,2"));
$info = mysql_query("SELECT * FROM tickets WHERE sid = '$now[id]' AND uid = $_SESSION[id] ORDER BY id DESC");

	while ($info1 = mysql_fetch_assoc($info))
	{
		$tickets[]=$info1['nid'];
	}
	$tickets = @implode (", ",$tickets);



$check  = mysql_fetch_assoc(mysql_query("SELECT count(*) AS count FROM tickets WHERE sid = '$now[id]'"));
if ($check['count']>=100)
{
	$rnd    = get_random_unique_indexes(37, 1, 100);
	$top    = implode(",", array_slice($rnd, 0, 2  ));
	$medium = implode(",", array_slice($rnd, 2, 15 ));
	$down   = implode(",", array_slice($rnd, 17, 38));
	$md5    = base_convert(rand(1000000,999999999), 10, 36)."_".$top."_".$medium."_".$down."_".base_convert(rand(1000000,999999999), 10, 36);

	mysql_query("INSERT INTO stages (top, medium, down, md5) VALUES ('$top', '$medium', '$down', '$md5')");

	$top    = mysql_query("SELECT * FROM tickets WHERE sid = '$now[id]' AND nid IN ({$now[top]})");
	$medium = mysql_query("SELECT * FROM tickets WHERE sid = '$now[id]' AND nid IN ({$now[medium]})");
	$down   = mysql_query("SELECT * FROM tickets WHERE sid = '$now[id]' AND nid IN ({$now[down]})");

	$api->sendNotification($now['top'],    'WIN:500');
	$api->sendNotification($now['medium'], 'WIN:300');
	$api->sendNotification($now['down'],   'WIN:150');

	while ($top1 = mysql_fetch_assoc($top))
	{
		$api->addVotes($top1['uid'], 500);
	}

	while ($medium1 = mysql_fetch_assoc($medium))
	{
		$api->addVotes($medium1['uid'], 300);
	}

	while ($down1 = mysql_fetch_assoc($down))
	{
		$api->addVotes($down1['uid'], 150);
	}
	header ('Location: index.php');
	exit();
	
}

$money = $api->getBalance  ($_SESSION['id']);
$name  = $api->getProfiles ($_SESSION['id']);
$first_name = iconv('utf-8', 'windows-1251',  $name['response']['user']['first_name']);
$last_name  = iconv('utf-8', 'windows-1251',  $name['response']['user']['last_name'] );
$money      = $money['response']['balance'];

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="vkontakte">
<head>
<meta http-equiv="content-type" content="text/html; charset=windows-1251" />
</head>

<link rel="stylesheet" href="index.css" type="text/css" />
<body>
	<div class="body">
		<div class="notice">
		<h1>Лотерея "100 копеек"</h1>
		</div>
		<div class="header">
		Общая информация
		</div>

		<div class="notice">
			<table>
				<tr>
					<td align=left>Имя: <input readOnly size=34 type=text value='<?=$first_name;?> <?=$last_name;?>'></td>
					<td align=right>Копеек: <input readOnly size=4 type=text value='<?=$money;?>'></td>
				</tr>
				<tr>
					<td align=left>md5: <input readOnly size=34 type=text value='<?=md5($now['md5']);?>'></td>
					<td align=right>Джек-пот: <input readOnly size=4 type=text value='<?=$last['id']*100;?>'></td>
				</tr>
			</table>
		</div>

		<div class="header">
		Информация о текущем розыгрыше
		</div>

		<div class="notice">
		<table>
				<tr>
					<td align=left>Продано билетов: <input readOnly size=4 type=text value='<?=$check['count'];?>'></td>
					<td align=right>Всего билетов: <input readOnly size=4 type=text value='100'></td>
				</tr>
		</table>
			<textarea rows=5 readOnly>Ваши билеты: <?=$tickets;?></textarea>
			<a class="button" href="index.php?action=buy">Купить 1 билет за 100* копеек</a>
			<font size=1>*100 копеек = 1 голос</font>
		</div>

		<div class="header">
		Информация о прошлом розыгрыше
		</div>

		<div class="notice">
		<table>
				<tr>
					<td align=left>md5: <input readOnly size=34 type=text value='<?=md5($last['md5']);?>'></td>
					<td align=right>Строка: <input readOnly size=34 type=text onClick="this.select()" value='<?=$last['md5'];?>'></td>
				</tr>
		</table>
		<p>
		<table>
				<tr>
					<td align=left><b>500 копеек:</b><p></td>
					<td align=left><?=$last['top'];?><p></td>
				</tr>
				<tr>
					<td align=left><b>300 копеек:</b><p></td>
					<td align=left><?=$last['medium'];?><p></td>
				</tr>
				<tr>
					<td align=left><b>150 копеек:</b><p></td>
					<td align=left><?=$last['down'];?><p></td>
				</tr>
			</table>
			
		</div>


	</div>
</body>
<p><h1>
<?

print (microtime()- $timerok);

?>