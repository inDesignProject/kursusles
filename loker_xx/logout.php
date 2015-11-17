<?php

include('php/lib/db_connection.php');
include('php/lib/enkripsi.php');
include('php/lib/session.php');
require "php/lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

session_start();

$table		=	$_SESSION['KursusLesLoker']['TYPEUSER'] == "1" ? "m_perusahaan" : "m_karyawan";
$field		=	$_SESSION['KursusLesLoker']['TYPEUSER'] == "1" ? "IDPERUSAHAAN" : "IDKARYAWAN";

$sql		=	sprintf("UPDATE %s SET SESSION_ID = NULL WHERE SESSION_ID = '%s'", $table, session_id());
$affected	=	$db->execSQL($sql, 0);
		
//JIKA DATA SUDAH MASUK, KIRIM RESPON
if($affected > 0){
	unset($_SESSION['KursusLesLoker']);
	session_destroy();
}

echo "<script>window.location.href = 'index';</script>";

?>