<?php

	include('php/include/enkripsi.php');
	include('php/include/session.php');
	include('php/lib/db_connection.php');
	require "php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	if($session->cekSession() <> 2){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode($session->cekSession())."'</script>";
		 die();
	}

	//MENU ADMIN
	$sqlmenu	=	sprintf("SELECT B.NAMA_MENU, B.FILEID, B.GROUPMENU, B.CLASS 
							 FROM admin_menu_level A
							 LEFT JOIN admin_menu B ON A.IDMENU = B.IDMENU
							 WHERE A.IDLEVEL = %s AND A.STATUS = 1
							 ORDER BY B.ORDERGROUP, B.ORDERMENU"
							, $_SESSION['KursusLesAdmin']['LEVEL']
							);
	$resultmenu	=	$db->query($sqlmenu);
	
	//JIKA DATA DITEMUKAN
	if(isset($resultmenu) || $resultmenu != false){
		$menugroup	=	array();
		$i			=	0;
		$menu		=	'';
		
		foreach($resultmenu as $key){
			
			if(!in_array($key['GROUPMENU'],$menugroup)){
				
				if($i <> 0){
					$menu	.=	"</ul>
									</li>";
				}
				$menu	.=	"<li>
								<a class='".$key['CLASS']." amenu' target='#menu".$key['GROUPMENU']."' id='".trim($key['GROUPMENU'])."' onclick='showMenuChild(this.getAttribute(\"target\"), this.id)'>".$key['GROUPMENU']."</a>
									<span class='arrow'></span>
									<ul class='menuChild' id='menu".$key['GROUPMENU']."'>";

				array_push($menugroup, $key['GROUPMENU']);
			}
			$menu	.=	"<li class='menu-item' id='".$key['FILEID']."' onclick = 'getContent(this.id)'><a href='#'>".$key['NAMA_MENU']."</a></li>";
			$i++;
		}
		
	} else {
		$menu	=	"<li>No Menu</li>";
	}
	
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>KursusLes - Admin</title>
<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141230'.'cssmain');?>.cssfile">
<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssstyle_contrast');?>.cssfile">
<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmainorigin');?>.cssfile">
<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141214'.'cssfontawesome');?>.cssfile">
<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141215'.'cssjqueryui');?>.cssfile">
<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141215'.'cssdatetimepicker');?>.cssfile">
<!--[if IE 9]>
    <link rel="stylesheet" media="screen" href="http://agncloud.com/statics/bankkalsel/p6nuvJjRnqqnlXuWrWJoY2s.cssfile"/>
<![endif]-->
<!--[if IE 8]>
    <link rel="stylesheet" media="screen" href="http://agncloud.com/statics/bankkalsel/p6nuvJjRnqqmlXuWrWJoY2s.cssfile"/>
<![endif]-->
<!--[if lt IE 9]>
	<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->
<style>
h3.logo {
	text-align:right;
	border: none !important;
}
h3.logo a{
	font-size:32px;
	text-align:right;
}
.topheader{
	background:#21887b !important; 
}
.userinfo{
	background:#21887b !important; 
}
.stdtable td {
vertical-align:top !important;
}
@media print{
	html{background: transparent;}
	.topheader, .header, .vernav2, .editodiv, .stdform p, .actionBar, 
	.tableoptions, .dataTables_paginate, .pagedesc, .hornavtab, .breadcrumbs{
		display:none !important;
	}
	.pagetitle { text-align: center; }
	.pageheader .pagetitle, .notab { border-bottom: none !important; padding-bottom: 0px !important;}
	.centercontent {margin-left:0px !important;}	
	.contentwrapper{padding:0px !important;} 
	.logoprint{display:block !important;}
	
}
</style>
</head>
<body class="withvernav">
<div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
</div>
<div class="bodywrapper">
    <div class="topheader">
        <div class="left">
            <h3 class="logo"><a href="home">ADMIN <span><span></a></h3> 
            <span class="slogan">KursusLes.com</span>            
            <br clear="all" />            
        </div><!--left-->
        
        <div class="right">
            <div class="userinfo">
                <span><?=$_SESSION['KursusLesAdmin']['USERNAME']?></span>
                <span>| <a href="logout">Logout</a></span>
            </div><!--userinfo-->
        </div><!--right-->
    </div><!--topheader-->
    
    <div class="vernav2 iconmenu">
    	<ul>
        	<?=$menu?>
        </ul>
        <br /><br />
    </div><!--leftmenu--> 
        
    <div class="centercontent" id="contentmenu"> 
    	
    </div>
    <div class="editordiv" id="editordiv">
   </div>
</div><!--bodywrapper-->
<div id="dialog-confirm" style="display:none">
  <p id="text_dialog"></p>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
<script>
$(document).ready(function(){
	$("#contentmenu").load("<?=APP_URL?>page/dashboard.php");
});
function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}
function getContent(id){
	$('#message_response_container').slideDown('fast').html('');
	$('#contentmenu').slideUp('fast').html("<center style='margin-top: 100px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
	$("#contentmenu").slideDown('fast').load("<?=APP_URL?>page/"+id+".php");
}
function showMenuChild(href, id){
	if($('#'+id).hasClass('active') == false){
		$('.menuChild').slideUp('fast');
		$('.amenu').removeClass('active');
		$(href).slideDown('fast');
		$('#'+id).addClass('active');
	}
}
function getDataOpt(urlFunc, param, id, key, optPrefix){
		
	$.ajax({
		url: 'php/lib/ajax_response.php?f='+urlFunc+'&'+param+'='+key,
		data: {
			format: 'json'
		},
		error: function() {
			return 'nodata';
		},
		type: 'GET',
		dataType: 'text',
		success: function(data) {

			var select		=	document.getElementById(id);
			select.options.length = 0;
			
			var optionUp	=	document.createElement("option");
			optionUp.text	=	optPrefix;
			optionUp.value	=	'';
			select.add(optionUp);

			if(data.length>0){ 
				
				data = JSON.parse(data);
				for(var i = 0; i < data.length; i++) {

					var obj			=	data[i];
					var option		=	document.createElement("option");
					option.text		=	obj[1];
					option.value	=	obj[0];
					select.add(option);

				}
				
			} else { 
				document.getElementById(id).value = "";
			}
			
		}

	});
	
}
function setOptMonth(){
	var arrMonth	=	[
						'Januari',
						'Pebruari',
						'Maret',
						'April',
						'Mei',
						'Juni',
						'Juli',
						'Agustus',
						'September',
						'Oktober',
						'Nopember',
						'Desember',
					];
	$('.optMonth').each(function () {
		var ID		=	this.id;
			elem	=	document.getElementById(ID);
			j		=	1;
			selIdx	=	'';
		for(i=0; i<arrMonth.length; i++){
			var option		= document.createElement("option");
			option.text		= arrMonth[i];
			if(j<10){
				option.value	= "0"+j;
			} else {
				option.value	= j;
			};
			if( "0"+j == 03){ selIdx = i }
			elem.add(option, elem[i]);
			j++;
		}
		document.getElementById(ID).selectedIndex = selIdx;
	});
}

function setOptYear(){
	arrYear		=	[2015,2014,2013,2012,2011,2010,2009,2008,2007,2006,2005,2004,2003,2002,2001,2000];
	$('.optYear').each(function () {
		var ID		=	this.id;
			elem	=	document.getElementById(ID);
		for(i=0; i<=4; i++){
			var option		= document.createElement("option");
			option.text		= arrYear[i];
			option.value	= arrYear[i];
			elem.add(option, elem[i]);
		}
	});
}
</script>
</body>
</html>