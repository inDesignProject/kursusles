<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";

	//HABIS - CEK SESSION
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
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>
        
		<br/><br/><div class="container">
            <div class="boxSquare">
                <div class="row">
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                        <img src="<?=APP_IMG_URL?>pemberi_kerja.png"/>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
                        <h3 style="margin-top: 4px !important;">Daftar sebagai pemberi kerja</h3>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus erat dolor, dictum id hendrerit ut, vulputate ut turpis. Curabitur lacus orci, vulputate vel nisi non, vulputate fermentum erat. Maecenas tempus elit nisl. Maecenas faucibus condimentum turpis, id sagittis nibh dignissim sit amet. Etiam eu venenatis nulla. Quisque id diam bibendum risus mattis tristique in in enim. Sed varius nulla at aliquet commodo. 
                        </p>
                        <a href="signup_pemberi" class="btn btn-custom btn-xs pull-right">Daftar <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
        	</div>
		</div>
		<br/><br/>
		<div class="container">
            <div class="boxSquare">
                <div class="row">
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                        <img src="<?=APP_IMG_URL?>pencari_kerja.png"/>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
                        <h3 style="margin-top: 4px !important;">Daftar sebagai pencari kerja</h3>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus erat dolor, dictum id hendrerit ut, vulputate ut turpis. Curabitur lacus orci, vulputate vel nisi non, vulputate fermentum erat. Maecenas tempus elit nisl. Maecenas faucibus condimentum turpis, id sagittis nibh dignissim sit amet. Etiam eu venenatis nulla. Quisque id diam bibendum risus mattis tristique in in enim. Sed varius nulla at aliquet commodo. 
                        </p>
                        <a href="signup_pencari" class="btn btn-custom btn-xs pull-right">Daftar <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
        	</div>
		<br/><br/></div>
        
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
    	<?=$session->getTemplate('footer')?>

    </body>
</html>