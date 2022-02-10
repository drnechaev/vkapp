<?php





class vk_Cart{

	//private $cart;
	private $vk_id;
	private $sqluserid;
	private $userlink;


	public function __construct($usr)
	{

		//FIXME тут нужна проверка что за юзер
		//$this->vk_id = $user_id;
		$this->sqluserid= $usr->getSqlId();
		$this->userlink = $usr->getUserLink();
		
		if(!isset($_SESSION['cart']))
		{

			$cart->total = 0;
			//$this->cart->totalQty = 0;
			$cart->totalUnder = 0;
			$cart->totalShirt = 0;
			$cart->prd = array();

			// тут проверяем было ли что в корзине на момент выхода

			$db =  &vk_Core::getDB();

			//$db->query("select * from os_vkcart where ".$this->sqluserid);
			/*
			$db->query("select vc.prdid, vc.qty,vc.size,vc.ptype,p.products_image as img,pd.products_name as name,pov.products_options_values_name as size_w from os_vkcart as vc
				left join os_products as p on p.products_id = vc.prdid
				left join os_products_description as pd on pd.products_id=p.products_id
				left join os_products_options_values as pov on pov.products_options_values_id=vc.size
				where vc.".$this->sqluserid);
			*/

			$db->query("select vc.prdid, vc.qty,vc.size,vc.ptype,p.products_image as img,p.products_model as name,pov.products_options_values_name as size_w from os_vkcart as vc
				left join os_products as p on p.products_id = vc.prdid
				left join os_products_options_values as pov on pov.products_options_values_id=vc.size
				where vc.".$this->sqluserid);
			while($p = $db->result())
			{
				if($p->ptype==1)
					$cart->totalUnder += $p->qty;
				else
					$cart->totalShirt += $p->qty;

				$cart->prd[$p->prdid."_".$p->size]=$p;
			}

			$db->query("select configuration_key as ckey, configuration_value as val from os_configuration where configuration_id>597");
			$prices = $db->results();
	
			if($cart->totalUnder >= 5)
				$und = $cart->totalUnder*$prices[1]->val;
			else
				$und = $cart->totalUnder*$prices[0]->val;
		

			$cart->total = $cart->totalShirt*$prices[2]->val + $und;
			$cart->prices = $prices;

			$_SESSION['cart'] = $cart;
		}
		//else
		//	$this->cart = $_SESSION['cart'];





	}

	public function ProcessOrder()
	{
		$db =  &vk_Core::getDB();


		$total = $_SESSION['cart']->total;

		if($_POST['pt']=='flat')
			$total += 150;
		else
			$total += 240;

		$sql = "insert into os_vkorders set ".$this->sqluserid.",".$this->userlink.", post='".mysql_real_escape_string($_POST['pt'])."',
				name ='".mysql_real_escape_string($_POST['fname'])."',sname='".mysql_real_escape_string($_POST['sname'])."',
				lname ='".mysql_real_escape_string($_POST['lname'])."',addres ='".mysql_real_escape_string($_POST['addres'])."',
				zip ='".mysql_real_escape_string($_POST['index'])."', city ='".mysql_real_escape_string($_POST['city'])."',
				regio ='".mysql_real_escape_string($_POST['regio'])."',comments = '".mysql_real_escape_string($_POST['comm'])."',
				total = ".$total." , phone ='".mysql_real_escape_string($_POST['phone'])."'";


		//echo $sql;
		//die();

		$db->query($sql);
		$id = $db->insert_id();


		//echo $id;

		foreach($_SESSION['cart']->prd as $k=>$prd)
		{

		//	print_r($prd);

			if($prd->ptype==1)
			{
				$fprice = ($_SESSION['cart']->totalUnder>5?$_SESSION['cart']->prices[1]->val:$_SESSION['cart']->prices[0]->val)*$prd->qty;
			}
			else
				$fprice = $_SESSION['cart']->prices[2]->val*$prd->qty;


			$sql = 	"insert into os_vkorders_products set ordId=".$id.",prdid=".$prd->prdid.",size=".$prd->size.",qty=".$prd->qty.",
				model='".$prd->name."',size_w='".$prd->size_w."',ptype=".$prd->ptype.",final_price=".$fprice;
			$db->query($sql);


			$db->query("update os_products_attributes set attributes_stock=attributes_stock-".$prd->qty." from  where products_id = '".$prd->prdid."' and options_id = '1' and options_values_id='".$prd->size."'");

			$db->query("select sum(attributes_stock) as ss from os_products_attributes where products_id = '".$prd->prdid."' and options_id = '1'");

			$sum = $db->result();
			if($sum->ss<=0)
				$db->query("update os_products set products_status=0 where products_id = '".$prd->prdid."'");

			$db->query("delete from os_vkcart where ".$this->sqluserid." and prdid=".$prd->prdid." and size=".$prd->size);
			unset($_SESSION['cart']->prd[$k]);
		}

		$_SESSION['cart']->total = 0;
		$_SESSION['cart']->totalUnder = 0;
		$_SESSION['cart']->totalShirt = 0;


	}

	public function __destruct()
	{
	}


	public function getTotal()
	{
		return $_SESSION['cart']->total . " руб";
		//return "111";
	}

	public function plainTotal()
	{
		return $_SESSION['cart']->total;
	}

	public function getTotalQty()
	{

		$all = $_SESSION['cart']->totalUnder + $_SESSION['cart']->totalShirt;

		if($all==0)
			return 0;

		$ptext='товаров';
                $p1=$all%10;
                $p2=$all%100;
                if($p1==1 && !($p2>=11 && $p2<=19))
			$ptext='товар';
		elseif($p1>=2 && $p1<=4 && !($p2>=11 && $p2<=19))
			$ptext='товара';

		return $all . " ".$ptext;
		//return "!!!";
	}

	public function Update($prd,$qty)
	{

		if($this->in_cart($prd))
		{
			$db =  &vk_Core::getDB();
			$d = explode("_",$prd);
			$db->query("select attributes_stock as stock from os_products_attributes where products_id = '".$d[0]."' and options_id = '1' and options_values_id='".$d[1]."'");
			$stock = $db->result();

			if($stock->stock <= ($_SESSION['cart']->prd[$prd]->qty + $qty))
				return 0;

			$qtys = $_SESSION['cart']->prd[$prd]->qty + $qty;

			if($qtys<1)
				return 0;



			$_SESSION['cart']->prd[$prd]->qty = $qtys;

			//print_r($d);

			

			$db->query("update os_vkcart set qty=".$_SESSION['cart']->prd[$prd]->qty." where prdid=".$d[0]." and size=".$d[1]." and ".$this->sqluserid);
			$ptype = $_SESSION['cart']->prd[$prd]->ptype;

			if($ptype==1)
			{
				$_SESSION['cart']->totalUnder += $qty;
			}
			else
			{
				$_SESSION['cart']->totalShirt += $qty;
			}



			if($_SESSION['cart']->totalUnder >= 5)
				$und = $_SESSION['cart']->totalUnder*$_SESSION['cart']->prices[1]->val;
			else
				$und = $_SESSION['cart']->totalUnder*$_SESSION['cart']->prices[0]->val;
		
			$_SESSION['cart']->total = $_SESSION['cart']->totalShirt*$_SESSION['cart']->prices[2]->val + $und;

			return $qtys;

		}

		return 0;

	}

	public function AddToCart($prdid,$qty,$size)
	{

		$db =  &vk_Core::getDB();
		

		//$db->query("select configuration_key as ckey, configuration_value as val from os_configuration where configuration_id>597");
		//$prices = $db->results();
	
		
		$db->query("select attributes_stock as stock from os_products_attributes where products_id = '".$prdid."' and options_id = '1' and options_values_id='".$size."'");
		$stock = $db->result();

		$ptr = $prdid.'_'.$size;

		if($this->in_cart($ptr))
		{
			if($stock->stock <= ($_SESSION['cart']->prd[$ptr]->qty + $qty))
			{
				//$_SESSION['cart']->prd[$ptr]->qty = $stock->stock;

				$qty = $stock->stock - $_SESSION['cart']->prd[$ptr]->qty;
			}

			$_SESSION['cart']->prd[$ptr]->qty += $qty;

			$db->query("update os_vkcart set qty=".$_SESSION['cart']->prd[$ptr]->qty." where prdid=".$prdid." and size=".$size." and ".$this->sqluserid);
			$ptype = $_SESSION['cart']->prd[$ptr]->ptype;
		}
		else
		{

			if($stock->stock < $qty)
				$qty = $stock->stock;

			/*заменил pd.products_name as name на 
			$db->query("SELECT pd.products_name as name,p.ptype, pov.products_options_values_name as size, p.products_image as img FROM os_products as p
				left join os_products_description as pd on pd.products_id=p.products_id
				left join os_products_options_values as pov on pov.products_options_values_id=".$size."
				where p.products_id =".$prdid);
			*/
			$db->query("SELECT p.products_model as name,p.ptype, pov.products_options_values_name as size, p.products_image as img FROM os_products as p
				left join os_products_options_values as pov on pov.products_options_values_id=".$size."
				where p.products_id =".$prdid);
			$pd = $db->result();
			$ptype = $pd->ptype;

		//	$product->prdid = $prdid;
			//$_SESSION['cart']->prd[] = array($ptr);
			$product->prdid = $prdid;
			$product->size = $size;
			$product->size_w = $pd->size;
			$product->name = $pd->name;
			$product->qty = $qty;
			$product->img = $pd->img;
			$product->ptype = $pd->ptype;
			$_SESSION['cart']->prd[$ptr] = $product;

			$db->query("insert into os_vkcart set ".$this->sqluserid.",prdid='".$prdid."',qty='".$qty."',size='".$size."',ptype='".$ptype."'");
		}


		if($ptype==1)
		{
			$_SESSION['cart']->totalUnder += $qty;
		}
		else
		{
			$_SESSION['cart']->totalShirt += $qty;
		}


		//print_r($prices);


		//print_r($_SESSION['cart']);
				
		//echo $prices[1]["val"];
		//print_r($prices);

		
		//print_r($_SESSION['cart']);
		if($_SESSION['cart']->totalUnder >= 5)
			$und = $_SESSION['cart']->totalUnder*$_SESSION['cart']->prices[1]->val;
		else
			$und = $_SESSION['cart']->totalUnder*$_SESSION['cart']->prices[0]->val;
		

		$_SESSION['cart']->total = $_SESSION['cart']->totalShirt*$_SESSION['cart']->prices[2]->val + $und;
		
		//$_SESSION = $this->cart;

	}


	public function in_cart($prdid)
	{
		if(isset($_SESSION['cart']->prd[$prdid]))
			return TRUE;
		else
			return FALSE;
	}

	public function DeleteFrom($prdid,$size)
	{
		$prd =  $prdid.'_'.$size;

		if(!isset($_SESSION['cart']->prd[$prd]))
			return;

		$prds = $_SESSION['cart']->prd[$prd];
		unset($_SESSION['cart']->prd[$prd]);

		$db =  &vk_Core::getDB();

		$db->query("delete from os_vkcart where ".$this->sqluserid." and prdid=".$prdid." and size=".$size);
		
		if($prds->ptype==1)
		{
			$_SESSION['cart']->totalUnder -= $prds->qty;
		}
		else
		{
			$_SESSION['cart']->totalShirt -= $prds->qty;
		}


		if($_SESSION['cart']->totalUnder >= 5)
			$und = $_SESSION['cart']->totalUnder*$_SESSION['cart']->prices[1]->val;
		else
			$und = $_SESSION['cart']->totalUnder*$_SESSION['cart']->prices[0]->val;
		

		$_SESSION['cart']->total = $_SESSION['cart']->totalShirt*$_SESSION['cart']->prices[2]->val + $und;

		
	}

	public function getCart()
	{
		return $_SESSION['cart'];
	}
	
}


?>
