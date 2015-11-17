<?php

include('php/lib/db_connection.php');
include('php/lib/enkripsi.php');
include('php/lib/session.php');
require "php/lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

session_start();

$sql		=	sprintf("UPDATE m_user SET SESSION_ID = NULL WHERE SESSION_ID = '%s'", session_id());
$affected	=	$db->execSQL($sql, 0);
		
//JIKA DATA SUDAH MASUK, KIRIM RESPON
if($affected > 0){
	unset($_SESSION['KursusLes']);
	session_destroy();
}

echo "<script>window.location.href = 'index';</script>";

?>