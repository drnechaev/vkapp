/*
	YABOMBA.RU
*/

jQuery.noConflict();

var catalog;
var content;
var loading;
var breads;

var cart;
var cartWidth;

var prds;
var pageNums;
var curPage;

var prevOff;
var nextOff;

var crtadded;

var currentUrl;
var catUrl;
var lastUrl;

function createProductsUl(data,id)
{
	var text ="<ul class='prds' id='prds_"+id+"'>";
	jQuery.each(data, function(key, val) {
			text += '<li class="cprd"><a href="' + val.url + '" class="cimg"><img src="/images/product_images/thumbnail_images/' + val.img + '" alt="' + val.name + '" /></a><a href="'+val.url+'">' + val.name + '</a><br/>';

			if(val.price!=0)
				text += val.price;

			text += '</li>';
		});

	return text + "</ul>";
}

function getProducts(data)
{
	loading.hide();

	if(data==null)
	{
		var text = "Категория пуста";
		content.css({"overflow":"hidden","background":"none"}).html(text);
		return;
	}

	catUrl = currentUrl;

	showBread(data.shift());

	var pages = data.shift();

	var text = '<div id="cbprev" class="coff">	</div>	<div id="cbnext"></div> <div id="prdwrap"> <div class="cont">';

	text += createProductsUl(data,pages.page) + '</div></div>';


	if(pages.nums_page!=1)
	{
		text +='<div id="pages">';
		for(var i=1;i<=pages.nums_page;i++)
			text +='<div rel="'+i+'" class="page" id="page_'+i+'">'+i+'</div>';
	
		text +='</div>';
	}

	content.css({"overflow":"hidden","background":"none"}).html(text).find("a").bind("click",getTovar);

	jQuery(".page").bind("click",showPage);
	jQuery("#page_"+pages.page).addClass("on");
	curPage=pages;

	prds = jQuery("#prdwrap .cont");
	//prdsWidth = 457;
	pageNums = 1;



	if(pages.nums_page!=1)
	{
		nextOff=0;

	}
	else
	{
		nextOff=1;
		jQuery("#cbnext").addClass("coff");
	}
	jQuery("#cbnext").bind("click",catNext);
	jQuery("#cbprev").bind("click",catPrev);
	prevOff=1;


}

function animatePage(pages)
{
	var ss = jQuery("#prds_"+pages.page).prevAll(".prds").length;

	marg = ss*(-457);
	prds.animate({"margin-left":marg+"px"},"fast");

	jQuery("#page_"+curPage.page).removeClass("on");
	jQuery("#page_"+pages.page).addClass("on");
	curPage = pages;

	if(curPage.page==pages.nums_page)
	{
		nextOff=1;
		jQuery("#cbnext").addClass("coff");
	}
	else if(nextOff==1) 
	{
		jQuery("#cbnext").removeClass("coff");
		nextOff=0;
	}

	

	if(curPage.page==1)
	{
		prevOff=1;
		jQuery("#cbprev").addClass("coff");
	}
	else if(prevOff==1)
	{
		jQuery("#cbprev").removeClass("coff");
		prevOff=0;
	}

}


function getPage(data)
{
	loading.hide();
	pages = data.shift();

	var text = createProductsUl(data,pages.page);

	var prevPage = pages.page-1;

	if(prevPage!=1)
	{
		if(!jQuery("#prds_"+prevPage).length)
		{
			while(prevPage!=1)
			{
				prevPage = prevPage -1;
				if(jQuery("#prds_"+prevPage).length)
					break;
			}
			
		}
	}

	pageNums +=1;
	prds.css({"width":(pageNums*457)+'px'});
	jQuery(text).insertAfter("#prds_"+prevPage).find("a").bind("click",getTovar);

	animatePage(pages);


}

function showPage()
{
	page = jQuery(this).attr('rel');

	if(page!=curPage.page)
	{
		if(!jQuery("#prds_"+page).length)
		{
			loading.show();
			getInfo({'url':catUrl+"&page="+page},cb);
		}
		else
			animatePage({"page":page,"nums_page":curPage.nums_page});
	}
	
}

function catNext()
{

	if(nextOff==1)
		return;


	if(!jQuery("#prds_"+(curPage.page+1)).length)
	{
		loading.show();
		getInfo({'url':catUrl+"&page="+(curPage.page+1)},cb);
	}
	else
		animatePage({"page":(curPage.page+1),"nums_page":curPage.nums_page});


}

function catPrev()
{

	if(prevOff==1)
		return;


	if(!jQuery("#prds_"+(curPage.page-1)).length)
	{
		loading.show();
		getInfo({'url':catUrl+"&page="+(curPage.page-1)},cb);
	}
	else
		animatePage({"page":(curPage.page-1),"nums_page":curPage.nums_page});
}





function getProduct(data)
{
	loading.hide();
	showBread(data.shift());

	//if(data[0].name!='')
	//	text = "<h1>"+data[0].name+"</h1>";
	
	//text = text + "<img src='/images/product_images/info_images/"+data[0].img+"' />";

	
	var sel = "";

	jQuery.each(data[0].attr, function(key,val){
		sel = sel + "<option value='"+key+"'>"+val+"</option>";
	});

	if(sel!='')
		sel = "<div class='vsize'>Размер: <select id='slsize'>" + sel +"</select></div>";

	

	var text = "<div id='vprd'>"+
			"<div class='vimg'><img src='/images/product_images/info_images/"+data[0].img+"' /><div class='vname'>"+data[0].name+"</div></div>"+
			"<div class='vdesc'><div class='vmodel'>"+data[0].model+"</div><div class='vnums'>Количество <input id='qty' name='qty' value='1'></div>"+
			sel+
			"<input type='hidden' id='pid' name='pid' value='"+data[0].id+"' /><div id='addtc'> </div>"+
			"<div class='vprice'>Предварительная цена*<br/><div class='vprs'>"+data[0].price+" руб.</div></div>"+
			"</div>"+
			"<div class='vspec'>*При покупке от 5 моделей вы получите скидку</div></div>";
	
	content.css({"overflow":"auto","background":"none"}).html(text);
	content.find("#addtc").bind("click",addToCart);
}


function getCatalog(data)
{

loading.hide();
showBread([{"name":"Каталог"}]);

var text = "";
jQuery.each(data, function(key, val) {

	text = text + "<div class='cats'><a href='/categories?cat="+val.id+"'><img src='/images/categories/"+val.img+"' alt=''/></a><br/>"+
		"<a href='/categories?cat="+val.id+"'>"+val.name+"</a></div>";

	});
content.css({"overflow":"hidden","background":"none"}).html(text).find("a").bind("click",getTovar);
	
}


function getInnerCatalog(data)
{

loading.hide();
showBread([{"name":"Каталог"}]);

var text = "";
jQuery.each(data, function(key, val) {

	text = text + "<div class='cats'><a href='/categories?cat="+val.id+"'><img src='/images/categories/"+val.img+"' alt=''/></a><br/>"+
		"<a href='/categories?cat="+val.id+"'>"+val.name+"</a></div>";

	});
content.css({"overflow":"hidden","background":"none"}).html(text).find("a").bind("click",getTovar);
	
}


function showBread(n)
{
	var text = '';
	for(var i=0; i<n.length; i++) {
		if(i+1 == n.length)
			text = text + "<span>"+n[i].name+"</span>";
		else
			text = text + "<a class='brd_last' href='"+n[i].url+"'>"+n[i].name+"</a> → ";
	}

	breads.html(text).find("a").bind("click",getTovar);
}

function hideAll()
{
loading.hide();
breads.html("");
}


function sArticle(data)
{
	hideAll();
	var text = '';
	if(data[0].title!='')
	text = "<h1>"+data[0].title+"</h1>";
	
	text = '<div id="artcl">'+text + data[0].data + "</div>";
	content.css({"overflow":"auto","background":"#fff"}).html(text).find("a").bind("click",getTovar);
}


function getCart(data)
{
	hideAll();


	total = data.pop();
	var text='';

	if(data[0].cartFree)
	{
		text='В корзине сейчас пусто';
		content.css({"overflow":"hidden","background":"none"}).html(text);
	}
	else
	{
		text = "<div id='cart_head'>"
			+"<div class='foto'>Фото</div>"
			+"<div class='model'>Модель</div>"
			+"<div class='qty'>Кол-во</div>"
			+"<div class='price'>Размер</div>"
			+"</div>"
			+"<div id='cart_body'>"
			+"<div id='cbprev' class='coff'> </div>"
			+"<div id='cbnext'> </div>"
			+"	<div class='crt_wnd'>"
			+"	<ul>";

		cartWidth = 0;

		jQuery.each(data, function(key, val) {
			text = text + "<li id='prd"+val.prdid+"_"+val.size+"'>"
					+"<div class='foto'><img src='/images/product_images/thumbnail_images/"+val.img+"'></div>"
					+"<div class='model'>"+val.name+"</div>"
					+"<div class='qty'><div class='n_qty'>"+val.qty+"</div><div class='arrows'><div class='qty_plus' rel='"+val.prdid+"_"+val.size+"'> </div><div class='qty_minus' rel='"+val.prdid+"_"+val.size+"'> </div></div>"
					+"<div class='price'>"+val.size_w+"</div>"
					+"<div class='del'><a href='/cart?del="+val.prdid+"&size="+val.size+"'> Удалить</a></div>"
					+"</li>";

			cartWidth += 111;
		});

		text = text + "</ul></div></div><div id='cart_total'>Итого: <span id='crtttl'>"+total.total+"</span> <span class='order'><a href='/order'>Оформить</a></div>";

		content.css({"overflow":"hidden","background":"none"}).html(text).find("a").bind("click",getTovar);
		//jQuery(".crt_wnd ul").css({"width":width+"px"});
		cart = jQuery(".crt_wnd ul");
		cart.css({"width":cartWidth+"px"});

		jQuery(".arrows div").bind("click",prdPM);

		nextOff=0;
		if(cartWidth<367)
		{
			jQuery("#cbnext").addClass("coff");
			nextOff=1;
		}
		jQuery("#cbnext").bind("click",cartNext);
		jQuery("#cbprev").bind("click",cartPrev);
		prevOff=1;
		
	}
}

function prdPM()
{
	var prd = jQuery(this);
	if(prd.attr("class")=='qty_plus')
		qty = 1;
	else
		qty = -1;
	var urls = "/cart?prd="+prd.attr("rel")+"&qty="+qty;
	//alert(urls);
	getInfo({'url':urls},cb);
}

function updCart(data)
{
	if(data[0].qty==0)
	{
		//alert("ERROR!");
		return;
	}

	jQuery("#"+data[0].ids+" .n_qty").text(data[0].qty);
	jQuery("#basket_list").html("В корзине "+data[0].items+"<br/>На сумму "+data[0].total+"<br/><span style='float:right'><a href='/cart'>Смотреть</a></span>");
	jQuery("#basket_list a").bind("click",getTovar);
	jQuery("#crtttl").html(data[0].total);
}


function refreshCart(data)
{
	hideAll();

	var itms = data.shift();

	if(itms.items!=0)
	{
		jQuery("#basket_list").html("В корзине "+itms.items+"<br/>На сумму "+itms.total+"<br/><span style='float:right'><a href='/cart'>Смотреть</a></span>");
		jQuery("#basket_list a").bind("click",getTovar);

		jQuery("#"+itms.ids).remove();

		var marg = parseInt(cart.css("margin-left"));
		if(marg<0)
		{
			marg = marg + 111;
			if( marg==0 ) 
			{
				jQuery("#cbprev").addClass("coff");
				prevOff=1;
			}

		}


		cartWidth -= 111;
		cart.css({"width":cartWidth+"px","margin-left":marg+"px"});

		jQuery("#crtttl").html(itms.total);

		if(cartWidth<367)
		{
			jQuery("#cbnext").addClass("coff");
			jQuery("#cbprev").addClass("coff");
			nextOff=1;
			prevOff=1;
		}
		
	}
	else
	{
		jQuery("#basket_list").html("Ваша корзина пуста");
		content.css({"overflow":"hidden","background":"none"}).html("В корзине сейчас пусто");
		//jQuery("#basket_list .basket_1").hide();
	}

	//getCart(data);
}


function myOrders(data)
{

	hideAll();

	var text = "<table><tr><td>Номер</td><td>Сумма</td><td>Статус</td><td>Дата</td><td></td><tr>";
	jQuery.each(data, function(key, val) {
		text = text + "<tr><td>"+val.id+"</td><td>"+val.total+"</td><td>"+val.status+"</td><td>"+val.date+"</td><td><a href='/myorders?oid="+val.id+"'>Подробнее</a></td></tr>";
	});

	text = text + "</table>";

	content.css({"overflow":"auto","background":"none"}).html(text).find("a").bind("click",getTovar);	
}

function myOrder(data)
{
	loading.hide();
	showBread(data.shift());

	var text = "<table><tr><td>Номер</td><td>Сумма</td><td>Статус</td><tr>";
	jQuery.each(data[0].prds, function(key, val) {
		text = text + "<tr><td>"+val.name+"</td><td>"+val.qty+"</td><td>"+val.size+"</td></tr>";
	});

	text = text + "</table>";

	content.css({"overflow":"auto","background":"none"}).html(text).find("a").bind("click",getTovar);	


}

var post_type;

function processed()
{
	loading.hide();
	jQuery("#basket_list").html("Ваша корзина пуста");
	content.html("<img src='/images/thanks.jpg' alt='Спасибо' style='margin:-10px' />");


}

function prepareOrder()
{


	var regio = jQuery("#regio :selected").val();
	if(post_type=='post' && regio=='')
	{	
		alert("Выберите регион");
		jQuery("#regio").focus();
		return false;
	}
	var city = jQuery("#city").val();
	if(post_type=='post' && city=='')
	{	
		alert("Введите город");
		jQuery("#city").focus();
		return false;
	}
	var index = jQuery("#index").val();
	if(post_type=='post' && index=='')
	{	
		alert("Введите индекс");
		jQuery("#index").focus();
		return false;
	}
	var fname = jQuery("#fname").val();
	if(fname=='')
	{	
		alert("Введите имя");
		jQuery("#fname").focus();
		return false;
	}
	var sname = jQuery("#sname").val();
	if(sname=='')
	{	
		alert("Введите фамилию");
		jQuery("#sname").focus();
		return false;
	}
	var tname = jQuery("#tname").val();
	if(post_type=='post' && tname=='')
	{	
		alert("Введите отчество");
		jQuery("#city").focus();
		return false;
	}

	var phone = jQuery("#phone").val();
	if(post_type=='flat' && (phone==''))
	{	
		alert("Введите телефон");
		jQuery("#phone").focus();
		return false;
	}

	var adress = jQuery("#addres").val();
	if(adress=='')
	{	
		alert("Введите адрес");
		jQuery("#addres").focus();
		return false;
	}
	var coms = jQuery("#comms").val();


	loading.show();	
	//jQuery("#qty").attr("value");

	//urls = "/order?pt="+post_type+"&city="+city+"&index="+index+"&fname="+fname+"&sname="+sname+"&lname="+tname+"&addres="+adress+"&regio="+regio;
	var urls = "/process";
	getInfo({'url':urls,"pt":post_type,"city":city,"index":index,"fname":fname,"sname":sname,"lname":tname,"addres":adress,"regio":regio,"comm":coms,"phone":phone},cb);


	return false;
}

function orderForm(data)
{
	loading.hide();
//	content.html("Оформить");



	var text = data[0].date;

	content.html(text).find("a").bind("click",prepareOrder);



	jQuery(".ord_post").bind("click",function(){
			post_type=jQuery(this).attr("id");
			if(post_type=='post')
			{
				jQuery(".ord_pd").show();
				jQuery("#ordmethod").text("Почта России");
				total = data[0].total+data[0].post;

			}
			else
			{
				jQuery(".ord_fd").show();
				jQuery("#ordmethod").text("Курьером");
				total = data[0].total+data[0].flat;
			}

			jQuery("#crtttl").html(total + "руб.");

			jQuery("#ord_method").hide("normal");
			jQuery("#post_date").show("normal");
		});

	jQuery("#ordmethod").bind("click",function(){
		jQuery(".ord_pd").hide();
		jQuery(".ord_fd").hide();
		jQuery("#ord_method").show("normal");
		jQuery("#post_date").hide("normal");

	});
}



function cartNext()
{

	if(nextOff==1)
		return;


	var marg = parseInt(cart.css("margin-left"));

	marg = marg - 111;

	cart.animate({"margin-left":marg+"px"},"fast");
	if(marg==-111)
	{
		jQuery("#cbprev").removeClass("coff");
		prevOff=0;
	}

	if( (cartWidth+marg)<367 )
	{
		jQuery("#cbnext").addClass("coff");
		nextOff=1;
	}
}

function cartPrev()
{

	if(prevOff==1)
		return;


	var marg = parseInt(cart.css("margin-left"));
	marg = marg + 111;

	cart.animate({"margin-left":marg+"px"},"fast");
	if(nextOff==1)
	{
		jQuery("#cbnext").removeClass("coff");
		nextOff=0;
	}

	if( marg==0 ) 
	{
		jQuery("#cbprev").addClass("coff");
		prevOff=1;
	}
}





function newCart(data)
{

	drawAdd();
	jQuery("#basket_list").html("В корзине "+data[0].items+"<br/>На сумму "+data[0].total+"<br/><span style='float:right'><a href='/cart'>Смотреть</a></span>");
	jQuery("#basket_list a").bind("click",getTovar);
	//jQuery("#basket_list .basket_1").show();
}


function drawAdd()
{
	crtadded.show().animate({"left":'40%',"top":"37%","width":"100px","height":"50px"},
				function(){
					crtadded.text("Товар добавлен").oneTime("1s", function() {
							  crtadded.fadeOut("normal",function(){
								crtadded.css({"left":'50%',"top":"50%","width":"1px","height":"1px"}).text("");
								});
							});
				});
}



function cb(data)
{
	loading.hide();
	//funct = data[0].funct+"(data);";
	//data.splice(0,1);
	//eval(funct);
}


function getInfo(data,callback,bsend){


	var uri = "/app/";
	
	if(jQuery.browser.safari || jQuery.browser.opera)
	{
		uri += "?sesid="+sesid;
		//alert(uri);
	}

	var ajax_info ={url:uri,dataType:"jsonp",type:"post"};
	ajax_info.data = data;
	ajax_info.data.act=1;
	ajax_info.success = callback;
	if(bsend)
		ajax_info.beforeSend=bsend;

	jQuery.ajax(ajax_info);
}


function drawCatalog()
{

	if(catalog.draw!=1)
	{
		catalog.slideDown("normal");
		catalog.draw=1;
	}

	catalog.stopTime().oneTime("1s", function() {
		  catalog.slideUp('normal');
		  catalog.draw=0;
		});
}





function getTovar(){

	//if(!inited)
	//	return false;


	//$targ = jQuery(this).attr('target');
	if(jQuery(this).attr('target')=='__blank')
		return true;

	var urls = jQuery(this).attr('href');

	if(urls==currentUrl)
		return false;

	lastUrl = currentUrl;
	currentUrl = urls;


	loading.show();

	getInfo({'url':urls},cb);

	

return false;
}



function addToCart(){

	var qty = jQuery("#qty").val();
	if(qty=="")
		qty=1;
	var size = jQuery("#slsize").val();

	if(size===undefined)
		size =0;

	var prdid = jQuery("#pid").val();
	var urls = "/cart?add="+prdid+"&qty="+qty+"&size="+size;

	getInfo({'url':urls},cb);
}

var inited;


function initApp()
{

	if(!catalog)
	{
		catalog = jQuery("#catalogs");
		catalog.hover(function(){
			catalog.stopTime();
		}, function(){
			catalog.oneTime("1s", function() {
			  catalog.slideUp('normal');
			  catalog.draw=0;
			});
		});
	}

	jQuery("#mmenu .a1").hover(function(){
			drawCatalog();
		});


	if(!content)
		content = jQuery("#mwind");

	if(!loading)
	{
		loading = jQuery("<div id='aloading'><img src='/images/aload.gif' /></div>").hide();
		jQuery("#main_wrap").append(loading);
	}

	if(!breads)
		breads = jQuery("#breads");

	
	if(!crtadded)
		crtadded = jQuery("#crtadded");

	jQuery('#msslideshow').slideshow({
	 	 width:557,      // ширина в пикселях
		height:434,     // ширина в пикселях
		index:0,         // начать со слайда номер N
		panel:false,
		play:true,
		loop:true,
		effect:'fade',
		playframe:false,
		imgcenter:true   // выравнивать картинки по центру (пока не работает)
        	});

	jQuery("#basket_1").hover(function(){
		jQuery("#basket_list").css({"height":"40px","padding":"10px 5px","display":"none"}).slideDown("normal");
	}, function(){
		jQuery("#basket_list").stop().slideUp("normal");
	});
	jQuery("a").bind("click",getTovar);
	currentUrl=='/';
	lastUrl='/catalog';
}



