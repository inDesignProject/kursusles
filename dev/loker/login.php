<?php
		
	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	$enkripsi	=	new Enkripsi();
	$db			=	new	Db_connection();
	$session	=	new Session();

	//CEK KIRIMAN AUTHRESULT
	switch($enkripsi->decode($_GET['authResult'])){
		//JIKA USERNAME DAN PASSWORDNYA TIDAK DITEMUKAN
		case	"1"	:	$pesan			=	"Username dan atau password salah";
						$username		=	$enkripsi->decode($_GET['u']);
						$password		=	$enkripsi->decode($_GET['p']);
						break;
		//JIKA AKAN MASUK KE INDEX TAPI BELUM LOGIN
		case	"2"	:	$pesan			=	"Anda belum masuk / login, silakan masukkan username dan password";
						$username		=	"";
						$password		=	"";
						break;
		//JIKA TIDAK MELAKUKAN AKTIVITAS SELAMA >= 1 JAM
		case	"3"	:	$pesan			=	"Anda tidak melakukan aktivitas selama lebih dari 1 jam. Silakan login ulang";
						$username		=	"";
						$password		=	"";
						break;
		//JIKA TIDAK MEMILIH LOGIN TYPE
		case	"4"	:	$pesan			=	"Anda belum memilih login sebagai <strong>Pencari Kerja</strong> atau <strong>Pemberi Kerja</strong>";
						$username		=	$enkripsi->decode($_GET['u']);
						$password		=	$enkripsi->decode($_GET['p']);
						break;
		//JIKA PERLU LOGIN UNTUK MELAKUKAN SESUATU
		case	"5"	:	$pesan			=	"Sebelum melanjutkan, silakan login terlebih dahulu";
						$username		=	"";
						$password		=	"";
						session_start();
						unset($_SESSION['KursusLesLoker']);
						break;
		default		:	$pesan			=	"Silakan isi <strong>Username</strong> dan <strong>Password</strong>";
						$username		=	"";
						$password		=	"";
						break;	
	}

	$rdr		=	$_GET['rdr'] == '' || !isset($_GET['rdr']) ? "" : "?rdr=".$_GET['rdr'];
	session_start();
	
	//SELECT LAST LOGIN ACTIVITY
	$sql		=	sprintf("SELECT A.DATETIME FROM log_login A
							 LEFT JOIN m_perusahaan B ON A.IDUSER = B.IDPERUSAHAAN
							 LEFT JOIN m_karyawan C ON A.IDUSER = C.IDKARYAWAN
							 WHERE B.SESSION_ID = '%s' OR C.SESSION_ID = '%s'
							 ORDER BY A.DATETIME DESC
							 LIMIT 0,10"
							, session_id()
							, session_id()
							);
	$result		=	$db->query($sql);
	
	if(!isset($result) || $result == false){
		$datalog		=	"Tidak ada data yang ditampilakan";
	} else {
		foreach($result as $key){
			$datalog	.=	"<small>
								<i class='fa fa-calendar'></i> 
								".date("d-m-Y", strtotime($key['DATETIME']))." 
								<i class='fa fa-clock-o'></i> 
								".date("H:i:s", strtotime($key['DATETIME']))." 
							 </small><br/>";
		}
	}
	
	if($session->cekSession() == 2){
		 echo "<script>window.location.href = '".APP_URL."index'</script>"; die();
	}

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

    	<?=$session->getTemplate('header','none')?>

        <div class="container">
        	<h3 class="text-left text_kursusles page-header">LOGIN</h3>
            <div class="row">
                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12"></div>
                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                    <div class="boxSquare">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                                    <strong><small id="login_msg"><?=$pesan?></small></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="boxSquare">
						<div class="panel panel-default">
                            <div class="panel-heading"><b>Username dan Password</b></div>
                            <div class="panel-body">
								<form action="auth" id="login" method="post">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            	<small><b>Status Anda Sebagai</b></small>
                                            </div>
                                        </div>
                                        <hr></hr>
                                        <div class="row">
                                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                                <input type="radio" id="login_type" name="login_type" value="1" <?=$_GET['t'] <> "1" ? "" : "checked"?> /> <small>Pemberi Kerja</small>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                                <input type="radio" id="login_type" name="login_type" value="2" <?=$_GET['t'] <> "2" ? "" : "checked"?> /> <small>Pencari Kerja</small>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            	<input type="text" name="username" id="username" class="form-control" maxlength="25" placeholder="Masukkan Username Anda" value="<?=$username?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		                                        <input type="password" name="password" id="password" class="form-control" maxlength="25" placeholder="Masukkan password" value="<?=$password?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <span class="devider"></span>
                        
                                    <div id="button_container">
                                        <input type="hidden" id="rdr" name="rdr" value="<?=$rdr?>" />
                                        <input type="submit" id="submit" name="submit" value="LogIn" class="btn btn-sm btn-custom2" />
                                        <input type="button" id="kembali" name="kembali" value="Kembali" onclick="history.back();" class="btn btn-sm btn-custom2" />
                                    </div>
                                </form>                            
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                    <div class="boxSquare">
						<div class="panel panel-default">
                            <div class="panel-heading"><b>Aktifitas Login Terakhir</b></div>
                            <div class="panel-body" style="text-align:center">
                               <?=$datalog?>
                        	</div>
						</div>
                    </div>
                </div>
                <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12"></div>
            </div>
		</div><br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

    	<?=$session->getTemplate('footer')?>

    </body>
</html>
