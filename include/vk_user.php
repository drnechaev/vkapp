<?php

require_once("vkapi.class.php");
require_once("myapi.class.php");

class vk_User {
	public $ID;
	private $is_user;
	private $user;

	//public $sqluserid;

	public function __construct(){

		//print_r($_SERVER);

		if(isset($_GET['sesid']))
		{
			session_id($_GET['sesid']);
		}

		session_start();
		$this->is_user=false;
		$config = &vk_Core::getConfig();

		//echo $config->secretKey;

		//print_r($_SESSION);

		if(!isset($_SESSION['user']))		
		{

			if( strpos(strtolower($_SERVER["HTTP_REFERER"]),"vkontakte.ru") !=false )
			{

				$auth_key = md5( $config->api_id.'_'.$_GET['viewer_id'].'_'.$config->secretKey );
	
				if($auth_key!=$_GET['auth_key'])
					die("INTERNAL ERROR!!!!");

				$_SESSION['is_user']=1;
			
				$VK = new vkapi($config->api_id,$config->secretKey);
				$resp = $VK->api('getProfiles', array('uids'=>$_GET['viewer_id']));

				$this->user->uid = $resp['response'][0]['uid'];
				$this->user->name = $resp['response'][0]['first_name'];
				$this->user->surname = $resp['response'][0]['last_name'];

				$this->user->sqluserid=("vk_id=".$this->user->uid);
				$this->user->sqluserlink="user_link='http://vkontakte.ru/id".$this->user->uid."'";
				$this->user->appType = "VK";

				$_SESSION['user']=$this->user;
			}
			else if( strpos(strtolower($_SERVER["HTTP_REFERER"]),"mail.ru") !=false )
			{
				//echo "MAIL";

				if(checkMy($config->my_secret)!=TRUE)
					die("INTERNAL ERROR!!!! SIG");

				$_SESSION['is_user']=1;

				$resp = mygetInfo($config->my_secret,$config->my_id);

				//print_r($resp);

				$this->user->uid = $resp[0]['uid'];
				$this->user->name = $resp[0]['first_name'];
				$this->user->surname = $resp[0]['last_name'];
				$this->user->link = $resp[0]['link'];

				$this->user->sqluserid=("my_id=".$this->user->uid);
				$this->user->sqluserlink = "user_link='".$this->user->link."'";
				$this->user->appType = "MY";

				$_SESSION['user']=$this->user;
			}
			else
				die("INTERNAL ERROR!!!");

			//$cart = &vk_Core::getCart();
		}
		else
			$this->user = $_SESSION['user'];
		

		//print_r($this->user);
		$this->is_user=true;
		
	}


	public function getAppType()
	{
		return $this->user->appType;
	}

	public function getSqlId()
	{
		return $this->user->sqluserid;
	}

	public function getUserLink()
	{
		return $this->user->sqluserlink;
	}

	public function getUserOrders()
	{
		
		$db = &vk_Core::getDB();
		$db->query("select id,total, os.orders_status_name as status, date as date
			from os_vkorders as vo
			left join os_orders_status as os on os.orders_status_id=vo.status where ".$this->user->sqluserid." order by date desc");

		$res = $db->results();

		$date ='';
		foreach($res as $k=>$r)
		{
			if($k!=0)
				$date .=',';

			$date .= '{"id":"'.$r->id.'","total":"'.$r->total.' руб","status":"'.$r->status.'","date":"'.$r->date.'"}';
			
		}
		
		return $date;
		
	}

	public function getUserOrder($id)
	{

		$db = &vk_Core::getDB();
		$db->query("select id,total, os.orders_status_name as status, date as date,
				vop.qty,p.products_model as model,p.products_image as img,pd.products_name as name,pov.products_options_values_name as size_w 
	 			from os_vkorders as vo
				left join os_vkorders_products as vop on vop.ordId=vo.id
				left join os_orders_status as os on os.orders_status_id=vo.status
				left join os_products as p on p.products_id = vop.prdid
				left join os_products_description as pd on pd.products_id=p.products_id
				left join os_products_options_values as pov on pov.products_options_values_id=vop.size
				where vo.id=".$id);
		

		$res = $db->results();

	

		$r = $res[0];
		$date ='{"id":"'.$r->id.'","total":"'.$r->total.' руб","status":"'.$r->status.'","date":"'.$r->date.'","prds":[';
		foreach($res as $k=>$r)
		{
			if($k!=0)
				$date .=',';

			$date .= '{"name":"'.$r->name.'","qty":"'.$r->qty.'","img":"'.$r->img.'","size":"'.$r->size_w.'"}';
			
		}
		
		return $date."]}";
	}

	public function is_user()
	{
		return $this->is_user;
	}

	public function getUser()
	{
		return $this->user;
	}
	
	public function userExit(){
		session_destroy();
		//$this->drawForm();
	}
}
?>
