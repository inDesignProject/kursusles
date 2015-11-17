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
		
		//SELECT DATA
		$sql		=	sprintf("SELECT IDPESANPIBADI, IDUSERTUJUAN, IDUSERPENGIRIM, SUBYEK, PESAN, TGL_PESAN, TYPETUJUAN, TYPEPENGIRIM
								 FROM t_pesan_pribadi
								 WHERE TGL_PESAN BETWEEN '%s' AND '%s'
								 ORDER BY TGL_PESAN DESC"
								, $_POST['tglfrom']
								, $_POST['tglto']
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDPESANPIBADI) AS TOTDATA FROM (%s) AS A", $sql);
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
				
				switch($key['TYPEPENGIRIM']){
					case "0"	:	$type_pengirim	=	"Admin";
									$nama_pengirim	=	"Admin";
									break;
					case "1"	:	$type_pengirim	=	"Pengajar";
									$sqlpengirim	=	sprintf("SELECT NAMA FROM m_pengajar WHERE IDPENGAJAR = %s LIMIT 0,1", $key['IDUSERPENGIRIM']);
									$resultpengirim	=	$db->query($sqlpengirim);
									if($resultpengirim <> '' && $resultpengirim <> false){
										$resultpengirim	=	$resultpengirim[0];
										$nama_pengirim	=	$resultpengirim['NAMA'];
									} else {
										$nama_pengirim	=	"Admin";
									}
									break;
					case "2"	:	$type_pengirim	=	"Murid";
									$sqlpengirim	=	sprintf("SELECT NAMA FROM m_murid WHERE IDMURID = %s LIMIT 0,1", $key['IDUSERPENGIRIM']);
									$resultpengirim	=	$db->query($sqlpengirim);
									if($resultpengirim <> '' && $resultpengirim <> false){
										$resultpengirim	=	$resultpengirim[0];
										$nama_pengirim	=	$resultpengirim['NAMA'];
									} else {
										$nama_pengirim	=	"Admin";
									}
									break;
					default		:	$type_pengirim	=	"Admin";
									$nama_pengirim	=	"Admin";
									break;
				}

				switch($key['TYPETUJUAN']){
					case "0"	:	$type_penerima	=	"Admin";
									$nama_penerima	=	"Admin";
									break;
					case "1"	:	$type_penerima	=	"Pengajar";
									$sqlpenerima	=	sprintf("SELECT NAMA FROM m_pengajar WHERE IDPENGAJAR = %s LIMIT 0,1", $key['IDUSERTUJUAN']);
									$resultpenerima	=	$db->query($sqlpenerima);
									if($resultpenerima <> '' && $resultpenerima <> false){
										$resultpenerima	=	$resultpenerima[0];
										$nama_penerima	=	$resultpenerima['NAMA'];
									} else {
										$nama_penerima	=	"Admin";
									}
									break;
					case "2"	:	$type_penerima	=	"Murid";
									$sqlpenerima	=	sprintf("SELECT NAMA FROM m_murid WHERE IDMURID = %s LIMIT 0,1", $key['IDUSERTUJUAN']);
									$resultpenerima	=	$db->query($sqlpenerima);
									if($resultpenerima <> '' && $resultpenerima <> false){
										$resultpenerima	=	$resultpenerima[0];
										$nama_penerima	=	$resultpenerima['NAMA'];
									} else {
										$nama_penerima	=	"Admin";
									}
									break;
					default		:	$type_penerima	=	"Admin";
									$nama_penerima	=	"Admin";
									break;
				}
				
				$data	.=	"	<div style='padding: 8px' id='condata".$enkripsi->encode($key['IDPESANPRIBADI'])."'>
									<div class='boxSquareWhite'>
									  <h4>
										<div style='margin-top: 5px;'>
											<i class='fa fa-chevron-circle-down'></i> Pengirim : ".$nama_pengirim."
											<small> (".$type_pengirim.")</small>
										</div>
										<div style='margin-top: 5px;'>
											<i class='fa fa-chevron-circle-up'></i> Penerima : ".$nama_penerima."
											<small> (".$type_penerima.")</small>
										</div>
									  </h4>
									  <p><small>".$key['PESAN']."</small> </p>
									  <small><i class='fa fa-calendar'></i> ".date('m-d-Y', strtotime($key['TGL_PESAN']))." <i class='fa fa-clock-o'></i> ".date('H:i:s', strtotime($key['TGL_PESAN']))."</small>
									</div>
								</div>";
			}
			
		} else {

			$data		=	"	<div style='padding: 8px'>
									<div class='boxSquareWhite'>
										<center><b>Tidak ada data yang ditampilkan</b></center>
									</div>
								</div>";
			$startData	=	0;
			$endData	=	0;

		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data, "startData"=>$startData, "endData"=>$endData, "pagination"=>$pagination));
		die();
	}
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-desktop"></i> Monitoring</a></li>
        <li>Pesan Pribadi</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Monitoring Kiriman Pesan Pribadi</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Tanggal Kiriman</label>
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
            <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            <div style="float:right">
                <ul class="pagination" id="pagination" >
                </ul>
            </div><br/><br/>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
            	
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

		$('#data-con').html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
		$.post( "<?=APP_URL?>page/405privatemsg.php?func=<?=$enkripsi->encode('filterData')?>", {tglfrom: tglfrom, tglto: tglto, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#data-con').html(data['respon']);
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