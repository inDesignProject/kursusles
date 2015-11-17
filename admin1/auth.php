<?php

include('php/lib/db_connection.php');
include('php/include/enkripsi.php');
include('php/include/session.php');
require "php/include/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

if($session->cekSession() == 2){
	 echo "<script>window.location.href = '".APP_URL."index'</script>"; die();
}

//CEK MEMILIH LOGIN TYPE ATAU TIDAK
if($_POST['username'] == ""  || !isset($_POST['username']) || $_POST['password'] == ""  || !isset($_POST['password'])){

	header("Location: login?authResult=".$enkripsi->encode('3')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password']));
	die();

}

//CEK GOOGLE RECAPTCHA
if($_POST['g-recaptcha-response'] == "" || !isset($_POST['g-recaptcha-response'])){
	header("Location: login?authResult=".$enkripsi->encode('6')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password']));
	die();
} else {
	$cekdata	=	$session->cekGCaptcha($_POST['g-recaptcha-response']);
	if($cekdata <> 2){
		header("Location: login?authResult=".$enkripsi->encode('7')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password']));
		die();
	}
}
	
//DEFINISI VARIABEL (ASAL BISA DARI AKTIVASI PENGAJAR, AKTIVASI MURID ATAU LOGIN NORMAL)
$username	=	$_POST['username'];
$password	=	md5($_POST['password']);

//QUERY SELECT USER
$sqlUser	=	sprintf("SELECT IDUSER
						 FROM admin_user 
						 WHERE USERNAME = '%s' AND PASSWORD = '%s'
						 LIMIT 0,1"
						 , $username
						 , $password
				);
$resultUser	=	$db->query($sqlUser);
$iduser		=	$resultUser[0]['IDUSER'];

//JIKA USER TERDAFTAR
if($iduser > 0 && $iduser <> ''){

	session_start();
	unset($_SESSION['KursusLesAdmin']);
	session_destroy();

	//REGISTER SESSION DAN UPDATE LAST LOGIN
	$session->registerSession($iduser);
	$setSession		=	$session->setSession();
	
	//JIKA BERHASIL UPDATE, MASUK KE INDEX
	if($setSession){

		header("Location: index");
		die();

	//JIKA TIDAK, KEMBALI KE HALAMAN LOGIN
	} else {
	
		header("Location: login?authResult=".$enkripsi->encode('5')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password']));
		die();
	
	}

//JIKA TERNYATA DATA USER TIDAK ADA, MAKA KEMBALI KE HALAMAN LOGIN
} else {

	echo "<script>window.location.href = 'login?authResult=".$enkripsi->encode('4')."&u=".$enkripsi->encode($_POST['username'])."&p=".$enkripsi->encode($_POST['password'])."';</script>";

}

?>