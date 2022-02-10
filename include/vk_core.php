<?php



require_once("..".nwDS."config.php");


require_once('vk_config.php');
require_once('vk_db.php');
require_once('vk_tmpl.php');
require_once('vk_user.php');
require_once('vk_cart.php');

require_once('vkapi.class.php');


class vk_Core{

	private static $vkConfig;
	private static $vkDB;
	private static $vkUser;
	private static $vkCart;


	public function __construct()
	{
		$this->begin = microtime(true);
		vk_Core::$vkConfig = new vk_Config();
		vk_Core::$vkDB = new vk_DB(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE);
		if(vk_Core::$vkDB->connect()==0)
		{	
			vk_Core::$vkDB->get_error(true);
		}

		vk_Core::$vkUser = new vk_User();

		if(!vk_Core::$vkUser->is_user())
		{
			die("INTERNAL ERROR");
		}

		
		if(isset($_REQUEST["act"]))
			$this->getJSON();

	}


	public function __destruct()
	{
		if(defined("nw_DEBUG_CORE"))
		{
			$end = microtime(true);
			echo SESSION_TIME . ($end - $this->begin);
		}	
	}


	public function draw(){

		$template = new nw_Template();
		$template->drawTemplate();
	}



	public static function getDB(){
		return vk_Core::$vkDB;
	}

	public static function getUser(){
		
		return vk_Core::$vkUser;
	}

	public static function getConfig(){
		return vk_Core::$vkConfig;
	}

	public static function getCart(){

		if(!is_object(vk_Core::$vkCart))
		{
			//$info = vk_Core::$vkUser->getUser();
			vk_Core::$vkCart = new vk_Cart(vk_Core::$vkUser);
		}
		return vk_Core::$vkCart;
	}


	private function getJSON()
	{
		require_once('vk_JSON.php');
		$json = new vk_JSON();

		$json->getData();
		//echo '[{"funct":"getCats"},{"id":"1","name":"cat"}]';
		die();

	}

	
}


?>
