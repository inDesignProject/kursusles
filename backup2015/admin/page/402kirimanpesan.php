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
	
	//FUNGSI DIGUNAKAN DELETE MSG
	if( $enkripsi->decode($_GET['func']) == "deleteMsg" && isset($_GET['func'])){
	
		$idpesan	=	$enkripsi->decode($_POST['value']);
		$sqlUpd		=	sprintf("UPDATE t_pesan
								 SET STATUS = -1, IDUSERHAPUS = %s, TGL_HAPUS = NOW()
								 WHERE IDPESAN = %s"
								, $_SESSION['KursusLesAdmin']['IDUSER']
								, $idpesan
								);
		$affected	=	$db->execSQL($sqlUpd, 0);

		//JIKA DATA BERUBAH
		if($affected > 0){
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Pesan terhapus"));
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menghapus pesan"));
		}
		die();
	
	}
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA
		$sql		=	sprintf("SELECT A.NAMA_PENGIRIM, A.EMAIL, A.PESAN, A.TGL_PESAN, A.ISREAD, A.TYPE,
										IF(A.TYPE = 1, C.NAMA, B.NAMA) AS NAMA_PENERIMA, A.IDPESAN, A.STATUS,
										D.USERNAME, A.TGL_HAPUS
								 FROM t_pesan A
								 LEFT JOIN m_murid B ON A.IDUSER = B.IDMURID
								 LEFT JOIN m_pengajar C ON A.IDUSER = C.IDPENGAJAR
								 LEFT JOIN admin_user D ON A.IDUSERHAPUS = D.IDUSER
								 WHERE A.TGL_PESAN BETWEEN '%s' AND '%s'
								 ORDER BY A.TGL_PESAN DESC"
								, $_POST['tglfrom']
								, $_POST['tglto']
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDPESAN) AS TOTDATA FROM (%s) AS A", $sql);
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
				
				$terbaca=	$key['ISREAD'] == 1 ? "<span class='pull-right'><i class='fa fa-check'></i> <small>sudah dibaca</small></span>" : "";
				$type	=	$key['TYPE'] == 1 ? "<small> (Pengajar)</small>" : "</i><small> (Murid)</small>";
				$tombol	=	$key['ISREAD'] == 0 && $key['STATUS'] == 1 ? "<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='hapusMsg(\"".$enkripsi->encode($key['IDPESAN'])."\")'>Hapus</a>" : "";
				$status	=	$key['STATUS'] == -1 ? "<small class='pull-right'>
													<i class='fa fa-times'></i> Dihapus oleh ".$key['USERNAME']."<br/>
													<i class='fa fa-clock-o'></i>".$key['TGL_HAPUS']."
												   </small>" : "";
				
				$data	.=	"	<div style='padding: 8px' id='condata".$enkripsi->encode($key['IDPESAN'])."'>
									<div class='boxSquareWhite'>
									  <h4>
									  	<i class='fa fa-envelope-o'></i>
									  	".$key['NAMA_PENGIRIM']."
										<small>- <i class='fa fa-envelope'></i> ".$key['EMAIL']."</small>
										".$terbaca."<br/>
										<div style='margin-top: 5px;'>
											<i class='fa fa-chevron-circle-right'></i> ".$key['NAMA_PENERIMA']."
											".$type."
										</div>
									  </h4>
									  <p><small>".$key['PESAN']."</small> </p>
									  <small><i class='fa fa-calendar'></i> ".date('m-d-Y', strtotime($key['TGL_PESAN']))." <i class='fa fa-clock-o'></i> ".date('H:i:s', strtotime($key['TGL_PESAN']))."</small>
									  ".$tombol.$status."
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
        <li>Kiriman Pesan</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Cek Kiriman Pesan Pengunjung</h1>
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
		$.post( "<?=APP_URL?>page/402kirimanpesan.php?func=<?=$enkripsi->encode('filterData')?>", {tglfrom: tglfrom, tglto: tglto, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#data-con').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
		return true;
	}
	function hapusMsg(value){
		$("#dialog-confirm").dialog({
			closeOnEscape: false,
			resizable: true,
			modal: true,
			minWidth: 500,
			title: "Konfirmasi",
			open: function() {
			  $(this).html("Yakin hapus data pesan?");
			},
			position: {
				my: 'top', 
				at: 'top'
			},
			close: function() {
				$(this).dialog( "close" );
			},
			buttons: {
				"Ya": function() {
						$.post("<?=APP_URL?>page/402kirimanpesan.php?func=<?=$enkripsi->encode('deleteMsg')?>", {value:value})
						.done(function( data ) {
							data			=	JSON.parse(data);
							$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));

							if(data['respon_code'] == '00000'){
								filterData(1);
							}
						});
						$(this).dialog("close");
					},
				"Tidak": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	$(document).ready(function(){
		filterData(1);
	});
</script>