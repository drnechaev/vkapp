<?
function connect_db($mysql)
{
	mysql_connect($mysql['host'], $mysql['user'], $mysql['pass']);
	mysql_select_db($mysql['db']);
}

//http://www.ru-coding.com/
function get_random_unique_indexes($n, $rnd_from, $rnd_to)
{
	$func_tmp = array();

	if ($n > ($rnd_to - $rnd_from))
		$n = $rnd_to - $rnd_from +1;

	for ($i = 0; $i < $n; $i++)
	{
		$rnd = mt_rand($rnd_from, $rnd_to);
		while(in_array($rnd, $func_tmp))
			$rnd = mt_rand($rnd_from, $rnd_to);
		$func_tmp[] = $rnd;
	}

	return $func_tmp;
}

?>