<?php
	include('../php/include/enkripsi.php');
	include('../php/include/session.php');
	include('../php/lib/db_connection.php');
	require "../php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	if($session->cekSession() <> 2 && !isset($_GET['func'])){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode($session->cekSession())."'</script>";
		 die();
	}
	
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-wrench"></i> Pengaturan</a></li>
        <li>Voucher & Withdraw</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Variabel Pengaturan Voucher dan Withdraw</h1>
</div>
<div id="contentwrapper" class="elements">
    
</div>