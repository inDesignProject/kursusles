<?php

include('php/lib/db_connection.php');
include('php/lib/enkripsi.php');
include('php/lib/session.php');
require "php/lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

//CEK SESSION UNTUK FORM LOGIN
$show_login	=	$session->cekSession() == 2 ? "false" : "true";
//HABIS - CEK SESSION
if($session->cekSession() <> 2){
	 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
	 die();
}

if($_GET['q'] <> "" && isset($_GET['q'])){
	$idperusahaan		=	$enkripsi->decode($_GET['q']);
} else {
	if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 1){
		echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
	 	die();
	} else {
		$idperusahaan	=	$_SESSION['KursusLesLoker']['IDUSER'];
	}
}

header("Content-type: text/html; charset=utf-8");

?>
<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<title>TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!</title>
		<meta name="description" content="TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!">
		<meta name="author" content="inDesign Project">
		<link rel="shortcut icon" href="favicon.ico">
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssbootstrap');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssfontawesome');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssdatepicker');?>.cssfile" />
        <!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<style>
			.nav-tabs > li {
				margin-bottom: 2px;
				text-align: center;
				font-weight: bold;
				width: 25%;
			}
		</style>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login)?>
		
		<div class="container">
			<h3 class="text-left text_kursusles page-header">HALAMAN UTAMA</h3>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="boxSquare">
						<div role="tabpanel">
							<ul class="nav nav-tabs" role="tablist" id="tab-con-nav">
                                <li role="presentation" class="active tab_list">
                                	<a href="#" id="lowongan" onclick="getContent(this.id)" aria-controls="lowongan" role="tab" data-toggle="tab">
                                    	Atur Lowongan &nbsp; <i class="fa fa-steam"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="kandidat" onclick="getContent(this.id)" aria-controls="kandidat" role="tab" data-toggle="tab">
                                    	Bookmark Kandidat &nbsp; <i class="fa fa-user"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="pencarian" onclick="getContent(this.id)" aria-controls="pencarian" role="tab" data-toggle="tab">
                                    	Pencarian Kandidat &nbsp; <i class="fa fa-search"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="profil" onclick="getContent(this.id)" aria-controls="profil" role="tab" data-toggle="tab">
                                    	Pengaturan Profil &nbsp; <i class="fa fa-wrench"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
					</div>
                </div>
            </div>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="boxSquare" id="tabContent">
                    </div>
                </div>
            </div>
        </div><br/>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    	<script>
			function getContent(id){
	
				$(".tab_list").removeClass('active');
				$("#tabContent").html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
				$("#tabContent").load("<?=APP_URL?>perusahaan/"+id+".php?q=<?=$enkripsi->encode($idperusahaan)?>");
				$("#"+id).closest('li').addClass('active');

			}
			
			$('#lowongan').click();
		</script>
		<?=$session->getTemplate('footer')?>
        <div id="dialog-confirm">
          <p id="text_dialog"></p>
        </div>		
    </body>
</html>