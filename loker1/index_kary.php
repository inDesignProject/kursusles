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
		$idkary		=	$enkripsi->decode($_GET['q']);
	} else {
		if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 2){
			echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
			die();
		} else {
			$idkary	=	$_SESSION['KursusLesLoker']['IDUSER'];
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
				text-align: right;
				font-weight: bold;
				width: 100%;
			}
		</style>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login)?>
		
		<div class="container">
			<h3 class="text-left text_kursusles page-header">HALAMAN UTAMA</h3>
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
					<div class="boxSquare">
						<div role="tabpanel">
							<ul class="nav nav-tabs" role="tablist" id="tab-con-nav">
                                <li role="presentation" class="active tab_list">
                                	<a href="#" id="profil" onclick="getContent(this.id)" aria-controls="profil" role="tab" data-toggle="tab">
                                    	Profil &nbsp; <i class="fa fa-user"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="bookmark" onclick="getContent(this.id)" aria-controls="bookmark" role="tab" data-toggle="tab">
                                    	Bookmark &nbsp; <i class="fa fa-bookmark"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="resume" onclick="getContent(this.id)" aria-controls="resume" role="tab" data-toggle="tab">
                                    	Resume Saya &nbsp; <i class="fa fa-newspaper-o"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="pendidikan" onclick="getContent(this.id)" aria-controls="pendidikan" role="tab" data-toggle="tab">
                                    	Data Pendidikan &nbsp; <i class="fa fa-graduation-cap"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="sertifikasi" onclick="getContent(this.id)" aria-controls="sertifikasi" role="tab" data-toggle="tab">
                                    	Sertifikasi &nbsp; <i class="fa fa-certificate"></i>
                                    </a>
                                </li>
                                <li role="presentation" class="tab_list">
                                	<a href="#" id="pengalaman" onclick="getContent(this.id)" aria-controls="pengalaman" role="tab" data-toggle="tab">
                                    	Pengalaman Kerja &nbsp; <i class="fa fa-file-text"></i>
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
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141214'.'jssajax');?>.jsfile"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    	<script>
			function getContent(id){
	
				$(".tab_list").removeClass('active');
				$("#tabContent").html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
				$("#tabContent").load("<?=APP_URL?>karyawan/"+id+".php?q=<?=$enkripsi->encode($idkary)?>");
				$("#"+id).closest('li').addClass('active');

			}
			$('#profil').click();
		</script>
		<?=$session->getTemplate('footer')?>
        <div id="dialog-confirm">
          <p id="text_dialog"></p>
        </div>		
    </body>
</html>