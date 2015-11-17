<?php

include('php/lib/db_connection.php');
include('php/lib/enkripsi.php');
include('php/lib/session.php');
require "php/lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

if($session->cekSession() == 2){
	 echo "<script>window.location.href = '".APP_URL."index'</script>"; die();
}

//CEK MEMILIH LOGIN TYPE ATAU TIDAK
if($_POST['login_type'] == ""  && !isset($_POST['login_type']) && $_GET['login_type'] == ""  && !isset($_GET['login_type'])){

	header("Location: login?authResult=".$enkripsi->encode('4')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password'])."&t=&rdr=".$_POST['rdr']);
	die();

}
	
//DEFINISI VARIABEL (ASAL BISA DARI AKTIVASI PENGAJAR, AKTIVASI MURID ATAU LOGIN NORMAL)
$type		=	$_POST['login_type'] == ""  && !isset($_POST['login_type']) ? $_GET['login_type'] : $_POST['login_type'];
$username	=	$_GET['u'] == "" && !isset($_GET['u']) ? "USERNAME = '".$_POST['username']."'" : "IDUSER = '".$enkripsi->decode($_GET['u'])."'";
$password	=	$_GET['p'] == "" && !isset($_GET['p']) ? md5($_POST['password']) : $_GET['p'];

//QUERY SELECT USER CHILD
$sqlUser	=	sprintf("SELECT IDUSER, IDUSER_CHILD
						 FROM m_user 
						 WHERE %s AND PASSWORD = '%s' AND IDLEVEL = %s
						 LIMIT 0,1"
						 , $username
						 , $password
						 , $type
				);
$resultUser	=	$db->query($sqlUser);

$iduserChild=	$resultUser[0]['IDUSER_CHILD'];
$iduserPrime=	$resultUser[0]['IDUSER'];

//JIKA USER TERDAFTAR
if($iduserChild > 0 && $iduserChild <> ''){

	session_start();
	unset($_SESSION['KursusLes']);
	session_destroy();

	//JIKA TIPENYA SEBAGAI MURID
	if($type == 2){
		
		//REGISTER SESSION DAN UPDATE LAST LOGIN
		$session->registerSessionMurid($iduserPrime, $iduserChild);
		$setSession		=	$session->setSession();
		
		//JIKA BERHASIL UPDATE, MASUK KE INDEX PENGAJAR
		if($setSession){

			if($_POST['rdr'] == '' || !isset($_POST['rdr'])){
				header("Location: index_murid");
			} else {
				$loc	=	str_replace(";;","&",$_POST['rdr']);
				$loc	=	str_replace(";","?",$loc);
				$loc	=	str_replace("?rdr=","",$loc);
				header("Location: ".$loc);
			}
			die();

		//JIKA TIDAK, KEMBALI KE HALAMAN LOGIN
		} else {
		
			header("Location: login?authResult=".$enkripsi->encode('1')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password'])."&t=".$type."&rdr=".$_POST['rdr']);
			die();
		
		}
	
	//JIKA TIPENYA SEBAGAI PENGAJAR
	} else {

		//REGISTER SESSION DAN UPDATE LAST LOGIN
		$session->registerSessionPengajar($iduserPrime, $iduserChild);
		$setSession		=	$session->setSession();
		
		//JIKA BERHASIL UPDATE, MASUK KE INDEX PENGAJAR
		if($setSession){

			if($_POST['rdr'] == '' || !isset($_POST['rdr'])){
				header("Location: index_pengajar");
			} else {
				$loc	=	str_replace(";;","&",$_POST['rdr']);
				$loc	=	str_replace(";","?",$loc);
				$loc	=	str_replace("?rdr=","",$loc);
				header("Location: ".$loc);
			}
			die();

		//JIKA TIDAK, KEMBALI KE HALAMAN LOGIN
		} else {
		
			header("Location: login?authResult=".$enkripsi->encode('1')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password'])."&t=".$type."&rdr=".$_POST['rdr']);
			die();
		
		}
	
	}

//JIKA TERNYATA DATA USER TIDAK ADA, MAKA KEMBALI KE HALAMAN LOGIN
} else {

	echo "<script>window.location.href = 'login?authResult=".$enkripsi->encode('1')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password'])."&t=".$type."&rdr=".$_POST['rdr']."';</script>";

}

?>