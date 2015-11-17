<?php

include('php/lib/db_connection.php');
include('php/lib/enkripsi.php');
include('php/lib/session.php');
require "php/lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

$iduser		=	$enkripsi->decode($_GET['u']);
$password	=	$_GET['p'];
$table		=	$_GET['tu'] == '1' ? "m_perusahaan" : "m_karyawan";
$field		=	$_GET['tu'] == '1' ? "IDPERUSAHAAN" : "IDKARYAWAN";

$sql	=	sprintf("SELECT %s FROM %s WHERE %s = %s", $field, $table, $field, $iduser);
$result	=	$db->query($sql);

//JIKA DATA KIRIMAN TIDAK KOSONG	
if($iduser <> ''){

	//JIKA ADA KIRIMAN DATA
	if($enkripsi->decode($_GET['t']) == "resetPwd" && isset($_GET['t'])){
		
		$sql		=	sprintf("UPDATE %s SET PASSWORD = '%s' WHERE %s = %s", $table, $password, $field, $iduser);
		$affected	=	$db->execSQL($sql, 0);
		
		if($affected){
			
			$results=	"Reset akun berhasil.<br />
						 Harap tunggu, sedang meneruskan ke halaman profil...";
			$redir	=	"<script>window.location.href = 'auth?u=".$_GET['u']."&p=".$password."&login_type=".$_GET['tu']."';</script>";
			$header	=	"Content-type: text/html; charset=utf-8";
			
		} else {
			
			//TUTUP AKSES YANG MENGGUNAKAN URL
			$results=	"Halaman tidak ditemukan";
			$redir	=	"";
			$header	=	"HTTP/1.1 404 Not Found";
			
		}
		
	} else {
		
		$sql		=	sprintf("UPDATE %s SET STATUS = 1 WHERE %s = %s AND PASSWORD = '%s'", $table, $field, $iduser, $password);
		$affected	=	$db->execSQL($sql, 0);

		if($affected){
			
			$results=	"Selamat! anda resmi bergabung.<br />
						 Selanjutnya anda bisa mengatur profil dan melengkapi data.<br /><br />
						 Harap tunggu, sedang meneruskan ke halaman profil...";
					
			$redir	=	"<script>window.location.href = 'auth?u=".$_GET['u']."&p=".$password."&login_type=".$_GET['tu']."';</script>";
			$header	=	"Content-type: text/html; charset=utf-8";
			
		} else {
			
			//TUTUP AKSES YANG MENGGUNAKAN URL
			$results=	"Halaman tidak ditemukan";
			$redir	=	"";
			$header	=	"HTTP/1.1 404 Not Found";
			
		}
	}

} else {

	//TUTUP AKSES YANG MENGGUNAKAN URL
	$results=	"Halaman tidak ditemukan";
	$redir	=	"";
	$header	=	"HTTP/1.1 404 Not Found";

}

//CEK SESSION UNTUK FORM LOGIN
$show_login		=	$session->cekSession() == 2 ? "false" : "true";
//HABIS - CEK SESSION

header($header);

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

    	<?=$session->getTemplate('header', $show_login)?>
		
		<div class="container">
			<h3 class="text-left text_kursusles page-header">AKTIVASI PENDAFTARAN</h3>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="boxSquare segmenDaftar">
                    	<center><b><?=$results?></b></center>
                    </div>
                </div>
            </div>
        </div><br/>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
    	<?=$session->getTemplate('footer')?>
        <?php
			sleep(3);
			echo $redir;
		?>

    </body>
</html>