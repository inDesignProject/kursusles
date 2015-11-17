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
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA MURID
		$sql		=	sprintf("SELECT A.NAMA, A.JNS_KELAMIN, B.NAMA_KOTA, A.EMAIL, C.TGL_APPROVE, D.USERNAME, A.IDPENGAJAR
								 FROM m_pengajar A
								 LEFT JOIN m_kota B ON A.IDKOTA_TINGGAL = B.IDKOTA
								 LEFT JOIN log_verifikasi C ON A.IDPENGAJAR = C.IDPENGAJAR
								 LEFT JOIN admin_user D ON C.IDUSERAPPROVE = D.IDUSER
								 WHERE A.VERIFIED_STATUS = 1 AND C.STATUS = 1 AND C.TGL_APPROVE BETWEEN '%s' AND '%s'
								 ORDER BY C.TGL_APPROVE, A.NAMA"
								, $_POST['tglfrom']
								, $_POST['tglto']
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
				
				$JK		=	$key['JNS_KELAMIN'] == "1" ? "L" : "P";
				$data	.=	"	<tr class='".$seling."' id='baris".$enkripsi->encode($key['IDPENGAJAR'])."'>
									<td>".$key['NAMA']."</td>
									<td>".$JK."</td>
									<td>".$key['NAMA_KOTA']."</td>
									<td>".$key['EMAIL']."</td>
									<td align='center'>".$key['TGL_APPROVE']."</td>
									<td>".$key['USERNAME']."</td>
								</tr>";
				$i++;
			}
		} else {
			$data		=	"	<tr><td colspan = '6'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
			$startData	=	0;
			$endData	=	0;
		}
		
		echo json_encode(array("totData"=>$totData*1, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}
	
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-calendar-o"></i> Laporan</a></li>
        <li>Verifikasi</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Laporan Verifikasi Pengajar</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Tanggal Verifikasi</label>
                <span class="field">
                	<input type="text" name="tglfrom" id="tglfrom" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-01')?>" style="width:75px; text-align: center;" />
                     s.d 
                	<input type="text" name="tglto" id="tglto" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-d')?>" style="width:75px; text-align: center;" />
                </span>
            </p>
        </div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="filter" id="filter" class="submit radius2 pull-right" value="Saring" type="button" onclick="filterData(1)">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="table_data" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Nama Pengajar</td>
                            <td class="head1" style="text-align:center;">JK</td>
                            <td class="head0" style="text-align:center;">Kota</td>
                            <td class="head1" style="text-align:center;">Email</td>
                            <td class="head0" style="text-align:center;">Tgl Verifikasi</td>
                            <td class="head1" style="text-align:center;">User Verifikator</td>
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
<script>
	$('#tglfrom, #tglto').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		lang:'id',
		closeOnDateSelect:true
	});
	function filterData(page){
		var tglfrom	=	$('#tglfrom').val();
			tglto	=	$('#tglto').val();

		$('#list_data').html("<tr><td colspan = '6'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/203verfpengajar.php?func=<?=$enkripsi->encode('filterData')?>", {tglfrom: tglfrom, tglto: tglto, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_data').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
		return true;
	}
	$(document).ready(function(){
		filterData(1);
	});
</script>