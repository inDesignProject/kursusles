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
	
	//FUNGSI DIGUNAKAN VERIFIKASI
	if( $enkripsi->decode($_GET['func']) == "setVerf" && isset($_GET['func'])){
		
		$idpengajar	=	$enkripsi->decode($_POST['id']);
		$status		=	$_POST['status'] == "true" ? "1" : "0";
		
		$sqlUpd		=	sprintf("UPDATE m_pengajar
								 SET VERIFIED_STATUS = %s 
								 WHERE IDPENGAJAR = %s"
								 , $status
								 , $idpengajar);
		$affected	=	$db->execSQL($sqlUpd, 0);
		
		if($affected > 0){
			$sqlLog	=	sprintf("INSERT INTO log_verifikasi
								 (IDPENGAJAR, IDUSERAPPROVE, TGL_APPROVE, STATUS)
								 VALUES
								 (%s, %s, NOW(), %s)"
								, $idpengajar
								, $_SESSION['KursusLesAdmin']['IDUSER']
								, $status
								);
			$db->execSQL($sqlLog, 1);
			echo "00000";
		} else {
			echo "00001";
		}
		die();
	}
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA
		$sql		=	sprintf("SELECT NAMA, ALAMAT, JNS_KELAMIN, TELPON, TGL_LAHIR, EMAIL, IDPENGAJAR
								 FROM m_pengajar
								 WHERE VERIFIED_STATUS = -1"
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDPENGAJAR) AS TOTDATA FROM (%s) AS A", $sql);
		$resultC	=	$db->query($sqlCount);
		$resultC	=	$resultC[0];
		$totData	=	$resultC['TOTDATA'];
		$totpage	=	ceil($totData / $dataperpage);

		$sqlSel		=	sprintf("SELECT * FROM (%s) AS A 
								 LIMIT %s, %s"
								, $sql
								, $startLimit
								, $dataperpage
								);
		$result		=	$db->query($sqlSel);
		$data		=	'';
		
		if($result <> '' && $result <> false){
			
			$i			=	1;
			$startData	=	$startLimit + 1;
			if(($startLimit + $dataperpage) > $totData){
				$endData	=	$totData;
			} else {
				$endData	=	$startLimit + $dataperpage;
			}
			
			if($totpage == 1){
				$pagination	=	"<li class='active'><a href='#'>1</a></li>";
			} else {
				
				if($_POST['page'] <> 1 && $totpage > 1){
					$prevPage	=	($_POST['page'] *1) - 1;
					$pagination	.=	"	<li class='previous' onClick='filterData(".$prevPage.")'><a href='#'>&laquo;</a></li>";
				}
				
				for($i=1; $i<=$totpage; $i++){
					if($i == $_POST['page']){
						$pagination	.=	"	<li><a href='#' class='current'> ".$i."</a></li>";
					} else {
						$pagination	.=	"	<li onClick='filterData(".$i.")'><a href='#'>".$i."</a></li>";
					}
				}

				if($_POST['page'] <> $totpage){
					$nextPage	=	($_POST['page'] *1) + 1;
					$pagination	.=	"	<li onClick='filterData(".$nextPage.")'><a href='#'>&raquo;</a></li>";
				}
				
			}
			
			foreach($result as $key){
				$JK		=	$result['JNS_KELAMIN'] == "1" ? "L" : "P";
				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDPENGAJAR'])."'>
									<td>".$key['NAMA']."</td>
									<td>".$key['ALAMAT']."</td>
									<td align='center'>".$JK."</td>
									<td>".$key['TELPON']."</td>
									<td align='center'>".$key['TGL_LAHIR']."</td>
									<td>".$key['EMAIL']."</td>
									<td align='center'>
										<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='getDetail(\"".$enkripsi->encode($key['IDPENGAJAR'])."\")'>
											Detail <i class='fa fa-chevron-circle-down'></i>
										</a>
									</td>
								</tr>";
				$i++;
			}
		} else {
			$data		=	"	<tr><td colspan = '7'><center><b>Tidak ada data yang belum diverifikasi</b></center></td></tr>";
			$startData	=	0;
			$endData	=	0;
		}
		
		echo json_encode(array("totData"=>$totData *1, "respon"=>$data.$sql, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}
	
	//FUNGSI DIGUNAKAN LIHAT DETAIL
	if( $enkripsi->decode($_GET['func']) == "getDetail" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['id']);
		$sql		=	sprintf("SELECT A.NAMA, A.ALAMAT, A.TELPON, A.TGL_LAHIR, B.NAMA_KOTA AS KOTA_LAHIR,
										C.NAMA_KOTA AS KOTA_TINGGAL, D.NAMA_KECAMATAN, E.NAMA_KELURAHAN, 
										A.KODEPOS, A.EMAIL, A.KTP, A.IDPENGAJAR
								 FROM m_pengajar A
								 LEFT JOIN m_kota B ON A.IDKOTA_LAHIR = B.IDKOTA
								 LEFT JOIN m_kota C ON A.IDKOTA_TINGGAL = C.IDKOTA
								 LEFT JOIN m_kecamatan D ON A.IDKECAMATAN = D.IDKECAMATAN
								 LEFT JOIN m_kelurahan E ON A.IDKELURAHAN = E.IDKELURAHAN
								 WHERE A.IDPENGAJAR = %s
								 LIMIT 0,1"
								, $iddata
								);
		$result		=	$db->query($sql);
		$result		=	$result[0];
		
		echo "	<tr class='detail-con'>
					<td style='padding: 24px;' colspan='7'>
						<h4>Detail Identitas</h4>
						<table class='table-noborder'>
							<tr>
								<td rowspan='10'>
									<img src='".KURSUSLES_IMG_URL."generate_pic.php?type=ktp&q=".$enkripsi->encode($result['KTP'])."'/>
								</td>
							</tr>
							<tr>
								<td>Nama</td>
								<td>:</td>
								<td>".$result['NAMA']."</td>
							</tr>
							<tr>
								<td>Alamat</td>
								<td>:</td>
								<td>".$result['ALAMAT']."</td>
							</tr>
							<tr>
								<td>Kelurahan</td>
								<td>:</td>
								<td>".$result['NAMA_KELURAHAN']."</td>
							</tr>
							<tr>
								<td>Kecamatan</td>
								<td>:</td>
								<td>".$result['NAMA_KECAMATAN']."</td>
							</tr>
							<tr>
								<td>Kota</td>
								<td>:</td>
								<td>".$result['KOTA_TINGGAL']."</td>
							</tr>
							<tr>
								<td>Kode Pos</td>
								<td>:</td>
								<td>".$result['KODEPOS']."</td>
							</tr>
							<tr>
								<td>Tempat Lahir</td>
								<td>:</td>
								<td>".$result['KOTA_LAHIR']."</td>
							</tr>
							<tr>
								<td>Tgl Lahir</td>
								<td>:</td>
								<td>".$result['TGL_LAHIR']."</td>
							</tr>
							<tr>
								<td>Email</td>
								<td>:</td>
								<td>".$result['EMAIL']."</td>
							</tr>
							<tr>
								<td colspan='4' align='right'>
									<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='setVerf(\"".$enkripsi->encode($result['IDPENGAJAR'])."\", true)'>
										Verifikasi <i class='fa fa-check'></i>
									</a>
									<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px; margin-right: 5px;' onclick='setVerf(\"".$enkripsi->encode($result['IDPENGAJAR'])."\", false)'>
										Tolak <i class='fa fa-times'></i>
									</a>
								</td>
							</tr>
						</table>
					</td>
				</tr>";
		die();
	}
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-desktop"></i> Monitoring</a></li>
        <li>Verifikasi</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Verifikasi Pengajar</h1>
</div>
<div id="contentwrapper" class="elements">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="table_data" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head1" style="text-align:center;">Nama</td>
                            <td class="head0" style="text-align:center;">Alamat</td>
                            <td class="head1" style="text-align:center;">JK</td>
                            <td class="head0" style="text-align:center;">Telpon</td>
                            <td class="head1" style="text-align:center;">Tgl Lahir</td>
                            <td class="head0" style="text-align:center;">Email</td>
                            <td class="head1" style="text-align:center;" width="75"></td>
                        </tr>
                    </thead>
                    <tbody id="list_data">
                    </tbody>
				</table><br/>
                <div style="float:right">
                    <ul class="pagination" id="pagination" >
                    </ul>
                </div><br/><br/>
            </div>
        </div>
    </div>
</div>
<style>
.table-noborder tr td{border: none !important}
</style>
<script>
	function filterData(page){
		$('#list_data').html("<tr><td colspan = '7'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/403verfpengajar.php?func=<?=$enkripsi->encode('filterData')?>", {page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_data').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
		return true;
	}
	function getDetail(id){
		$('.detail-con').slideUp('fast').remove();
		$.post( "<?=APP_URL?>page/403verfpengajar.php?func=<?=$enkripsi->encode('getDetail')?>", {id:id})
		.done(function( data ) {
			$('#baris'+id).after(data);
			
		});
	}
	function setVerf(id, status){

		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		$.post( "<?=APP_URL?>page/403verfpengajar.php?func=<?=$enkripsi->encode('setVerf')?>", {id:id, status:status})
		.done(function( data ) {
			
			if(status == true){ket = 'Memverifikasi'} else {ket = 'Menolak Verifikasi'}
			if(data == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg('Berhasil '+ket));
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg('Gagal '+ket+'. Silakan coba lagi nanti'));
			}
			
		});

	}
	$(document).ready(function(){
		filterData(1);
	});
</script>