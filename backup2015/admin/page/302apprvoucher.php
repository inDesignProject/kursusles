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
	
	//FUNGSI DIGUNAKAN APPROVE
	if( $enkripsi->decode($_GET['func']) == "setApproval" && isset($_GET['func'])){

		$iddata		=	$enkripsi->decode($_POST['id']);
		$idmurid	=	$enkripsi->decode($_POST['idmurid']);
		$status		=	$_POST['status'];
		$sqlSel		=	sprintf("SELECT B.NAMA_VOUCHER, A.TOTAL_LEMBAR, A.TOTAL_VOUCHER, A.TOTAL_RP, C.NAMA, C.EMAIL
								 FROM log_voucher A
								 LEFT JOIN m_voucher B ON A.IDVOUCHER = B.IDVOUCHER
								 LEFT JOIN m_murid C ON A.IDMURID = C.IDMURID
								 WHERE A.IDMURID = %s AND A.IDLOGVOUCHER = %s
								 LIMIT 0,1"
								 , $idmurid
								 , $iddata
								);
		$resultSel	=	$db->query($sqlSel);
		$resultSel	=	$resultSel[0];
		
		if($status <> 1){
			
			$sqlupd		=	sprintf("UPDATE log_voucher A
									 LEFT JOIN t_balance B ON A.IDLOGVOUCHER = B.IDLOGVOUCHER
									 SET A.STATUS = %s
									 WHERE A.IDLOGVOUCHER = %s AND A.STATUS = 0"
									, $status
									, $iddata
									);
			$affected	=	$db->execSQL($sqlupd, 0);
			
			if($affected > 0){
				
				if($status == -2){
					$kettolak	=	"<b>Tidak ada transfer dana dari rekening asal sampai dengan batas waktu yang telah ditentukan</b>";
				} else {
					$kettolak	=	"<b>Jumlah nominal yang kami terima tidak sesuai dengan jumlah yang seharusnya / Kami tidak menemukan mutasi yang berasal dari rekening transfer anda</b>";
				}
				
				$message	=	"<html>
								<head>
								</head>
								
								<body>
									
									<p>
									Halo ".$resultSel['NAMA'].",<br/><br/>
									
									kami menginfomasikan kepada anda bahwa permintaan pembelian voucher dengan data : <br/><br/>
									Nama Voucher : ".$resultSel['NAMA_VOUCHER']."<br/>
									Jumlah Lembar : ".$resultSel['TOTAL_LEMBAR']."<br/>
									Total Nominal Voucher : Rp. ".number_format($resultSel['TOTAL_VOUCHER'],0,',','.').",-<br/>
									Total Pembayaran : Rp. ".number_format($resultSel['TOTAL_RP'],0,',','.').",-<br/><br/>
									
									tidak dapat kami setujui karena ".$kettolak.".<br/>
									Demikian pemberitahuan yang dapat kami sampaikan.<br/><br/>
									
									Best regards,<br /><br />
									Admin KursusLes.com
									</p>
								
								</body>
								</html>";
				$session->sendEmail($resultSel['EMAIL'], $resultSel['NAMA'], "Notifikasi Status Pembelian Voucher KursusLes.com", $message);
				
				echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Data tersimpan. Email notifikasi terkirim"));
				die();
				
			} else {
				
				echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menyimpan data"));
				die();
				
			}
			
		} else {
			
			$sqlupd		=	sprintf("UPDATE log_voucher A
									 LEFT JOIN t_balance B ON A.IDLOGVOUCHER = B.IDLOGVOUCHER
									 LEFT JOIN m_murid C ON A.IDMURID = C.IDMURID
									 SET A.STATUS = 1, B.DEBET = A.TOTAL_VOUCHER, C.DEPOSIT = C.DEPOSIT + A.TOTAL_VOUCHER
									 WHERE A.IDLOGVOUCHER = %s"
									, $iddata
									);
			$affected	=	$db->execSQL($sqlupd, 0);
			
			if($affected > 0){
				
				$message	=	"<html>
								<head>
								</head>
								
								<body>
									
									<p>
									Halo ".$resultSel['NAMA'].",<br/><br/>
									
									kami menginfomasikan kepada anda bahwa permintaan pembelian voucher dengan data : <br/><br/>
									Nama Voucher : ".$resultSel['NAMA_VOUCHER']."<br/>
									Jumlah Lembar : ".$resultSel['TOTAL_LEMBAR']."<br/>
									Total Nominal Voucher : Rp. ".number_format($resultSel['TOTAL_VOUCHER'],0,',','.').",-<br/>
									Total Pembayaran : Rp. ".number_format($resultSel['TOTAL_RP'],0,',','.').",-<br/><br/>
									
									telah kami setujui dan deposit anda akan secara otomatis bertambah. Anda dapat mengecek data transaksi di menu <strong>Balance</strong> pada halaman profil.<br/>
									Demikian pemberitahuan yang dapat kami sampaikan.<br/><br/>
									
									Best regards,<br /><br />
									Admin KursusLes.com
									</p>
								
								</body>
								</html>";
				$session->sendEmail($resultSel['EMAIL'], $resultSel['NAMA'], "Notifikasi Status Pembelian Voucher KursusLes.com", $message);
				
				echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data tersimpan. Notifikasi email terkirim"));
				die();
				
			} else {

				echo json_encode(array("respon_code"=>"00003", "respon_msg"=>"Gagal Menyimpan. Silakan coba lagi nanti"));
				die();
				
			}
		}
		
		die();
	}
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA
		$sql		=	sprintf("SELECT B.NAMA_VOUCHER, A.TOTAL_RP, C.NAMA_BANK AS BANK_ASAL,
										A.NOREK, A.ATAS_NAMA AS AN_ASAL, E.NAMA_BANK AS BANK_TUJUAN, D.CABANG_BANK, D.UNIT_BANK,
										D.NO_REKENING, D.ATAS_NAMA AS AN_TUJUAN, A.TGL_PEMBELIAN, A.TGL_MAXTRF, '' AS ACTION,
										A.IDLOGVOUCHER, A.IDMURID
								 FROM log_voucher A
								 LEFT JOIN m_voucher B ON A.IDVOUCHER = B.IDVOUCHER
								 LEFT JOIN m_bank C ON A.IDBANKASAL = C.IDBANK
								 LEFT JOIN admin_rekening D ON A.IDREKENING = D.IDREKENING
								 LEFT JOIN m_bank E ON D.IDBANK = E.IDBANK
								 WHERE A.TGL_PEMBELIAN BETWEEN '%s' AND '%s' AND A.STATUS = 0
								 ORDER BY A.TGL_PEMBELIAN DESC"
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
				
				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDLOGVOUCHER'])."'>
									<td>".$key['NAMA_VOUCHER']."</td>
									<td align='right'>".number_format($key['TOTAL_RP'], 0, ',', '.')."</td>
									<td>
										".$key['BANK_ASAL']."<br/>
										(".$key['NOREK'].")<br/>
										An. : ".$key['AN_ASAL']."
									</td>
									<td>
										".$key['BANK_TUJUAN']."<br/>
										(".$key['NO_REKENING'].")<br/>
										".$key['CABANG_BANK']."<br/>
										".$key['UNIT_BANK']."<br/>
										An. ".$key['AN_TUJUAN']."
									</td>
									<td align='center'>".$key['TGL_PEMBELIAN']."</td>
									<td align='center'>".$key['TGL_MAXTRF']."</td>
									<td width='75' align='center'>
										<select id='status".$enkripsi->encode($key['IDLOGVOUCHER'])."' name='status".$enkripsi->encode($key['IDLOGVOUCHER'])."'>
											<option value='1'>Approve</option>
											<option value='-1'>Tolak</option>
											<option value='-2'>Tolak Lewat Batas Waku</option>
										</select><br/><br/>
										<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='setApproval(\"".$enkripsi->encode($key['IDLOGVOUCHER'])."\", \"".$enkripsi->encode($key['IDMURID'])."\")'>
											<i class='fa fa-check'></i> Simpan
										</a>
									</td>
								</tr>";
				$i++;
			}
		} else {
			$data		=	"	<tr><td colspan = '7'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
			$startData	=	0;
			$endData	=	0;
		}
		
		echo json_encode(array("totData"=>$totData *1, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}
	
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-pencil-square-o"></i> Transaksi</a></li>
        <li>Approve Voucher</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Approve Pembelian Voucher Siswa</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="<?=APP_URL?>page/204pembelianvoucher.php?func=<?=$enkripsi->encode('exportData')?>">
        <div id="divfilter">
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
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="table_data" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head1" style="text-align:center;">Voucher</td>
                            <td class="head0" style="text-align:center;">Tot Rupiah</td>
                            <td class="head1" style="text-align:center;">Rekening Asal</td>
                            <td class="head0" style="text-align:center;">Rekening Tujuan</td>
                            <td class="head1" style="text-align:center;">Tgl Pembelian</td>
                            <td class="head0" style="text-align:center;">Tgl Maks Trf</td>
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

		$('#list_data').html("<tr><td colspan = '7'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/302apprvoucher.php?func=<?=$enkripsi->encode('filterData')?>", {tglfrom: tglfrom, tglto: tglto, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_data').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
		return true;
	}
	function setApproval(id, idmurid){
		var status	=	$('#status'+id).val();

		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		$('#status'+id).prop('disabled', true);
		$.post( "<?=APP_URL?>page/302apprvoucher.php?func=<?=$enkripsi->encode('setApproval')?>", {id: id, status:status, idmurid:idmurid})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#status'+id).prop('disabled', false);
			}

			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			$('#baris'+id).remove();
			filterData(1);
			
		});
		return true;
	}
	$(document).ready(function(){
		filterData(1);
	});
</script>