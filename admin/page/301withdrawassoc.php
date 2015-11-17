<?php
	include('../php/include/enkripsi.php');
	include('../php/include/session.php');
	include('../php/lib/db_connection.php');
	include('../php/lib/recaptchalib.php');
	require "../php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	if($session->cekSession() <> 2 && !isset($_GET['func'])){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode($session->cekSession())."'</script>";
		 die();
	}
	
	//FUNGSI DIGUNAKAN APPROVE WITHDRAW
	if( $enkripsi->decode($_GET['func']) == "saveWith" && isset($_GET['func'])){
		
		//CEK PILIHAN REKENING
		if($_POST['rekeningadmin'] == '' || !isset($_POST['rekeningadmin'])){
			
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Silakan pilih rekening yang digunakan untuk transfer dana"));
			die();
			
		}
		
		//CEK G-RECAPTCHA
		if($_POST['g-recaptcha-response'] == '' || !isset($_POST['g-recaptcha-response'])){
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Silakan cek box reCaptcha untuk melanjutkan"));
			die();
		}
		
		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'00003',"respon_msg"=>"Captcha yang anda masukkan tidak valid"));
			die();
		}
		
		$idwithdraw	=	$enkripsi->decode($_POST['idwith']);
		$idrekadmin	=	$enkripsi->decode($_POST['rekeningadmin']);
		
		$sqlupd		=	sprintf("UPDATE t_withdraw
								 SET IDREKENINGADMIN	= %s,
								 	 STATUS				= 1,
									 USERAPPROVE		= %s
								 WHERE IDWITHDRAW		= %s"
								, $idrekadmin
								, $_SESSION['KursusLesAdmin']['IDUSER']
								, $idwithdraw
								);
		$affected	=	$db->execSQL($sqlupd, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			
			$sqlSel		=	sprintf("SELECT B.NAMA, B.EMAIL, A.NOMINAL, B.IDMURID, C.NO_REKENING, C.ATAS_NAMA, D.NAMA_BANK
									 FROM t_withdraw A
									 LEFT JOIN m_murid B ON A.IDUSER = B.IDMURID
									 LEFT JOIN m_rekening C ON A.IDREKENING = C.IDREKENING
									 LEFT JOIN m_bank D ON C.IDBANK	= D.IDBANK
									 WHERE A.IDWITHDRAW = %s"
									, $idwithdraw
									);
			$resultsel	=	$db->query($sqlSel);
			$resultsel	=	$resultsel[0];
			$nama		=	$resultsel['NAMA'];
			$email		=	$resultsel['EMAIL'];
			$nominal	=	$resultsel['NOMINAL'] * 1;
			$idmurid	=	$resultsel['IDMURID'];
			$keterangan	=	"Withdraw sebesar Rp. ".number_format($nominal, 0, ',', '.').",- ke rekening ".$resultsel['NAMA_BANK']." (".$resultsel['NO_REKENING'].") atas nama ".$resultsel['ATAS_NAMA'] ;
			
			$sqlIns		=	sprintf("INSERT INTO t_balance
									 (IDJENISTRANSAKSI, IDWITHDRAW, JENIS_PEMILIK, IDUSER, TGL_TRANSAKSI, KREDIT, KETERANGAN)
									 VALUES
									 (2, %s, 2, %s, NOW(), %s, '%s')"
									, $idwithdraw
									, $idmurid
									, $nominal
									, $keterangan
									);
			$db->execSQL($sqlIns, 0);
			
			$sqlupdB	=	sprintf("UPDATE m_murid A
									 LEFT JOIN t_withdraw B ON A.IDMURID = B.IDUSER
									 SET A.DEPOSIT = A.DEPOSIT - %s
									 WHERE B.IDWITHDRAW = %s"
									, $nominal
									, $idwithdraw
									);
			$db->execSQL($sqlupdB, 0);
			
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								
								<p>
								kami telah menerima permintaan withdraw yang anda ajukan dan telah melakukan transfer dana sejumlah <b>Rp. ".number_format($nominal, 0, ',', '.').",-</b><br/>
								ke rekening tujuan ".$resultsel['NAMA_BANK']." (".$resultsel['NO_REKENING'].") atas nama ".$resultsel['ATAS_NAMA']." sesuai dengan data permintaan anda.<br/>
								Deposit anda akan secara otomatis berkurang sejumlah nominal diatas.
								<br/><br/>
								Demikian pemberitahuan dari kami.
								</p>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Pemberitahuan Persetujuan Withdraw", $message);
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data tersimpan"));
			die();
		} else {
			echo json_encode(array("respon_code"=>'00004',"respon_msg"=>"Gagal menyimpan, silakan coba lagi nanti"));
			die();
		}
		
	}
	
	//FUNGSI DIGUNAKAN IGNORE WITHDRAW
	if( $enkripsi->decode($_GET['func']) == "procIgnoreWith" && isset($_GET['func'])){
		
		//CEK ALASAN
		if($_POST['alasan'] == '' || !isset($_POST['alasan'])){
			
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Silakan isi alasan penolakan withdraw"));
			die();
			
		}
		
		//CEK G-RECAPTCHA
		if($_POST['g-recaptcha-response'] == '' || !isset($_POST['g-recaptcha-response'])){
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Silakan cek box reCaptcha untuk melanjutkan"));
			die();
		}
		
		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'00003',"respon_msg"=>"Captcha yang anda masukkan tidak valid"));
			die();
		}
		
		$idwithdraw	=	$enkripsi->decode($_POST['idwith']);
		
		$sqlupd		=	sprintf("UPDATE t_withdraw
								 SET ALASAN_TOLAK		= '%s',
								 	 STATUS				= -1,
									 USERAPPROVE		= %s
								 WHERE IDWITHDRAW		= %s"
								, $db->db_text($_POST['alasan'])
								, $_SESSION['KursusLesAdmin']['IDUSER']
								, $idwithdraw
								);
		$affected	=	$db->execSQL($sqlupd, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			
			$sqlSel		=	sprintf("SELECT B.NAMA, B.EMAIL, A.NOMINAL, B.IDMURID, C.NO_REKENING, C.ATAS_NAMA, D.NAMA_BANK
									 FROM t_withdraw A
									 LEFT JOIN m_murid B ON A.IDUSER = B.IDMURID
									 LEFT JOIN m_rekening C ON A.IDREKENING = C.IDREKENING
									 LEFT JOIN m_bank D ON C.IDBANK	= D.IDBANK
									 WHERE A.IDWITHDRAW = %s"
									, $idwithdraw
									);
			$resultsel	=	$db->query($sqlSel);
			$resultsel	=	$resultsel[0];
			$nama		=	$resultsel['NAMA'];
			$email		=	$resultsel['EMAIL'];
			$nominal	=	$resultsel['NOMINAL'] * 1;
			$idmurid	=	$resultsel['IDMURID'];
			
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								
								<p>
								kami telah menerima permintaan withdraw yang anda ajukan sejumlah <b>Rp. ".number_format($nominal, 0, ',', '.').",-</b><br/>
								ke rekening tujuan ".$resultsel['NAMA_BANK']." (".$resultsel['NO_REKENING'].") atas nama ".$resultsel['ATAS_NAMA'].", namun dana tidak dapat kami transfer karena<br/>
								<b>".$db->db_text($_POST['alasan']).".</b>
								<br/><br/>
								Demikian pemberitahuan dari kami.
								</p>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Pemberitahuan Penolakan Withdraw", $message);
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data tersimpan"));
			die();
			
		} else {
			echo json_encode(array("respon_code"=>'00004',"respon_msg"=>"Gagal menyimpan, silakan coba lagi nanti"));
			die();
		}
		
	}
	
	//FUNGSI DIGUNAKAN FORM APPROVE WITHDRAW
	if( $enkripsi->decode($_GET['func']) == "appWith" && isset($_GET['func'])){
		
		$iddata	=	$enkripsi->decode($_POST['id']);
		$sql	=	sprintf("SELECT NAMA, DEPOSIT
							 FROM t_withdraw A
							 LEFT JOIN m_murid B ON A.IDUSER = B.IDMURID
							 WHERE A.IDWITHDRAW = %s AND STATUS = 0
							 LIMIT 0,1"
							, $iddata
							);
		$result	=	$db->query($sql);
		$result	=	$result[0];
		
		echo "	<tr id='detail".$_POST['id']."' class='detail-con'>
					<td colspan='8' style='padding: 18px'>
						<h4>Data Murid - Withdraw Disetujui</h4><br>
						<i class='fa fa-lg fa-user'></i> Nama : ".$result['NAMA']."<br>
						<i class='fa fa-lg fa-tag'></i> Deposit : Rp. ".number_format($result['DEPOSIT'],0,',','.').",-<br><br>
						
						<div class='form-group'>
							<div class='g-recaptcha' data-sitekey='".$siteKey."'></div>
							<script type='text/javascript' src='".GOOGLE_RECAPTCHA_URL."api.js?hl=".$lang."'></script>
						</div>

						<span class='field'>
							Transfer dana melalui rekening :		
							<select id='rekeningadmin' name='rekeningadmin' class='form-control'>
								<option value=''>- Pilih Rekening Asal -</option>
							</select>
							<input type='hidden' id='idwith' name='idwith' value='".$_POST['id']."' />
							<a href='#' class='btn btn-kursusles btn-sm' style='padding: 3px;' onclick='saveWith(\"".$_POST['id']."\")'>
								<i class='fa fa-lg fa-floppy-o'></i> Simpan
							 </a>
						</span>
					</td>
				</tr>";
		die();
	}
	
	//FUNGSI DIGUNAKAN FORM IGNORE WITHDRAW
	if( $enkripsi->decode($_GET['func']) == "ignWith" && isset($_GET['func'])){
		
		$iddata	=	$enkripsi->decode($_POST['id']);
		$sql	=	sprintf("SELECT NAMA, DEPOSIT
							 FROM t_withdraw A
							 LEFT JOIN m_murid B ON A.IDUSER = B.IDMURID
							 WHERE A.IDWITHDRAW = %s AND STATUS = 0
							 LIMIT 0,1"
							, $iddata
							);
		$result	=	$db->query($sql);
		$result	=	$result[0];
		
		echo "	<tr id='detail".$_POST['id']."' class='detail-con'>
					<td colspan='8' style='padding: 18px'>
						<h4>Data Murid - Withdraw Ditolak</h4><br>
						<i class='fa fa-lg fa-user'></i> Nama : ".$result['NAMA']."<br>
						<i class='fa fa-lg fa-tag'></i> Deposit : Rp. ".number_format($result['DEPOSIT'],0,',','.').",-<br><br>
						
						<div class='form-group'>
							<div class='g-recaptcha' data-sitekey='".$siteKey."'></div>
							<script type='text/javascript' src='".GOOGLE_RECAPTCHA_URL."api.js?hl=".$lang."'></script>
						</div>

						<span class='field'>
							Alasan Penolakan :		
							<input type='text' name='alasan' id='alasan' style='width:250px;' />
							<input type='hidden' id='idwith' name='idwith' value='".$_POST['id']."' />
							<a href='#' class='btn btn-kursusles btn-sm' style='padding: 3px;' onclick='procIgnoreWith(\"".$_POST['id']."\")'>
								<i class='fa fa-lg fa-floppy-o'></i> Simpan
							 </a>
						</span>
					</td>
				</tr>";
		die();
	}	

	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage	=	10;
		$startLimit		=	($_POST['page'] - 1) * $dataperpage;
		$_POST['tglto']	=	date('Y-m-d', strtotime($_POST['tglto']. "+1 days"));
		
		if($_POST['status'] <> '' && isset($_POST['status'])){
			$conStatus	=	"A.STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		//SELECT DATA
		$sql		=	sprintf("SELECT A.TANGGAL, B.NAMA, B.IDMURID, C.NO_REKENING AS REKENING_ASAL, C.ATAS_NAMA AS AN_ASAL,
										D.NAMA_BANK AS BANK_ASAL, E.NO_REKENING AS REKENING_TUJUAN, E.ATAS_NAMA AS AN_TUJUAN,
										F.NAMA_BANK AS BANK_TUJUAN, A.NOMINAL, A.STATUS, G.USERNAME, A.IDWITHDRAW
								 FROM t_withdraw A
								 LEFT JOIN m_murid B ON A.IDUSER = B.IDMURID
								 LEFT JOIN admin_rekening C ON A.IDREKENINGADMIN = C.IDREKENING
								 LEFT JOIN m_bank D ON C.IDBANK = D.IDBANK
								 LEFT JOIN m_rekening E ON A.IDREKENING = E.IDREKENING
								 LEFT JOIN m_bank F ON E.IDBANK = F.IDBANK
								 LEFT JOIN admin_user G ON A.USERAPPROVE = G.IDUSER
								 WHERE A.TANGGAL BETWEEN '%s' AND '%s' AND %s AND A.TYPE = 2"
								, $_POST['tglfrom']
								, $_POST['tglto']
								, $conStatus
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
				
				if($key['REKENING_ASAL'] <> ''){
					$rek_asal	=	"<i class='fa fa-lg fa-arrow-circle-right'></i> ".$key['REKENING_ASAL']."<br/>
									 <i class='fa fa-lg fa-user'></i> ".$key['AN_ASAL']."<br/>
									 <i class='fa fa-lg fa-building'></i> ".$key['BANK_ASAL']."<br/>";
				} else {
					$rek_asal	=	"-";
				}
				
				switch($key['STATUS']){
					case "0"	:	$status	=	"Menunggu Persetujuan";
									$tombol	=	"<a href='#' id='T".$enkripsi->encode($key['IDWITHDRAW'])."' class='btn btn-kursusles btn-sm' style='padding: 3px;' onclick='setWith(\"".$enkripsi->encode($key['IDWITHDRAW'])."\", true)'>
													<i class='fa fa-lg fa-check'></i> Setujui
												 </a>
												 <a href='#' id='T".$enkripsi->encode($key['IDWITHDRAW'])."' class='btn btn-kursusles btn-sm' style='padding: 3px;' onclick='setWith(\"".$enkripsi->encode($key['IDWITHDRAW'])."\", false)'>
													<i class='fa fa-lg fa-times'></i> Tolak
												 </a>";
									break;
					case "1"	:	$status	=	"Disetujui";
									$tombol	=	"-";
									break;
					case "-1"	:	$status	=	"Ditolak";
									$tombol	=	"-";
									break;
					default		:	$status	=	"Tidak Diketahui";
									$tombol	=	"-";
									break;
				}
				
				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDWITHDRAW'])."'>
									<td align='center'>".$key['TANGGAL']."</td>
									<td>".$key['NAMA']."</td>
									<td>
										".$rek_asal."
									</td>
									<td>
										<i class='fa fa-lg fa-arrow-circle-right'></i> ".$key['REKENING_TUJUAN']."<br/>
										<i class='fa fa-lg fa-user'></i> ".$key['AN_TUJUAN']."<br/>
										<i class='fa fa-lg fa-building'></i> ".$key['BANK_TUJUAN']."<br/>
									</td>
									<td align='right'>".number_format($key['NOMINAL'],0,',','.')."</td>
									<td>".$status."</td>
									<td>".$key['USERNAME']."</td>
									<td align='center'>
										".$tombol."
									</td>
								</tr>";
				$i++;
			}
		} else {
			$data	=	"	<tr><td colspan = '8'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}	

?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-pencil-square-o"></i> Transaksi</a></li>
        <li>Withdraw Assoc</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Withdraw Dana Saldo Voucher Siswa</h1>
</div>
<div id="contentwrapper" class="elements">
     <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Tanggal Pengajuan</label>
                <span class="field">
                	<input type="text" name="tglfrom" id="tglfrom" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-01')?>" style="width:75px; text-align: center;" />
                     s.d 
                	<input type="text" name="tglto" id="tglto" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-d')?>" style="width:75px; text-align: center;" />
                </span>
            </p>
            <p>
                <label>Status Withdraw</label>
                <span class="field">
                    <select id="status" name="status" class="form-control">
                        <option value="">- Semua Status -</option>
                        <option value="0">Menunggu Persetujuan</option>
                        <option value="1">Disetujui</option>
                        <option value="-1">Ditolak</option>
                    </select>
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
            	<table id="standart_table" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Tanggal</td>
                            <td class="head1" style="text-align:center;">Nama</td>
                            <td class="head0" style="text-align:center;">Rekening Asal</td>
                            <td class="head1" style="text-align:center;">Rekening Tujuan</td>
                            <td class="head0" style="text-align:center;">Nominal</td>
                            <td class="head1" style="text-align:center;">Status</td>
                            <td class="head0" style="text-align:center;">User Approve</td>
                            <td class="head1" style="text-align:center;"> </td>
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
	function procIgnoreWith(id){
		var data	=	$('#detail'+id+' input, #detail'+id+' textarea').serialize();
		$('#detail'+id+' input, #detail'+id+' textarea').prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg("Sedang menyimpan"));
		$.post( "<?=APP_URL?>page/301withdrawassoc.php?func=<?=$enkripsi->encode('procIgnoreWith')?>", data)
		.done(function( data ) {
			data	=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				filterData(1);
				$('#message_response_container').slideUp('fast').html('');
			}
			$('#detail'+id+' input, #detail'+id+' textarea').prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			grecaptcha.reset();
		});
	}
	function saveWith(id){
		var data	=	$('#detail'+id+' input, #detail'+id+' select, #detail'+id+' textarea').serialize();
		$('#detail'+id+' input, #detail'+id+' select, #detail'+id+' textarea').prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg("Sedang menyimpan"));
		$.post( "<?=APP_URL?>page/301withdrawassoc.php?func=<?=$enkripsi->encode('saveWith')?>", data)
		.done(function( data ) {
			data	=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				filterData(1);
				$('#message_response_container').slideUp('fast').html('');
			}
			$('#detail'+id+' input, #detail'+id+' select, #detail'+id+' textarea').prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			grecaptcha.reset();
		});
	}
	function setWith(id, status){
		$('.detail-con').slideUp('fast').remove();
		if(status == true){
			$.post( "<?=APP_URL?>page/301withdrawassoc.php?func=<?=$enkripsi->encode('appWith')?>", {id:id})
			.done(function( data ) {
				$('#baris'+id).after(data);
				getDataOpt('getDataRekeningAdmin','noparam','rekeningadmin','','- Pilih Rekening Asal -');
			});
		} else {
			$.post( "<?=APP_URL?>page/301withdrawassoc.php?func=<?=$enkripsi->encode('ignWith')?>", {id:id})
			.done(function( data ) {
				$('#baris'+id).after(data);
			});
		}
	}
	function filterData(page){
		var tglfrom	=	$('#tglfrom').val();
			tglto	=	$('#tglto').val();
			status	=	$('#status').val();

		$('#list_data').html("<tr><td colspan = '7'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/301withdrawassoc.php?func=<?=$enkripsi->encode('filterData')?>", {tglto: tglto, tglfrom:tglfrom, status:status, page:page})
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