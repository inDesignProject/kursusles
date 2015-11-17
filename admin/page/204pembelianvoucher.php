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
			$conStatus	=	$_POST['status'] == 3 ? "1=1" : "A.STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		$sql			=	sprintf("SELECT B.NAMA AS NAMA_MURID, D.NAMA_BANK AS BANK_TUJUAN, C.NO_REKENING, C.UNIT_BANK, C.CABANG_BANK,
											C.ATAS_NAMA AS AN_TUJUAN, E.NAMA_VOUCHER, A.TOTAL_LEMBAR, A.TOTAL_VOUCHER, A.TOTAL_RP, 
											F.NAMA_BANK AS BANK_ASAL, A.NOREK, A.ATAS_NAMA AS AN_ASAL, A.TGL_PEMBELIAN, 
											A.TGL_MAXTRF, A.TGL_APPROVAL, G.USERNAME, A.STATUS
									 FROM log_voucher A
									 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
									 LEFT JOIN admin_rekening C ON A.IDREKENING = C.IDREKENING
									 LEFT JOIN m_bank D ON C.IDBANK = D.IDBANK
									 LEFT JOIN m_voucher E ON A.IDVOUCHER = E.IDVOUCHER
									 LEFT JOIN m_bank F ON A.IDBANKASAL = F.IDBANK
									 LEFT JOIN admin_user G ON A.IDUSERAPPROVE = G.IDUSER
									 WHERE %s AND A.TGL_PEMBELIAN BETWEEN '%s' AND '%s'"
									, $conStatus
									, $_POST['tglfrom']
									, $_POST['tglto']
									);
		$result			=	$db->query($sql);
										
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment;Filename=DataPembelianVoucher".date('dmY').".xls");
		$i			= 1;
		
		echo "	<center><strong>LAPORAN DATA PEMBELIAN VOUCHER</strong></center>
				<center><strong>PEMBELIAN TANGGAL : ".$_POST['tglfrom']." s/d ".$_POST['tglto']."</strong></center>
				<br/><br/>
				<table style='width: 100%;' border='1'>
					<thead>
						<tr align='center'>
							<td rowspan='2'>No.</td>
							<td colspan='4'>Keterangan Voucher</td>
							<td colspan='4'>Data Pembeli</td>
							<td colspan='5'>Data Rekening Tujuan</td>
							<td colspan='4'>Keterangan Transaksi</td>
							<td rowspan='2'>Status Transaksi</td>
						</tr>
						<tr>
							<td >Nama Voucher</td>
							<td >Lembar</td>
							<td >Total Nominal Voucher</td>
							<td >Total Nominal Transfer</td>
							<td >Nama Murid</td>
							<td >Bank Asal</td>
							<td >Nomor Rekening</td>
							<td >Atas Nama</td>
							<td >Nama Bank</td>
							<td >Nomor Rekening</td>
							<td >Cabang</td>
							<td >Unit</td>
							<td >Atas Nama</td>
							<td >Tgl Pembelian</td>
							<td >Tgl Maks Transfer</td>
							<td >Tgl Approval</td>
							<td >User Approval</td>
						</tr>
					</thead> ";
		if($result <> '' && $result <> false){
			
			foreach($result as $key){
				
				switch($key['STATUS']){
					case "1"	:	$status	=	"<b style='color: green'>Pembelian Disetujui</b>"; break;
					case "0"	:	$status	=	"<b style='color: #E8A40C'>Menunggu Approval</b>"; break;
					case "-1"	:	$status	=	"<b style='color: red'>Pembelian Ditolak</b>"; break;
					case "-2"	:	$status	=	"<b style='color: red'>Aktifitas transfer tidak ada / melebihi batas waktu</b>"; break;
					default		:	$status	=	""; break;
				}
				
				echo "<tr>
						<td>".$i."</td>
						<td>".$key["NAMA_VOUCHER"]."</td>
						<td align='right'>".$key["TOTAL_LEMBAR"]."</td>
						<td align='right'>".$key["TOTAL_VOUCHER"]."</td>
						<td align='right'>".$key["TOTAL_RP"]."</td>
						<td>".$key["NAMA_MURID"]."</td>
						<td>".$key["BANK_ASAL"]."</td>
						<td>".$key["NOREK"]."</td>
						<td>".$key["AN_ASAL"]."</td>
						<td>".$key["BANK_TUJUAN"]."</td>
						<td>".$key["NO_REKENING"]."</td>
						<td>".$key["CABANG_BANK"]."</td>
						<td>".$key["UNIT_BANK"]."</td>
						<td>".$key["AN_TUJUAN"]."</td>
						<td align='right'>".$key["TGL_PEMBELIAN"]."</td>
						<td align='right'>".$key["TGL_MAXTRF"]."</td>
						<td align='right'>".$key["TGL_APPROVAL"]."</td>
						<td>".$key["USERNAME"]."</td>
						<td>".$status."</td>
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
	
	//FUNGSI DIGUNAKAN LIHAT DETAIL
	if( $enkripsi->decode($_GET['func']) == "getDetail" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['id']);
		$sql		=	sprintf("SELECT B.NAMA AS NAMA_MURID, D.NAMA_BANK AS BANK_TUJUAN, C.NO_REKENING, C.UNIT_BANK, C.CABANG_BANK,
										C.ATAS_NAMA AS AN_TUJUAN, E.NAMA_VOUCHER, A.TOTAL_LEMBAR, A.TOTAL_VOUCHER, A.TOTAL_RP, F.NAMA_BANK AS BANK_ASAL, 
										A.NOREK, A.ATAS_NAMA AS AN_ASAL, A.TGL_PEMBELIAN, A.TGL_MAXTRF, A.TGL_APPROVAL, G.USERNAME
								 FROM log_voucher A
								 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
								 LEFT JOIN admin_rekening C ON A.IDREKENING = C.IDREKENING
								 LEFT JOIN m_bank D ON C.IDBANK = D.IDBANK
								 LEFT JOIN m_voucher E ON A.IDVOUCHER = E.IDVOUCHER
								 LEFT JOIN m_bank F ON A.IDBANKASAL = F.IDBANK
								 LEFT JOIN admin_user G ON A.IDUSERAPPROVE = G.IDUSER
								 WHERE A.IDLOGVOUCHER = %s
								 LIMIT 0,1"
								, $iddata
								);
		$result		=	$db->query($sql);
		$result		=	$result[0];
		$listSol	=	'';
		
		echo "	<tr class='detail-con'>
					<td style='padding: 24px;' colspan='8'>
						<h4>Keterangan Voucher</h4>
						Voucher : ".$result['NAMA_VOUCHER']." / ".$result['TOTAL_LEMBAR']." Lembar<br/>
						Tot Nominal Voucher : Rp. ".number_format($result['TOTAL_VOUCHER'], 0, ',', '.').",- <br/>
						Tot Pembayaran : Rp. ".number_format($result['TOTAL_RP'], 0, ',', '.').",- <br/>
						<br/>
						<h4>Data Pembeli</h4>
						Nama Murid : ".$result['NAMA_MURID']."<br/>
						Dari Rekening ".$result['BANK_ASAL']." (".$result['NOREK'].") Atas Nama ".$result['AN_ASAL']."<br/>
						<br/>
						<h4>Data Rekening Tujuan</h4>
						Rekening Tujuan : ".$result['BANK_TUJUAN']." (".$result['NO_REKENING'].")<br/>
						Cabang : ".$result['CABANG_BANK']." / Unit ".$result['UNIT_BANK']."<br/>
						Atas Nama : ".$result['AN_TUJUAN']."<br/>
						<br/>
						<h4>Keterangan Transaksi</h4>
						Tgl Pembelian : ".$result['TGL_PEMBELIAN']."<br/>
						Tgl Maks Transfer : ".$result['TGL_MAXTRF']."<br/>
						Tgl Approval : ".$result['TGL_APPROVAL']."<br/>
						User Approval : ".$result['USERNAME']."<br/>
					</td>
				</tr>";
		die();
	}

	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		if(isset($_POST['status']) && $_POST['status'] <> ''){
			$conStatus	=	$_POST['status'] == "" ? "1=1" : "A.STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		//SELECT DATA
		$sql		=	sprintf("SELECT B.NAMA_VOUCHER, A.TOTAL_LEMBAR, A.TOTAL_RP, A.TGL_PEMBELIAN,
										A.TGL_MAXTRF, A.TGL_APPROVAL, A.STATUS, A.IDLOGVOUCHER
								 FROM log_voucher A
								 LEFT JOIN m_voucher B ON A.IDVOUCHER = B.IDVOUCHER
								 WHERE %s AND A.TGL_PEMBELIAN BETWEEN '%s' AND '%s'
								 ORDER BY A.TGL_PEMBELIAN DESC"
								, $conStatus
								, $_POST['tglfrom']
								, $_POST['tglto']
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDLOGVOUCHER) AS TOTDATA FROM (%s) AS A", $sql);
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
				
				switch($key['STATUS']){
					case "1"	:	$status	=	"<b style='color: green'>Pembelian Disetujui</b>"; break;
					case "0"	:	$status	=	"<b style='color: #E8A40C'>Menunggu Approval</b>"; break;
					case "-1"	:	$status	=	"<b style='color: red'>Pembelian Ditolak</b>"; break;
					case "-2"	:	$status	=	"<b style='color: red'>Aktifitas transfer tidak ada / melebihi batas waktu</b>"; break;
					default		:	$status	=	""; break;
				}

				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDLOGVOUCHER'])."'>
									<td>".$key['NAMA_VOUCHER']."</td>
									<td align='right'>".$key['TOTAL_LEMBAR']."</td>
									<td align='right'>".number_format($key['TOTAL_RP'], 0, ',', '.')."</td>
									<td align='center'>".$key['TGL_PEMBELIAN']."</td>
									<td align='center'>".$key['TGL_MAXTRF']."</td>
									<td align='center'>".$key['TGL_APPROVAL']."</td>
									<td>".$status."</td>
									<td width='75' align='center'>
										<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='getDetail(\"".$enkripsi->encode($key['IDLOGVOUCHER'])."\")'>
											Detail <i class='fa fa-chevron-circle-down'></i>
										</a>
									</td>
								</tr>";
				$i++;
			}
		} else {
			$data		=	"	<tr><td colspan = '8'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
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
        <li>Pembelian Voucher</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Laporan Pembelian Voucher</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="<?=APP_URL?>page/204pembelianvoucher.php?func=<?=$enkripsi->encode('exportData')?>">
        <div id="divfilter">
            <p>
                <label>Status</label>
                <span class="field">
					<select id="status" name="status" class="form-control" >
                    	<option value="">Semua Status</option>
                    	<option value="0">Menunggu Approval</option>
                    	<option value="1">Sudah Approve</option>
                    	<option value="-1">Ditolak</option>
                    	<option value="-2">Tidak ada aktivitas transfer</option>
                    </select>
                </span>
            </p>
            <p>
                <label>Tanggal Pembelian</label>
                <span class="field">
                	<input type="text" name="tglfrom" id="tglfrom" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-01')?>" style="width:75px; text-align: center;" />
                     s.d 
                	<input type="text" name="tglto" id="tglto" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-t')?>" style="width:75px; text-align: center;" />
                </span>
            </p>
        </div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="filter" id="filter" class="submit radius2 pull-right" value="Saring" type="button" onclick="filterData(1)" style="margin-left: 5px;">
            <input name="export" id="export" class="submit radius2 pull-right" value="Ekspor Excel" type="submit">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="table_data" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Voucher</td>
                            <td class="head1" style="text-align:center;" width="25">Lbr</td>
                            <td class="head0" style="text-align:center;">Tot Rupiah</td>
                            <td class="head1" style="text-align:center;">Tgl Pembelian</td>
                            <td class="head0" style="text-align:center;">Tgl Maks Trf</td>
                            <td class="head1" style="text-align:center;">Tgl Approval</td>
                            <td class="head0" style="text-align:center;">Status</td>
                            <td class="head1" style="text-align:center;"></td>
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
			status	=	$('#status').val();

		$('#list_data').html("<tr><td colspan = '8'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/204pembelianvoucher.php?func=<?=$enkripsi->encode('filterData')?>", {tglfrom: tglfrom, tglto: tglto, page:page, status:status})
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
		$.post( "<?=APP_URL?>page/204pembelianvoucher.php?func=<?=$enkripsi->encode('getDetail')?>", {id:id})
		.done(function( data ) {
			$('#baris'+id).after(data);
			
		});
	}
	$(document).ready(function(){
		filterData(1);
	});
</script>