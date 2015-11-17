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
	
	//FUNGSI DIGUNAKAN EXPORT DATA
	if( $enkripsi->decode($_GET['func']) == "exportData" && isset($_GET['func'])){
		
		if(isset($_POST['status']) && $_POST['status'] <> ''){
			$conStatus	=	$_POST['status'] == 3 ? "1=1" : "C.STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		$sql			=	sprintf("SELECT A.NAMA, A.ALAMAT, A.JNS_KELAMIN, A.TELPON, A.TGL_LAHIR, 
											B.NAMA_KOTA AS NAMA_KOTA_LAHIR, B1.NAMA_KOTA AS NAMA_KOTA_TINGGAL,
											D.NAMA_KECAMATAN, E.NAMA_KELURAHAN, A.KODEPOS, A.EMAIL, 
											A.DEPOSIT, C.TGL_DAFTAR, C.STATUS
									 FROM m_murid A
									 LEFT JOIN m_kota B ON A.IDKOTA_LAHIR = B.IDKOTA
									 LEFT JOIN m_kota B1 ON A.IDKOTA_TINGGAL = B1.IDKOTA
									 LEFT JOIN m_user C ON A.IDMURID = C.IDUSER_CHILD
									 LEFT JOIN m_kecamatan D ON A.IDKECAMATAN = D.IDKECAMATAN
									 LEFT JOIN m_kelurahan E ON A.IDKELURAHAN = E.IDKELURAHAN
									 WHERE C.IDLEVEL = 2 AND %s
									 ORDER BY C.STATUS, A.NAMA"
									, $conStatus
									);
		$result			=	$db->query($sql);
										
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment;Filename=DataMurid".date('dmY').".xls");
		$i			= 1;
		
		echo "	<center><strong>LAPORAN DATA MURID</strong></center>
				<center><strong>PER TANGGAL : ".date('d-m-Y')."</strong></center>
				<br/><br/>
				<table style='width: 100%;' border='1'>
					<thead>
						<tr align='center'>
							<td >No.</td>
							<td >NAMA</td>
							<td >ALAMAT</td>
							<td >JENIS KELAMIN</td>
							<td >TELPON</td>
							<td >TANGGAL LAHIR</td>
							<td >KOTA LAHIR</td>
							<td >KOTA TINGGAL</td>
							<td >KECAMATAN</td>
							<td >KELURAHAN</td>
							<td >KODE POS</td>
							<td >EMAIL</td>
							<td >DEPOSIT</td>
							<td >TGL DAFTAR</td>
							<td >STATUS AKUN</td>
						</tr>
					</thead> ";
		if($result <> '' && $result <> false){
			foreach($result as $key){
				
				$jeniskelamin	=	$key['JNS_KELAMIN'] == "1" ? "Laki-laki" : "Perempuan";
				
				switch($key['STATUS']){
					case "0"	:	$statusakun		=	"Belum Konfirmasi Pendaftaran"; break;
					case "1"	:	$statusakun		=	"Aktif"; break;
					case "2"	:	$statusakun		=	"Tidak Aktif"; break;
					default		:	$statusakun		=	"Tidak Diketahui"; break;
				}
				
				echo "<tr>
						<td>".$i."</td>
						<td>".$key["NAMA"]."</td>
						<td>".$key["ALAMAT"]."</td>
						<td>".$jeniskelamin."</td>
						<td> ".$key["TELPON"]."</td>
						<td>".$key["TGL_LAHIR"]."</td>
						<td>".$key["NAMA_KOTA_LAHIR"]."</td>
						<td>".$key["NAMA_KOTA_TINGGAL"]."</td>
						<td>".$key["NAMA_KECAMATAN"]."</td>
						<td>".$key["NAMA_KELURAHAN"]."</td>
						<td>".$key["KODEPOS"]."</td>
						<td>".$key["EMAIL"]."</td>
						<td>".$key["DEPOSIT"]."</td>
						<td>".$key["TGL_DAFTAR"]."</td>
						<td>".$statusakun."</td>
					</tr>
				\n";
			$i++;
			}
		} else {
			echo "<tr><td colspan='14'><center>Tidak ada data yang ditampilkan</center></td></tr>";
		}

		echo "</table>";
		die();

	}
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		if(isset($_POST['status']) && $_POST['status'] <> ''){
			$conStatus	=	$_POST['status'] == 3 ? "1=1" : "C.STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		//SELECT DATA MURID
		$sql		=	sprintf("SELECT A.IDMURID, A.NAMA, A.JNS_KELAMIN, B.NAMA_KOTA, A.EMAIL, 
										A.DEPOSIT, C.TGL_DAFTAR, C.STATUS
								 FROM m_murid A
								 LEFT JOIN m_kota B ON A.IDKOTA_TINGGAL = B.IDKOTA
								 LEFT JOIN m_user C ON A.IDMURID = C.IDUSER_CHILD
								 WHERE C.IDLEVEL = 2 AND %s
								 ORDER BY C.STATUS, A.NAMA"
								, $conStatus
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDMURID) AS TOTDATA FROM (%s) AS A", $sql);
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
				
				$status	=	$key['STATUS'] == "1" ? "Non Aktifkan" : "Aktifkan";
				$JK		=	$key['JNS_KELAMIN'] == "1" ? "L" : "P";
				
				if($key['STATUS'] <> 0){
					$action	=	"	<a href='#' id='nonA".$enkripsi->encode($key['IDMURID'])."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($key['IDMURID'])."\")'>
										".$status."
									</a>";
				} else {
					$action	=	"-";
				}


				$data	.=	"	<tr class='".$seling."' id='baris".$enkripsi->encode($key['IDMURID'])."'>
									<td>".$key['NAMA']."</td>
									<td>".$JK."</td>
									<td>".$key['NAMA_KOTA']."</td>
									<td>".$key['EMAIL']."</td>
									<td align='right'>".number_format($key['DEPOSIT'], 0, ',' ,'.')."</td>
									<td align='center'>".$key['TGL_DAFTAR']."</td>
									<td width='100' align='center'>".$action."</td>
								</tr>";
				$i++;
			}
		} else {
			$data		=	"	<tr><td colspan = '7'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
			$startData	=	0;
			$endData	=	0;
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-calendar-o"></i> Laporan</a></li>
        <li>Murid</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Laporan Data Murid</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="<?=APP_URL?>page/202murid.php?func=<?=$enkripsi->encode('exportData')?>">
        <div id="divfilter">
            <p>
                <label>Status</label>
                <span class="field">
                    <input name="status" value="3" id="status3" onclick="filterData(1)" checked type="radio"> Semua &nbsp; 
                    <input name="status" value="0" id="status0" onclick="filterData(1)" type="radio"> Belum Aktifasi &nbsp; 
                    <input name="status" value="1" id="status1" onclick="filterData(1)" type="radio"> Aktif &nbsp; 
                    <input name="status" value="2" id="status2" onclick="filterData(1)" type="radio"> Tidak Aktif
                </span>
            </p>
        </div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="export" id="export" class="submit radius2 pull-right" value="Ekspor Excel" type="submit">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="table_murid" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Nama</td>
                            <td class="head1" style="text-align:center;">JK</td>
                            <td class="head0" style="text-align:center;">Kota</td>
                            <td class="head1" style="text-align:center;">Email</td>
                            <td class="head0" style="text-align:center;">Deposit</td>
                            <td class="head1" style="text-align:center;">Tgl Daftar</td>
                            <td class="head0" style="text-align:center;"></td>
                        </tr>
                    </thead>
                    <tbody id="list_murid">
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
<script>
	function filterData(page){
		var status	=	$('input[name=status]:checked', '#divfilter').val();

		$('#list_murid').html("<tr><td colspan = '7'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/202murid.php?func=<?=$enkripsi->encode('filterData')?>", {status: status, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_murid').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Murid Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
	}

	$(document).ready(function(){
		filterData(1);
	});
</script>