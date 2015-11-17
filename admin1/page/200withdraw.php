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
		
		$_POST['tglto']	=	date('Y-m-d', strtotime($_POST['tglto']. "+1 days"));
		$sql			=	sprintf("SELECT IF(A.TYPE = 1, B.NAMA, C.NAMA) AS NAMA_USER, IF(A.TYPE = 1, 'Pengajar', 'Murid') AS TYPE_USER,
											IF(A.TYPE = 1, 'Withdraw', 'Withdraw Assoc') AS TYPE_WITHDRAW, A.NOMINAL, E.NAMA_BANK AS BANK_ASAL,
											D.NO_REKENING AS NOREK_ASAL, D.ATAS_NAMA AS AN_ASAL, G.NAMA_BANK AS BANK_TUJUAN, 
											F.NO_REKENING AS NOREK_TUJUAN, F.ATAS_NAMA AS AN_TUJUAN, A.TANGGAL, H.TGL_TRANSAKSI,
											I.USERNAME, A.IDWITHDRAW
									 FROM t_withdraw A
									 LEFT JOIN m_pengajar B ON A.IDUSER = B.IDPENGAJAR
									 LEFT JOIN m_murid C ON A.IDUSER = C.IDMURID
									 LEFT JOIN admin_rekening D ON A.IDREKENINGADMIN = D.IDREKENING
									 LEFT JOIN m_bank E ON D.IDBANK = E.IDBANK
									 LEFT JOIN m_rekening F ON A.IDREKENING = F.IDREKENING
									 LEFT JOIN m_bank G ON F.IDBANK = G.IDBANK
									 LEFT JOIN t_balance H ON A.IDWITHDRAW = H.IDWITHDRAW
									 LEFT JOIN admin_user I ON A.USERAPPROVE = I.IDUSER
									 WHERE A.STATUS = 1 AND A.TANGGAL BETWEEN '%s' AND '%s'
									 ORDER BY A.TANGGAL"
									, $_POST['tglfrom']
									, $_POST['tglto']
									);
		$result			=	$db->query($sql);
										
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment;Filename=DataWithdraw".date('dmY').".xls");
		$i			= 1;
		
		echo "	<center><strong>LAPORAN DATA WITHDRAW</strong></center>
				<center><strong>PER TANGGAL : ".date('d-m-Y')."</strong></center>
				<br/><br/>
				<table style='width: 100%;' border='1'>
					<thead>
						<tr align='center'>
							<td >No.</td>
							<td >NAMA PENGGUNA</td>
							<td >STATUS</td>
							<td >JENIS WITHDRAW</td>
							<td >NOMINAL</td>
							<td >REKENING ASAL</td>
							<td >REKENING TUJUAN</td>
							<td >TANGGAL PENGAJUAN</td>
							<td >TANGGAL PENCAIRAN</td>
							<td >USER APPROVAL</td>
						</tr>
					</thead> ";
		if($result <> '' && $result <> false){

			foreach($result as $key){
				echo "<tr>
						<td>".$i."</td>
						<td>".$key['NAMA_USER']."</td>
						<td>".$key['TYPE_USER']."</td>
						<td>".$key['TYPE_WITHDRAW']."</td>
						<td align='right'>".number_format($key['NOMINAL'], 0, ',' ,'.')."</td>
						<td>".$key['BANK_ASAL']." (".$key['NOREK_ASAL'].") an. ".$key['AN_ASAL']."</td>
						<td>".$key['BANK_TUJUAN']." (".$key['NOREK_TUJUAN'].") an. ".$key['AN_TUJUAN']."</td>
						<td align='center'>".$key['TANGGAL']."</td>
						<td align='center'>".$key['TGL_TRANSAKSI']."</td>
						<td>".$key['USERNAME']."</td>
					</tr>
				\n";
			$i++;
			}
		} else {
			echo "<tr><td colspan='9'><center>Tidak ada data yang ditampilkan</center></td></tr>";
		}

		echo "</table>";
		die();

	}
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		$_POST['tglto']	=	date('Y-m-d', strtotime($_POST['tglto']. "+1 days"));
		
		//SELECT DATA
		$sql		=	sprintf("SELECT IF(A.TYPE = 1, B.NAMA, C.NAMA) AS NAMA_USER, IF(A.TYPE = 1, 'Pengajar', 'Murid') AS TYPE_USER,
										IF(A.TYPE = 1, 'Withdraw', 'Withdraw Assoc') AS TYPE_WITHDRAW, A.NOMINAL, E.NAMA_BANK AS BANK_ASAL,
										D.NO_REKENING AS NOREK_ASAL, D.ATAS_NAMA AS AN_ASAL, G.NAMA_BANK AS BANK_TUJUAN, 
										F.NO_REKENING AS NOREK_TUJUAN, F.ATAS_NAMA AS AN_TUJUAN, A.TANGGAL, H.TGL_TRANSAKSI,
										I.USERNAME, A.IDWITHDRAW
								 FROM t_withdraw A
								 LEFT JOIN m_pengajar B ON A.IDUSER = B.IDPENGAJAR
								 LEFT JOIN m_murid C ON A.IDUSER = C.IDMURID
								 LEFT JOIN admin_rekening D ON A.IDREKENINGADMIN = D.IDREKENING
								 LEFT JOIN m_bank E ON D.IDBANK = E.IDBANK
								 LEFT JOIN m_rekening F ON A.IDREKENING = F.IDREKENING
								 LEFT JOIN m_bank G ON F.IDBANK = G.IDBANK
								 LEFT JOIN t_balance H ON A.IDWITHDRAW = H.IDWITHDRAW
								 LEFT JOIN admin_user I ON A.USERAPPROVE = I.IDUSER
								 WHERE A.STATUS = 1 AND A.TANGGAL BETWEEN '%s' AND '%s'
								 ORDER BY A.TANGGAL"
								, $_POST['tglfrom']
								, $_POST['tglto']
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDWITHDRAW) AS TOTDATA FROM (%s) AS A", $sql);
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
				
				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDWITHDRAW'])."'>
									<td>".$key['NAMA_USER']."</td>
									<td>".$key['TYPE_USER']."</td>
									<td>".$key['TYPE_WITHDRAW']."</td>
									<td align='right'>".number_format($key['NOMINAL'], 0, ',' ,'.')."</td>
									<td>".$key['BANK_ASAL']." (".$key['NOREK_ASAL'].") an. ".$key['AN_ASAL']."</td>
									<td>".$key['BANK_TUJUAN']." (".$key['NOREK_TUJUAN'].") an. ".$key['AN_TUJUAN']."</td>
									<td align='center'>".$key['TANGGAL']."</td>
									<td align='center'>".$key['TGL_TRANSAKSI']."</td>
									<td>".$key['USERNAME']."</td>
								</tr>";
				$i++;
			}
		} else {
			$data		=	"	<tr><td colspan = '9'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
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
        <li>Withdraw</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Laporan Withdraw Pengguna</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="<?=APP_URL?>page/200withdraw.php?func=<?=$enkripsi->encode('exportData')?>">
        <div id="divfilter">
            <p>
                <label>Tanggal Pengajuan</label>
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
            <input name="export" id="export" class="submit radius2 pull-right" value="Ekspor Excel" type="submit" style="margin-right: 5px">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="table_data" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Nama Pengguna</td>
                            <td class="head1" style="text-align:center;">Status</td>
                            <td class="head0" style="text-align:center;">Jenis</td>
                            <td class="head1" style="text-align:center;">Nominal</td>
                            <td class="head0" style="text-align:center;">Rekening Asal</td>
                            <td class="head1" style="text-align:center;">Rekening Tujuan</td>
                            <td class="head0" style="text-align:center;">Tgl Pengajuan</td>
                            <td class="head1" style="text-align:center;">Tgl Pencairan</td>
                            <td class="head0" style="text-align:center;">User Approval</td>
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

		$('#list_data').html("<tr><td colspan = '9'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/200withdraw.php?func=<?=$enkripsi->encode('filterData')?>", {tglto: tglto, tglfrom:tglfrom, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_data').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
	}
	$(document).ready(function(){
		filterData(1);
	});
</script>