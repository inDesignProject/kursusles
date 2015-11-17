<?php
include('php/include/enkripsi.php');
include('php/include/session.php');
include('php/lib/db_connection.php');
require "php/include/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

session_start();

$sql		=	sprintf("UPDATE admin_user SET SESSION_ID = NULL WHERE SESSION_ID = '%s'", session_id());
$affected	=	$db->execSQL($sql, 0);
		
//JIKA DATA SUDAH MASUK, KIRIM RESPON
if($affected > 0){
	unset($_SESSION['KursusLesAdmin']);
	session_destroy();
}
$autResult	=	$_GET['authResult'] == '' || !isset($_GET['authResult']) ? $enkripsi->encode('3') : $_GET['authResult'];
echo "<script>window.location.href = 'login?authResult=".$autResult."';</script>";

?>