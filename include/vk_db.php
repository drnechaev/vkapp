<?php

class vk_DB     {
private $host;
private $database;
private $name;
private $pass;
private $link;
private $error_msg;

public $query;
public $lRes;

public $numQuerys;
public $Querys;

	// Конструктор
	public function __construct ($h, $n, $p, $b) {
		$this->host=$h;
		$this->name=$n;
		$this->pass=$p;
		$this->database=$b;
		$this->numQuerys = 0;
	}

	public function __destruct()
	{
		$this->destroy();
	}

	// Функция подключения к БД
	public function connect() {

		if (!($this->link=mysql_connect($this->host,$this->name,$this->pass))) {
			$this->error_msg="Error. Can't connect";
			return FALSE;
		}

		if (!mysql_select_db($this->database)) {
			$this->error_msg="Can't select database";
			return FALSE;
		}
		mysql_query('SET character_set_database = utf8');
		mysql_query('SET NAMES utf8');

		return TRUE;

	}

	public function destroy()
	{
		mysql_close($this->link);
	}

	public function get_error($err=0)
	{
		echo $this->error_msg."<br/> Ошибка:" . mysql_errno($this->link) ."<br/>". mysql_error($this->link);
		echo "[".$this->query."]";

		if($err)
			die(1);
	}

	// Функция выполнения запроса
	//  знак #будет менять на префикс к базеданных по умолчанию "nw_"
	public function query($q="") {

		if($q!="")
			$this->query = $q;

		//$this->query = str_replace("#",nwDB,$this->query);

		$this->lRes=mysql_query($this->query)
		or $this->error_msg = "ERROR";
		return $this->lRes;

	}

	// возвращает результаты в виде массива объектов
	function results()
	{
		$result = array();

		if(!$this->lRes)
		{
		     $this->error_msg = DB_QUERY_ERROR;
	     	     return 0;
		}

		while($row = mysql_fetch_object($this->lRes))
		{
			array_push($result, $row);
		}

		return $result;
	}


	function result()
	{
		$result = array();

		if(!$this->lRes)
		{
			$this->error_msg = "DSSD";
			return 0;
		}

		$row = mysql_fetch_object($this->lRes);
		return $row;
	}

	//Возвращает последний добавленный id
	function insert_id()
	{
		return mysql_insert_id($this->link);
	}

	//Кол-во возращенных строк
	function num_rows()
	{
		return mysql_num_rows($this->lRes);
	}


}
?>
