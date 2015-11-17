<?php

include('../lib/db_connection.php');
include('../lib/enkripsi.php');
include('../lib/session.php');
require "../lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

session_start();
$idmurid	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;

//AMBIL DATA BOOKMARK
$sql		=	sprintf("SELECT A.IDJOIN,
								IF(A.TYPE = 1, B.NAMA, C.NAMA_PAKET) AS NAMA,
								IF(A.TYPE = 1, B.FOTO, C.JENIS) AS PARAM1,
								IF(A.TYPE = 1, B.ALAMAT, C.JUMLAH_MURID) AS PARAM2,
								IF(A.TYPE = 1, B.EMAIL, C.WAKTU) AS PARAM3,
								IF(A.TYPE = 1, B.TELPON, C.HARGA) AS PARAM4,
								IF(A.TYPE = 1, '', D.IDPENGAJAR) AS PARAM5,
								A.TYPE
						 FROM t_bookmark A
						 LEFT JOIN m_pengajar B ON A.IDJOIN = B.IDPENGAJAR
						 LEFT JOIN t_paket C ON A.IDJOIN = C.IDPAKET
						 LEFT JOIN t_mapel_pengajar D ON C.IDMAPELPENGAJAR = D.IDMAPELPENGAJAR
						 WHERE A.IDMURID = %s
						 ORDER BY A.TGL_INSERT DESC"
						, $idmurid
						);
$result		=	$db->query($sql);

if($result <> false && $result <> ""){
	
	foreach($result as $key){
		
		$type	=	$key['TYPE'];
		$idjoin	=	$enkripsi->encode($key['IDJOIN']);
		$data	.=	"";
		if($key['TYPE'] == 1){
			$foto	=	$enkripsi->encode($key['PARAM1']);
			$data	.=	"    <div class='row'>
								<div class='col-lg-2 col-md-2 col-sm-2 col-xs-12'>
									<img src='".APP_IMG_URL."generate_pic.php?type=pr&q=".$foto."&w=90&h=90' class='img-circle pull-right'>
								</div>
								<div class='col-lg-10 col-md-10 col-sm-10 col-xs-12'>
									<h5><b>".$key['NAMA']."</b></h5>
									<i class='fa fa-home'></i><small> ".$key['PARAM2']."</small><br />
									<i class='fa fa-envelope'></i><small> ".$key['PARAM3']."</small><br />
									<i class='fa fa-phone'></i><small> ".$key['PARAM4']."</small>
									<a href='pengajar_profil?q=".$idjoin."' class='btn btn-custom btn-xs pull-right' target='_blank'><i class='fa fa-user'></i> Kunjungi Profil >> </a>
								</div>
							</div>
							<hr />";
						 
		} else {
			$jenis	=	$key['PARAM1'] == "1" ? "Privat (1 Murid)" : "Grup (".$key['PARAM2']." Murid)";
			$class	=	$key['PARAM1'] == "1" ? "user" : "group";
			$idp	=	$enkripsi->encode($key['PARAM5']);
			$data	.=	"    <div class='row'>
								<div class='col-lg-2 col-md-2 col-sm-2 col-xs-12'>
									<img src='".APP_IMG_URL."icon/paket.png' class='img-circle pull-right'>
								</div>
								<div class='col-lg-10 col-md-10 col-sm-10 col-xs-12'>
									<h5><b>".$key['NAMA']."</b></h5>
									<i class='fa fa-".$class."'></i><small> ".$jenis."</small><br />
									<i class='fa fa-clock-o'></i><small> ".$key['PARAM3']." Jam</small><br />
									<i class='fa fa-dollar'></i><small> ".number_format($key['PARAM4'],0,',','.').",-</small>
									<a href='detail_paket?q=".$idp."&idp=".$idjoin."' class='btn btn-custom btn-xs pull-right' target='_blank'><i class='fa fa-book'></i> Kunjungi Laman >> </a>
								</div>
							</div>
							<hr />";
		}
		
	}
	
} else {
	$data	=	"<tr id='rownodata'><td colspan ='7'><center>Tidak ada data</center></td></tr>";
}
?>
<div class="boxSquareWhite">
    <h4>Data Bookmark</h4>
    <?=$data?>
</div>
<script>

</script>