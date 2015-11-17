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
	$idmurid		=	$enkripsi->decode($_GET['q']);
} else {
	
	$idmurid	=	$_SESSION['KursusLes']['IDUSER'];
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
				width: 100%;
				text-align: right;
				font-weight: bold;
			}
			.address{background:url("<?=APP_IMG_URL?>icon/street.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
			.materi{background:url("<?=APP_IMG_URL?>icon/materi.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
			.level{background:url("<?=APP_IMG_URL?>icon/level.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
		</style>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login)?>
		
		<div class="container">
			<h3 class="text-left text_kursusles page-header">PROFIL SAYA</h3>
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
					<div class="boxSquare">
						<div role="tabpanel">
							<ul class="nav nav-tabs" role="tablist" id="tab-con-nav">
                                <li role="presentation" class="active tab_list">
                                	<a href="#" id="mprofil" onclick="getContent(this.id)" aria-controls="profil" role="tab" data-toggle="tab">
                                    	Profil &nbsp; <i class="fa fa-user"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="mkursus" onclick="getContent(this.id)" aria-controls="kursus" role="tab" data-toggle="tab">
                                    	Kursus Saya &nbsp; <i class="fa fa-flag-checkered"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="mpencarian" onclick="getContent(this.id)" aria-controls="pencarian" role="tab" data-toggle="tab">
                                    	Pencarian Kursus &nbsp; <i class="fa fa-search"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="mpenawarwanted" onclick="getContent(this.id)" aria-controls="penawaran" role="tab" data-toggle="tab">
                                    	Penawaran Kursus &nbsp; <i class="fa fa-reply-all"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="msg_compose" onclick="getContent(this.id)" aria-controls="privatemessage" role="tab" data-toggle="tab">
                                    	Private Message &nbsp; <i class="fa fa-envelope"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="mbookmark" onclick="getContent(this.id)" aria-controls="bookmark" role="tab" data-toggle="tab">
                                   		Bookmark &nbsp; <i class="fa fa-bookmark"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="mbalance" onclick="getContent(this.id)" aria-controls="balance" role="tab" data-toggle="tab">
                                    	Voucher & Balance &nbsp; <i class="fa fa-tags"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="mwithdraw" onclick="getContent(this.id)" aria-controls="withdraw" role="tab" data-toggle="tab">
                                    	Withdraw &nbsp; <i class="fa fa-download"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="mhelpdesk" onclick="getContent(this.id)" aria-controls="helpdesk" role="tab" data-toggle="tab">
                                    	Helpdesk &nbsp; <i class="fa fa-paper-plane-o"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
					</div>
                </div>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<div class="boxSquare" id="tabContent">
                    </div>
                </div>
            </div>
        </div><br/>
        
        <div class="container">
        	<h3 class="text-left text_kursusles page-header">HALAMAN PENGAJAR</h3>
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <a href="<?=APP_URL?>pengajar_profil" style="text-decoration:none">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <img class="img-responsive img-circle" src="<?=APP_IMG_URL?>/generate_pic.php?type=pr&q=<?=$enkripsi->encode($_SESSION['KursusLes']['FOTO'])?>&w=64&h=64" style="display:inline"/>
                                    <b> Profil Saya </b>
                                </div>
                                <div class="panel-body">
                                    <p>
                                         Lorem ipsum dolor sit amet, consectetur adipiscing
                                         elit. Vivamus erat dolor, dictum id hendrerit
                                         ut, vulputate ut turpis. Curabitur lacus orci, vulputate
                                         vel nisi non, vulputate fermentum erat. Maecenas
                                         tempus elit nisl. Maecenas faucibus
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <a href="<?=APP_URL?>php/page/level.php" style="text-decoration:none">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <img class="img-responsive img-circle" src="<?=APP_IMG_URL?>/keahlian_saya.png" style="display:inline"/>
                                    <b> Daftar Keahlian </b>
                                </div>
                                <div class="panel-body">
                                    <p>
                                         Lorem ipsum dolor sit amet, consectetur adipiscing
                                         elit. Vivamus erat dolor, dictum id hendrerit
                                         ut, vulputate ut turpis. Curabitur lacus orci, vulputate
                                         vel nisi non, vulputate fermentum erat. Maecenas
                                         tempus elit nisl. Maecenas faucibus
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <a href="<?=APP_URL?>php/page/Kjadwal.php" style="text-decoration:none">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <img class="img-responsive img-circle" src="<?=APP_IMG_URL?>/atur_jadwal.png" style="display:inline"/>
                                    <b> Ketersediaan Jadwal </b>
                                </div>
                                <div class="panel-body">
                                    <p>
                                         Lorem ipsum dolor sit amet, consectetur adipiscing
                                         elit. Vivamus erat dolor, dictum id hendrerit
                                         ut, vulputate ut turpis. Curabitur lacus orci, vulputate
                                         vel nisi non, vulputate fermentum erat. Maecenas
                                         tempus elit nisl. Maecenas faucibus
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <a href="<?=APP_URL?>pengajar_kursus" style="text-decoration:none">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <img class="img-responsive img-circle" src="<?=APP_IMG_URL?>/jadwal_saya.png" style="display:inline"/>
                                    <b> Kursus Saya </b>
                                </div>
                                <div class="panel-body">
                                    <p>
                                         Lorem ipsum dolor sit amet, consectetur adipiscing
                                         elit. Vivamus erat dolor, dictum id hendrerit
                                         ut, vulputate ut turpis. Curabitur lacus orci, vulputate
                                         vel nisi non, vulputate fermentum erat. Maecenas
                                         tempus elit nisl. Maecenas faucibus
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <a href="<?=APP_URL?>daftar_rekening" style="text-decoration:none">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <img class="img-responsive img-circle" src="<?=APP_IMG_URL?>/daftar_rekening.png" style="display:inline"/>
                                    <b> Daftar Rekening Saya </b>
                                </div>
                                <div class="panel-body">
                                    <p>
                                         Lorem ipsum dolor sit amet, consectetur adipiscing
                                         elit. Vivamus erat dolor, dictum id hendrerit
                                         ut, vulputate ut turpis. Curabitur lacus orci, vulputate
                                         vel nisi non, vulputate fermentum erat. Maecenas
                                         tempus elit nisl. Maecenas faucibus
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <a href="<?=APP_URL?>withdraw" style="text-decoration:none">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <img class="img-responsive img-circle" src="<?=APP_IMG_URL?>/withdraw.png" style="display:inline"/>
                                    <b> Withdraw </b>
                                </div>
                                <div class="panel-body">
                                    <p>
                                         Lorem ipsum dolor sit amet, consectetur adipiscing
                                         elit. Vivamus erat dolor, dictum id hendrerit
                                         ut, vulputate ut turpis. Curabitur lacus orci, vulputate
                                         vel nisi non, vulputate fermentum erat. Maecenas
                                         tempus elit nisl. Maecenas faucibus
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
		</div><br/><br/>
        
        
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    	<script>
			function getContent(id){
	
				$(".tab_list").removeClass('active');
				$("#tabContent").html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
				if(id != 'msg_compose'){
					$("#tabContent").load("<?=APP_URL?>php/page/"+id+".php?q=<?=$enkripsi->encode($idmurid)?>");
				} else {
					$("#tabContent").load("<?=APP_URL?>"+id+"?q=<?=$enkripsi->encode($idmurid)?>&r=imurd");
				}
				$("#"+id).closest('li').addClass('active');

			}
			
			$('#mprofil').click();
		</script>
		<?=$session->getTemplate('footer')?>
        <div id="dialog-confirm">
          <p id="text_dialog"></p>
        </div>		
    </body>
</html>