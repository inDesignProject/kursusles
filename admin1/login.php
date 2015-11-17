<?php
	include('php/include/enkripsi.php');
	include('php/include/session.php');
	include('php/lib/db_connection.php');
	include('php/lib/recaptchalib.php');
	require "php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	if($session->cekSession() == 2){
		 echo "<script>window.location.href = '".APP_URL."index'</script>";
		 die();
	}

	if(isset($_GET['authResult']) && $_GET['authResult'] <> ''){
		switch($enkripsi->decode($_GET['authResult'])){
			case "0"	:	$pesan	=	"Anda belum masuk"; break;
			case "1"	:	$pesan	=	"Anda belum masuk atau tidak melakukan aktivitas lebih dari 1 jam"; break;
			case "3"	:	$pesan	=	"Masukkan username dan password"; break;
			case "4"	:	$pesan	=	"Username dan atau Password salah"; break;
			case "5"	:	$pesan	=	"Silakan ulangi lagi"; break;
			case "6"	:	$pesan	=	"Verifikasi bahwa anda bukan robot dengan klik pada kotak Recaptcha"; break;
			case "7"	:	$pesan	=	"Captcha yang anda masukkan tidak berlaku"; break;
			case "8"	:	$pesan	=	"Perubahan pengaturan akun disimpan. Silakan Login ulang menggunakan data akun perubahan"; break;
			default		:	$pesan	=	"Anda belum masuk"; break;
		}
	}
	
	if(isset($_GET['u']) && $_GET['u'] <> ''){
		$username	=	$enkripsi->decode($_GET['u']);
	} else {
		$username	=	'';
	}
	
	if(isset($_GET['p']) && $_GET['p'] <> ''){
		$password	=	$enkripsi->decode($_GET['p']);
	} else {
		$password	=	'';
	}
?>
<html>
<head>
    <meta charset="utf-8">
    <title>Login Admin | KursusLes</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
	<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmainorigin');?>.cssfile">
</head>
<body>

	<?php if($pesan <> ""){ ?>
        <div class="alert alert-success alert-dismissible" role="alert" style="text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <strong><small id="login_msg"><?=$pesan?></small></strong>
        </div>
    <? } ?>
    <div class="row" style="margin: 0 !important">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"></div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="boxSquare">
                <div class="panel panel-default">
                    <div class="panel-heading"><b>LOGIN</b></div>
                    <div class="panel-body">
                        <form action="auth" id="login" method="post">
                            <div class="form-group">
                                <input name="username" id="username" class="form-control" maxlength="25" placeholder="Masukkan username" type="text" value="<?=$username?>">
                            </div>
                            <div class="form-group">
                                <input name="password" id="password" class="form-control" maxlength="25" placeholder="Masukkan password" type="password" value="<?=$password?>">
                            </div>
                                
							<div class="form-group">
                                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
                                <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script>
							</div>

                            <span class="devider"></span>
                
                            <div id="button_container">
                                <input id="submit" name="submit" value="Login" class="btn btn-sm btn-custom2" type="submit">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"></div>
    </div>
	<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
</body>
</html>