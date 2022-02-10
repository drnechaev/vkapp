<?php 



/*
$sig = $_GET['sig'];
 unset($_GET['sig']);
$md5_param = sign_server_server($_GET,$my_secret);
echo "sig=".$sig . "<br/>";
echo "sig_my=".$md5_param."<br/>";
*/

function sign_server_server(array $request_params, $secret_key) {
	ksort($request_params);
	$params = '';
	foreach ($request_params as $key => $value) {
		$params .= "$key=$value";
	}

	//echo "<br/>".$params."<br/>";

	return md5($params . $secret_key);
}


function checkMy($my_secret)
{
	$sig = $_GET['sig'];
	unset($_GET['sig']);
	$md5_param = sign_server_server($_GET,$my_secret);

	//echo $md5_param ."<br/>";
	//echo $sig;

	if($md5_param!=$sig)
		return FALSE;

	return TRUE;
}


function get_url($query) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $query);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);// $res;
}


function mygetInfo($my_secret,$my_id)
{
$params = array("app_id"=>$my_id,"session_key"=>$_GET['session_key'],'method'=>"users.getInfo","secure"=>1);
$url = "http://www.appsmail.ru/platform/api?method=users.getInfo&app_id=".$my_id."&session_key=".$_GET['session_key']."&secure=1&sig=".sign_server_server($params,$my_secret);//."&uids=".$_GET['vid'];

return get_url($url);
}


 ?>
