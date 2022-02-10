<?php
class vk_JSON {

	/*
	1 - список категорий
		cat подкатегориии
	2 - все производители
		cat = производители данной категории	
	3 - товары
		cat = в данной категории
		man = данного производителя
		page = страница
	4 - статья
		url = адресс
	5 - получить товар
		prdid - id товара
	6 - корзина
		add - добавить (size,qty)
		delete удалить (id,size)
	7 - оформить заказ
	8 - процесс оформления
	9 - мои заказы
		
	*/
	private $items;

	/*
	JSON - возвращает в виде JSON
	DATA - в виде php массива
	*/
	public $type;
	private $vars;

	public function __construct($getItems=0){
		$this->db = vk_Core::getDB();

		$this->items=$getItems;

		if($this->items==0)
		{


			if(!isset($_REQUEST['url']))
				return;
		
			preg_match('/^([^?]+)(\?.*?)?(#.*)?$/', $_REQUEST['url'], $matches);
			$gp = (isset($matches[2])) ? str_replace("?","",$matches[2]) : '';


			$this->vars['url']=str_replace("/","",$matches[1]);

			if($gp!='')
			{
				$params = explode("&",$gp);

				foreach($params as $pp)
				{
					$p = explode("=",$pp);
					$this->vars[$p[0]] = $p[1];
				}

			//	print_r($parametrs);
			}

			if($this->vars['url']=='categories')
			{
				$this->items=3;
			}
			elseif($this->vars['url']=='catalog')
				$this->items=1;
			elseif($this->vars['url']=='product')
			{

				$this->items=5;
			}
			elseif($this->vars['url']=='cart')
				$this->items=6;
			elseif($this->vars['url']=='order')
				$this->items=7;
			elseif($this->vars['url']=='process')
				$this->items = 8;
			elseif($this->vars['url']=='myorders')
				$this->items=9;
			else
			{
				$this->items = 4;
				//$this->vars = array("url"=>str_replace("/","",$matches[1]));
			}
			
			
		}
		//echo $gp;
		
		//die();

	}	


	public function getData($type='JSON',$vars=NULL)
	{
		if($this->items==0)
			return;

		if($vars!=NULL)
			$this->vars=$vars;

		$this->type=$type;

		switch($this->items)
		{
			case 1:
			{
				
				$this->data=$this->getCategories(isset($this->vars['cat'])?$this->vars['cat']:0);
				break;
			}
			case 2:
				$this->data=$this->getManufacturers(isset($this->vars['cat'])?$this->vars['cat']:0);
				break;
			case 3:
				
				$this->data=$this->getProducts(isset($this->vars['cat'])?$this->vars['cat']:0,isset($this->vars['man'])?$this->vars['man']:0,isset($this->vars['page'])?$this->vars['page']:1);
				break;
			case 4:
				$this->data = $this->getArticle($this->vars['url']);
				break;
			case 5:

				if(isset($this->vars['prdid']))
					$this->data = $this->getProduct($this->vars['prdid']);
				break;
			case 6:

				if(isset($this->vars['add']))
					$this->data = $this->AddToCart($this->vars['add'],isset($this->vars['qty'])?$this->vars['qty']:1,isset($this->vars['size'])?$this->vars['size']:0);
				elseif(isset($this->vars['del']))	
					$this->data = $this->DeleteFrom($this->vars['del'],$this->vars['size']);
				elseif(isset($this->vars['prd']))
					$this->data = $this->UpdateCartPrd($this->vars['prd'],$this->vars['qty']);
				else
					$this->data = $this->getCart();

				break;
			case 7:
				//$this->data = $this->getCatalog();
				$this->data = $this->getOrder();
				break;
			case 8:
				$this->data = $this->processOrder();
				break;
			case 9:
				$this->data = $this->myOrders(isset($this->vars['oid'])?$this->vars['oid']:0);
				break;
			default:
				return;
		}

		if($this->type=='JSON')
			echo $this->data;
		return $this->data;
	}


	public function myOrders($id=0)
	{

		$user = vk_Core::getUser();
		if($id==0)
		{
			return "myOrders([".$user->getUserOrders()."]);";
		}
		else return "myOrder([[{\"name\":\"Мои заказы\",\"url\":\"/myorders\"},{\"name\":\"Заказ №".$id."\"}],".$user->getUserOrder($id)."]);";

	}


	public function getOrder()
	{
		$cart = &vk_Core::getCart();
		if($cart->getTotalQty()!=0)
		{
			$user = &vk_Core::getUser();
			$user = $user->getUser();

		$regio = "<option value=''>Выберите Регион</option><option value='Агинский Бурятский АО'>Агинский Бурятский АО</option><option value='Адыгея'>Адыгея</option><option value='Алтайский край'>Алтайский край</option><option value='Амурская область'>Амурская область</option><option value='Архангельская область'>Архангельская область</option><option value='Астраханская область'>Астраханская область</option><option value='Башкирия'>Башкирия</option><option value='Белгородская область'>Белгородская область</option><option value='Брянская область'>Брянская область</option><option value='Бурятия'>Бурятия</option><option value='Владимирская область'>Владимирская область</option><option value='Волгоградская область'>Волгоградская область</option><option value='Вологодская область'>Вологодская область</option><option value='Воронежская область'>Воронежская область</option><option value='Горный Алтай'>Горный Алтай</option><option value='Дагестан'>Дагестан</option><option value='Еврейская автономная область'>Еврейская автономная область</option><option value='Ивановская область'>Ивановская область</option><option value='Ингушетия'>Ингушетия</option><option value='Иркутская область'>Иркутская область</option><option value='Кабардино-Балкария'>Кабардино-Балкария</option><option value='Калининградская область'>Калининградская область</option><option value='Калмыкия'>Калмыкия</option><option value='Калужская область'>Калужская область</option><option value='Камчатская область'>Камчатская область</option><option value='Карачаево-Черкесия'>Карачаево-Черкесия</option><option value='Карелия'>Карелия</option><option value='Кемеровская область'>Кемеровская область</option><option value='Кировская область'>Кировская область</option><option value='Коми'>Коми</option><option value='Коми-Пермяцкий АО'>Коми-Пермяцкий АО</option><option value='Корякский АО'>Корякский АО</option><option value='Костромская область'>Костромская область</option><option value='Краснодарский край'>Краснодарский край</option><option value='Красноярский край'>Красноярский край</option><option value='Курганская область'>Курганская область</option><option value='Курская область'>Курская область</option><option value='Ленинградская область'>Ленинградская область</option><option value='Липецкая область'>Липецкая область</option><option value='Магаданская область'>Магаданская область</option><option value='Марийская Республика'>Марийская Республика</option><option value='Мордовская Республика'>Мордовская Республика</option><option value='Москва'>Москва</option><option value='Московская область'>Московская область</option><option value='Мурманская область'>Мурманская область</option><option value='Ненецкий АО'>Ненецкий АО</option><option value='Нижегородская область'>Нижегородская область</option><option value='Новгородская область'>Новгородская область</option><option value='Новосибирская область'>Новосибирская область</option><option value='Омская область'>Омская область</option><option value='Оренбургская область'>Оренбургская область</option><option value='Орловская область'>Орловская область</option><option value='Пензенская область'>Пензенская область</option><option value='Пермская область'>Пермская область</option><option value='Приморский край'>Приморский край</option><option value='Псковская область'>Псковская область</option><option value='Ростовская область'>Ростовская область</option><option value='Рязанская область'>Рязанская область</option><option value='Самарская область'>Самарская область</option><option value='Санкт-Петербург'>Санкт-Петербург</option><option value='Саратовская область'>Саратовская область</option><option value='Сахалинская область'>Сахалинская область</option><option value='Свердловская область'>Свердловская область</option><option value='Северная Осетия'>Северная Осетия</option><option value='Смоленская область'>Смоленская область</option><option value='Ставропольский край'>Ставропольский край</option><option value='Таймырский АО'>Таймырский АО</option><option value='Тамбовская область'>Тамбовская область</option><option value='Татарстан'>Татарстан</option><option value='Тверская область'>Тверская область</option><option value='Томская область'>Томская область</option><option value='Тува'>Тува</option><option value='Тульская область'>Тульская область</option><option value='Тюменская область'>Тюменская область</option><option value='Удмуртия'>Удмуртия</option><option value='Ульяновская область'>Ульяновская область</option><option value='Усть-Ордынский Бурятский АО'>Усть-Ордынский Бурятский АО</option><option value='Хабаровский край'>Хабаровский край</option><option value='Хакасия'>Хакасия</option><option value='Ханты-Мансийский АО'>Ханты-Мансийский АО</option><option value='Челябинская область'>Челябинская область</option><option value='Чечня'>Чечня</option><option value='Читинская область'>Читинская область</option><option value='Чувашия'>Чувашия</option><option value='Чукотский АО'>Чукотский АО</option><option value='Эвенкийский АО'>Эвенкийский АО</option><option value='Якутия'>Якутия</option><option value='Ямало-Ненецкий АО'>Ямало-Ненецкий АО</option><option value='Ярославская область'>Ярославская область</option>";
			$html = "<div id='ord_method'>"
			."<h2>Выберите способ доставки</h2>"
		."	<table>"
		."		<tr><td class='td_lrow_cd'>Почта россии*</td><td class='td_rrow_cd'><div id='post' class='ord_post'>240руб</div></td></tr>"
		."		<tr><td class='td_lrow_cd'>Доставка курьером**</td><td class='td_rrow_cd'><div id='flat' class='ord_post'>150руб</div></td></tr>"
		."	</table>"
		."	<div class='d_zacon'>ПО ЗАКОНУ «О ЗАЩИТЕ ПРАВ ПОТРЕБИТЕЛЕЙ»<br/>И СОГЛАСНО ПОСТАНОВЛЕНИЮ РОСПОТРЕБНАЗОРА РФ<br/>СРЕДСТВА ГИГИЕНЫ, А ТАКЖЕ ПРЕДМЕТЫ <span style='font-weight:bold'>НИЖНЕГО БЕЛЬЯ</span><br/>ВОЗВРАТУ И ОБМЕНУ НЕ ПОДЛЕЖАТ.</div>"
		."	<div class='d_snoska'>*ЦЕНЫ НА ДОСТАВКУ В ВАШ ГОРОД ДИКТУЕТ ПОЧТА А НЕ МЫ<br/>**КУРЬЕР ДОСТАВЛЯЕТ ВАШ ТОВАР ТОЛЬКО ПО ЛИНИИ МЕТРО</div>"
		."</div>"
		."<div style='display:none' id='post_date'>"
		."	<table>"
		."		<tr><td class='td_lrow_cd_2'>Доставка</td><td class='td_rrow_cd'><div id='ordmethod' ></div></td></tr>"
		."	</table>"
		."	<h2>Введите ваши данные</h2>"
		."	<table>"
		."	<tr class='ord_pd'><td class='td_lrow'>Регион</td><td class='td_rrow'><select id='regio' name='regio' class='ordselect'>"
		.$regio."		</select></td></tr>"
		."	<tr class='ord_pd'><td class='td_lrow'>Город</td><td class='td_rrow'><input value='' class='ordinput' id='city' name='city'></td></tr>"
		."	<tr class='ord_pd'><td class='td_lrow'>Индекс</td><td class='td_rrow'><input value='' class='ordinput' id='index' name='index'></td></tr>"
		."	<tr class='ord_ad'><td class='td_lrow'>Адрес</td><td class='td_rrow'><input value='' class='ordinput' id='addres' name='addres' /></td></tr>"
		."	<tr class='ord_fd'><td class='td_lrow'>Контактный телефон</td><td class='td_rrow'><input value='' class='ordinput' id='phone' name='phone' /></td></tr>"
		."	<tr class='ord_ad'><td class='td_lrow'>Фамилия</td><td class='td_rrow'><input value='".$user->surname."' class='ordinput' id='sname' name='sname'></td></tr>"
		."	<tr class='ord_ad'><td class='td_lrow'>Имя</td><td class='td_rrow'><input value='".$user->name."' class='ordinput' id='fname' name='name'></td></tr>"
		."	<tr class='ord_pd'><td class='td_lrow'>Отчество</td><td class='td_rrow'><input value='' class='ordinput' id='tname' name='tname'></td></tr>"
		."	<tr class='ord_ad'><td class='td_lrow'>Комментарии</td><td class='td_rrow'><input value='' class='ordinput' id='comms' name='comms'/></td></tr>"
		."	</table>"
		."	<div id='order_total'>Итого c доставкой: <span id='crtttl'></span> <span class='order'><a href='/order'>Подтвердить</a></span></div>"
		."</div>";





			return 'orderForm([{"date":"'.$html.'","post":240,"flat":150,"total":'.$cart->plainTotal().'}]);';
		}
		else
			return "error();";
	}



	public function processOrder()
	{
		$cart = &vk_Core::getCart();
		$cart->ProcessOrder();



		return "processed();";
	}


	public function UpdateCartPrd($prd,$qty)
	{
		$cart = &vk_Core::getCart();

		$itms = $cart->Update($prd,$qty);
		$json = '[{"ids":"prd'.$prd.'","qty":"'.$itms.'","items":"'.$cart->getTotalQty().'","total":"'.$cart->getTotal().'"}]';

		return "updCart(".$json.")";
	}

	public function AddToCart($prdid,$qty,$size)
	{


		//$json = '[{"items":"1 товар","total":"350"}]';

		$cart = &vk_Core::getCart();

		$cart->AddToCart($prdid,$qty,$size);
		$json = '[{"items":"'.$cart->getTotalQty().'","total":"'.$cart->getTotal().'"}]';

		return "newCart(".$json.")";
	}

	public function DeleteFrom($prdid,$size)
	{

		$cart = &vk_Core::getCart();

		$cart->DeleteFrom($prdid,$size);
		$json = '{"items":"'.$cart->getTotalQty().'","total":"'.$cart->getTotal().'","ids":"prd'.$prdid.'_'.$size.'"}';

		//$json .= $this->getCart(0);

		return "refreshCart([".$json."])";
	}

	public function getCart($js=1)
	{

		//print_r($_SESSION['cart']);

		$cart = &vk_Core::getCart();
		
		$crt = $cart->getCart();

		$json = '';

		$i=0;

		if(count($crt->prd)==0)
			$json.='{"cartFree":"1"}';

		foreach($crt->prd as $c)
		{

			if($i!=0)
				$json .=',';

			$json .='{"prdid":"'.$c->prdid.'","qty":"'.$c->qty.'","size":"'.$c->size.'","name":"'.$c->name.'","size_w":"'.$c->size_w.'","img":"'.$c->img.'"}';

			$i++;
		}

		//return "getCart(".$json.",\"total\":{\"total\":\"1500\"}])";
		if($js==1)
			return "getCart([".$json.",{\"total\":\"".$cart->getTotal()."\"}])";
		else
			return $json.",{\"total\":\"".$cart->getTotal()."\"}";
		
	}



	public function getCategories($parent=0)
	{
		//echo "!";
		$this->db->query("select c.categories_id,cd.categories_name,c.categories_image as image from os_categories as c
			left join os_categories_description as cd on cd.categories_id=c.categories_id
			where c.categories_status=1 and c.parent_id=".$parent);

		$res = $this->db->results();
		if(!isset($res))
			return;
		

		if($this->type=='JSON')
		{
			$json ="[";
			foreach($res as $k=>$r)
			{
				if($k!=0)
					$json .= ',';

				$mans = $this->getManufacturers($r->categories_id);
				$json .='{"id":"'.$r->categories_id.'","name":"'.$r->categories_name.'","img":"'.$r->image.'"'.($mans!=''?',"man":'.$mans:'').'}';
			}

			$json .= "]";

			return "getCatalog(".$json.");";
		}
		return $res;
	}

	public function getManufacturers($catid=0)
	{

		$sql = "select manufacturers_name,m.manufacturers_id from os_manufacturers as m
					left join os_products as p on m.manufacturers_id=p.manufacturers_id
					left join os_products_to_categories as p2c on p.products_id=p2c.products_id
					left join os_categories as c on c.categories_id=p2c.categories_id".
					($catid!=0?(' where c.categories_id='.$catid.' group by manufacturers_name'):'');

		$this->db->query($sql);

		$res = $this->db->results();
		if($this->db->num_rows()==0)
			return '';

		if($this->type=='JSON')
		{
			$json ="[";
			foreach($res as $k=>$r)
			{
				if($k!=0)
					$json .= ',';
				$json .='{"id":"'.$r->manufacturers_id.'","name":"'.$r->manufacturers_name.'"}';
			}

			$json .= "]";

			return $json;
		}
		return $res;
	}

	public function getProducts($catid=0,$manid=0,$page=1)
	{



		//here to fix

		//$this->db->query("select count(*) as total from os_categories where parent_id = '".$catid."'");


		

		$sql = "select SQL_CALC_FOUND_ROWS *,c.categories_id, cd.categories_name as name, p.products_image as img from os_categories as c
			left join os_categories_description as cd on cd.categories_id=c.categories_id
			left join os_products_to_categories as p2c on p2c.categories_id=c.categories_id
			left join os_products as p on p2c.products_id=p.products_id ".($manid==0?"":" and p.manufacturers_id=".$manid).
			" where parent_id = ".$catid." and c.categories_status=1 and p.products_status=1 group by name limit ".(($page-1)*8).",8";

		//echo $sql;

		$this->db->query($sql);
		if($this->db->num_rows()>0)
		{

			$cats = $this->db->results();
			$breads = "select categories_name from os_categories_description where categories_id=".$catid;
			$this->db->query($breads);
			$br = $this->db->result();

			$breads_json = '{"name":"Каталог","url":"/catalog"},{"name":"'.$br->categories_name.'"}';

			$this->db->query("select FOUND_ROWS() as count");
			$nums = $this->db->result();
			$nums = ceil($nums->count/8);
			$pages = '{"page":'.$page.',"nums_page":'.$nums.'}';

			if($manid!=0)
				$mns = "&man=".$manid;

			$json ='[';
			if($page==1)
				$json .='['.$breads_json.'],';
			$json .=$pages;
			foreach($cats as $k=>$r)
			{
				$json .= ',';
				$json .='{"id":"'.$r->categories_id.'","name":"'.$r->name.'","url":"/categories?cat='.$r->categories_id.$mns.'","img":"'.$r->img.'","price":0}';
			}

			$json .= "]";

			if($page==1)
				return "getProducts(".$json.");";
			else
				return "getPage(".$json.");";
	
			
		}




		$breads_json = '';

		$where='';
		if($catid!=0)
		{
			$where=' left join os_products_to_categories as p2c on p2c.products_id = p.products_id
				where p2c.categories_id='.$catid.' and';

			$breads = "select cd1.categories_name as name1, cd2.categories_name as name2, cd2.categories_id as id2 from os_categories_description as cd1
				left join os_categories as c on c.categories_id = cd1.categories_id
				left join os_categories_description as cd2 on cd2.categories_id = c.parent_id
				 where cd1.categories_id=".$catid;

			$this->db->query($breads);
			$br = $this->db->result();

			$breads_json = '{"name":"Каталог","url":"/catalog"},'.($br->name2!=NULL?'{"name":"'.$br->name2.'","url":"/categories?cat='.$br->id2.'"},':"").'{"name":"'.$br->name1.'"}';
		}
		else
			$where = ' where';
		if($manid!=0)
		{
			
			$where .= ' p.manufacturers_id='.$manid.' and';

			$breads = "select m.manufacturers_name, cd1.categories_name as name1,cd1.categories_id as id1, cd2.categories_name as name2, cd2.categories_id as id2 from os_categories_description as cd1
				left join os_categories as c on c.categories_id = cd1.categories_id
				left join os_categories_description as cd2 on cd2.categories_id = c.parent_id
				left join os_manufacturers as m on m.manufacturers_id =".$manid." where cd1.categories_id=".$catid;
			$this->db->query($breads);
			$br = $this->db->result();

			$breads_json = '{"name":"Каталог","url":"/catalog"},'.($br->name2!=NULL?'{"name":"'.$br->name2.'","url":"/categories?cat='.$br->id2.'"},':"").'{"name":"'.$br->name1.'","url":"/categories?cat='.$catid.'"},{"name":"'.$br->manufacturers_name.'"}';
		}

		if($page<1)
			$page=1;

		$where .=' p.products_status=1';

		$limit = " limit ".(($page-1)*8).",8";

		$sql = "select SQL_CALC_FOUND_ROWS *,p.products_id,p.products_price,p.ptype,p.products_image as image,m.manufacturers_name as manname,pd.products_name from os_products as p
				left join os_products_description as pd on p.products_id=pd.products_id
				left join os_manufacturers as m on m.manufacturers_id=p.manufacturers_id ".$where.$limit;


		//echo $sql;
		$this->db->query($sql);
		$res=$this->db->results();

		$this->db->query("select FOUND_ROWS() as count");

		$nums = $this->db->result();

		//print_r($nums);
		$nums = ceil($nums->count/8);

		//echo $nums->count;

		$pages = '{"page":'.$page.',"nums_page":'.$nums.'}';


		if($this->db->num_rows()==0)
			return 'getProducts(null);';



		$this->db->query("select configuration_key as ckey, configuration_value as val from os_configuration where configuration_id>597");
	
		$prices = $this->db->results();

		//print_r($prices);
		
		if($this->type=='JSON')
		{
			$json ='[';

			if($page==1)
				$json .='['.$breads_json.'],';

			$json .=$pages;
			
			foreach($res as $k=>$r)
			{
				//if($k!=0)
				$json .= ',';

				$price=0;

				if($r->ptype==1)
					$price = $prices[0]->val;
				else
					$price = $prices[2]->val;
				$json .='{"id":"'.$r->products_id.'","name":"'.$r->products_name.'","url":"/product?prdid='.$r->products_id.'","img":"'.$r->image.'","price":"'.$price.' руб","man":"'.$r->manname.'"}';
			}

			$json .= "]";
		
			if($page==1)
				return "getProducts(".$json.");";
			else
				return "getPage(".$json.");";
		}
	}



	public function getProduct($prdid)
	{

		$sql = "select p.products_id,p.ptype,p.products_model as model,p.products_image as image,m.manufacturers_id as manid,m.manufacturers_name as manname,pd.products_name,pd.products_description as `desc`, cd.categories_name as catname, cd.categories_id as catid from os_products as p
				left join os_products_to_categories as p2c on p2c.products_id = p.products_id
				left join os_categories_description as cd on cd.categories_id = p2c.categories_id
				left join os_products_description as pd on p.products_id=pd.products_id
				left join os_manufacturers as m on m.manufacturers_id=p.manufacturers_id where p.products_status=1 and p.products_id=".$prdid;

		//echo $sql;
		$this->db->query($sql);
		$r=$this->db->result();


		$this->db->query("SELECT cd.categories_name AS name, cd.categories_id as id
					FROM os_categories AS c
					LEFT JOIN os_categories_description AS cd ON c.parent_id = cd.categories_id
					WHERE c.categories_id=".$r->catid);

		$inbr ='';
		$c = $this->db->result();	
		if($c->name!=null)
			$inbr = '{"name":"'.$c->name.'","url":"/categories?cat='.$c->id.'"},';



		$breads_json = '{"name":"Каталог","url":"/catalog"},'.$inbr.'{"name":"'.$r->catname.'","url":"/categories?cat='.$r->catid.'"},';
		if($r->manid!=0)
			$breads_json .= '{"name":"'.$r->manname.'","url":"/categories?cat='.$r->catid.'&man='.$r->manid.'"},';

		$breads_json .= '{"name":"'.$r->products_name.'"}';

		
		if($this->db->num_rows()==0)
			return '';



		$this->db->query("select configuration_key as ckey, configuration_value as val from os_configuration where configuration_id>597");
	
		$prices = $this->db->results();

		//print_r($prices);
		
		if($this->type=='JSON')
		{
			$json ="[[".$breads_json."],";
		
			$price=0;

			if($r->ptype==1)
				$price = $prices[0]->val;
			else
				$price = $prices[2]->val;


			$this->db->query("SELECT products_options_values_name as name, pa.options_values_id as value FROM os_products_options_values as pov
				left join os_products_attributes as pa on pa.options_values_id=pov.products_options_values_id
				where pa.products_id=".$prdid." and pa.attributes_stock>0 order by pov.products_options_values_id");

			$attr='"attr":{';

			$aa = $this->db->results();
			foreach($aa as $k=>$a)
			{
				if($k!=0)
					$attr.=',';
				$attr.='"'.$a->value.'":"'.$a->name.'"';
			}
			$attr.='}';

			$json .='{"id":"'.$r->products_id.'","name":"'.$r->products_name.'","model":"'.$r->model.'","img":"'.$r->image.'","price":"'.$price.'","man":"'.$r->manname.'",'.$attr.'}';

			$json .= "]";

			return "getProduct(".$json.");";
		}
	}


	public function getArticle($url)
	{
		$sql = "select * from os_content_manager where content_page_url='".$url."'";

		//echo $sql;
		$this->db->query($sql);
		$res=$this->db->result();


		if($this->type='JSON')
		{

			
			return 'sArticle(['.$this->json_encode(array('title'=>$res->content_heading,'data'=>$res->content_text)).']);';
			//return 'sArticle([{"title":"'.$res->content_title.'","data":"'.$string=str_replace("\r\n","",$res->content_text).'"}]);';
			//return 'sArticle([{"title":"'.$res->content_title.'","data":"'.utf8_encode($res->content_text).'"}]);';
			
		}
		else
			return $res;

	}


	private function json_encode($value) 
	    {
		if (is_int($value)) {
		    return (string)$value;   
		} elseif (is_string($value)) {
			$value = str_replace(array('\\', '/', '"', "\r", "\n", "\b", "\f", "\t"), 
			                     array('\\\\', '\/', '\"', '\r', '\n', '\b', '\f', '\t'), $value);
			$convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
			$result = "";
			for ($i = mb_strlen($value) - 1; $i >= 0; $i--) {
			    $mb_char = mb_substr($value, $i, 1);
			    if (mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)) {
			        $result = sprintf("\\u%04x", $match[1]) . $result;
			    } else {
			        $result = $mb_char . $result;
			    }
			}
			return '"' . $result . '"';                
		} elseif (is_float($value)) {
		    return str_replace(",", ".", $value);         
		} elseif (is_null($value)) {
		    return 'null';
		} elseif (is_bool($value)) {
		    return $value ? 'true' : 'false';
		} elseif (is_array($value)) {
		    $with_keys = false;
		    $n = count($value);
		    for ($i = 0, reset($value); $i < $n; $i++, next($value)) {
		                if (key($value) !== $i) {
				      $with_keys = true;
				      break;
		                }
		    }
		} elseif (is_object($value)) {
		    $with_keys = true;
		} else {
		    return '';
		}
		$result = array();
		if ($with_keys) {
		    foreach ($value as $key => $v) {
		        $result[] = json_encode((string)$key) . ':' . json_encode($v);    
		    }
		    return '{' . implode(',', $result) . '}';                
		} else {
		    foreach ($value as $key => $v) {
		        $result[] = json_encode($v);    
		    }
		    return '[' . implode(',', $result) . ']';
		}
	    } 

}
?>
