<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	//JIKA LAMA TIDAK MELAKUKAN AKTIFITAS
	if($session->cekSession() == 1){

		header("Location: login?authResult=".$enkripsi->encode('3')."&t=2");
		die();
	
	//JIKA NORMAL
	} else if($session->cekSession() == 2){
		
		header('Content-type: text/html; charset=utf-8');

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
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssindexpengajar');?>.cssfile">
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

    	<?=$session->getTemplate('header')?>
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
        
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
    	<?=$session->getTemplate('footer')?>

    </body>
</html>
<?php
	} else {
		header("Location: login?authResult=".$enkripsi->encode('2')."&t=2");
		die();
	}
?>