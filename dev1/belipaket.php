<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	include('php/lib/recaptchalib.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	session_start();
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
	//CEK TIPE LOGIN - HARUS SEBAGAI MURID
	$body			=	'';
	if($_SESSION['KursusLes']['TYPEUSER'] <> 1){
		
		$body		=	"<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
							<div class='boxSquare'>
								<center>
									<b>
										Silakan login sebagi murid terlebih dahulu<br/>
										<a href='#' onClick='window.location.href = \"".APP_URL."login?authResult=".$enkripsi->encode('5')."&rdr=".APP_URL."belipaket;q=".$_GET['q']."\"'>
											Login Murid
										</a>
									</b>
								</center>
							 </div>
						 </div>";
						 
	} else if(!isset($_GET['q']) || $_GET['q'] == ''){
		$body		=	"<div class='boxSquare'><center><b>Halaman tidak tersedia</b></center></div>";
	} else {
		$idmurid	=	$_SESSION['KursusLes']['IDUSER'];
	}
	
	//DATA SALDO DEPOSTI MURID
	$sqlSaldo		=	sprintf("SELECT DEPOSIT
								 FROM m_murid
								 WHERE IDMURID = %s
								 LIMIT 0,1"
								, $idmurid
								);
	$resultSaldo	=	$db->query($sqlSaldo);
	$resultSaldo	=	$resultSaldo[0];
	$saldo_deposit	=	$resultSaldo['DEPOSIT'];	
	
	//DATA PAKET PENGAJAR
	if($_GET['q'] <> '' && isset($_GET['q'])){
		$idpaket		=	$enkripsi->decode($_GET['q']);
		$sqlPaket		=	sprintf("SELECT A.IDPAKET, A.NAMA_PAKET, C.NAMA_MAPEL, A.JENIS, A.JUMLAH_MURID, A.TEMPAT,
											A.MATERI, A.WAKTU, A.HARGA, A.TOTAL_PERTEMUAN
									 FROM t_paket A
									 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
									 LEFT JOIN m_mapel C ON B.IDMAPEL = C.IDMAPEL
									 WHERE A.IDPAKET = %s
									 LIMIT 0,1"
									, $idpaket
									);
		$resultPaket	=	$db->query($sqlPaket);
		
		if($resultPaket <> false && $resultPaket <> ''){
			$resultPaket=	$resultPaket[0];
			$jenis		=	$resultPaket['JENIS'] == "1" ? "Privat" : "Grup";
			$harga_paket=	$resultPaket['HARGA'];
			
			$detailPaket=	"<ul style='list-style:none'>".
							"	<li class='namapaket'>".$resultPaket['NAMA_PAKET']." - ".$resultPaket['NAMA_MAPEL']."</li>".
							"	<li class='jenispaket'>".$jenis." ( ".$resultPaket['JUMLAH_MURID']." Murid ) - ".$resultPaket['TOTAL_PERTEMUAN']." Pertemuan </li>".
							"	<li class='harga'>Rp. ".number_format($resultPaket['HARGA'],0,',','.')." </li>".
							"	<li class='waktu'>".$resultPaket['WAKTU']." Jam </li>".
							"	<li class='lokasi'>".$resultPaket['TEMPAT']." </li>".
							"	<li class='materi'>".$resultPaket['MATERI']."</li>".
							"</ul><br/>";
		} else {
			$detailPaket=	"<center><b>Tidak ada detail yang ditampilkan</b></center>";
		}
		$status_deposit		=	$harga_paket > $saldo_deposit ? "<b style='color: red'>Saldo Tidak mencukupi</b>" : "<b style='color: green'>Saldo mencukupi</b>";
		$hargaperpertemuan	=	$resultPaket['HARGA'] / $resultPaket['TOTAL_PERTEMUAN'];
	}
	//HABIS -- DATA PAKET PENGAJAR
	
	//FUNGSI DIGUNAKAN BELI PAKET
	if( $enkripsi->decode($_GET['func']) == "submitBeli" && isset($_GET['func'])){
		
		//CEK STUJU
		if($_POST['stuju'] == '' || !isset($_POST['stuju'])){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Silakan cek pada tanda persetujuan Syarat & Ketentuan untuk melanjutkan"));
			die();
		}

		//CEK G-RECAPTCHA
		if($_POST['g-recaptcha-response'] == '' || !isset($_POST['g-recaptcha-response'])){
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Silakan cek box reCaptcha untuk melanjutkan"));
			die();
		}
		
		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'00003',
								   "respon_msg"=>"Captcha yang anda masukkan tidak valid")
						    );
			die();
		}
		
		$idmurid	=	$enkripsi->decode($_POST['usr']);
		$idpaket	=	$enkripsi->decode($_POST['idp']);
		
		$sqlPaket	=	sprintf("SELECT A.TOTAL_PERTEMUAN, A.HARGA, C.NAMA, C.EMAIL
								 FROM t_paket A
								 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
								 LEFT JOIN m_pengajar C ON B.IDPENGAJAR = C.IDPENGAJAR
								 WHERE A.IDPAKET = %s
								 LIMIT 0,1"
								, $idpaket
								);
		$resultPkt	=	$db->query($sqlPaket);
		$resultPkt	=	$resultPkt[0];
		$jmlpert	=	$resultPkt['TOTAL_PERTEMUAN'];
		$rptotal	=	$resultPkt['HARGA'];
		$rpperpert	=	$rptotal / $jmlpert;
		$tglexp		=	date('Y-m-d', strtotime("+180 days"));
		$email		=	$resultPkt['EMAIL'];
		$nama		=	$resultPkt['NAMA'];
		
		$sqlIns		=	sprintf("INSERT INTO t_pengajuan_jasa
								 (IDMURID, IDPAKET, JUMLAH_PERTEMUAN, TGL_AWAL, TGL_KADALUARSA, RP_PERPERTEMUAN, RP_TOTAL)
								 VALUES
								 (%s, %s, %s, NOW(), '%s', %s, %s)"
								, $idmurid
								, $idpaket
								, $jmlpert
								, $tglexp
								, $rpperpert
								, $rptotal
								);
		$affected	=	$db->execSQL($sqlIns, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								
								<p>
								paket kursus yang anda tawarkan di KursusLes.com baru saja dipesan oleh seorang murid. Silakan cek info pemesanan
								di halaman utama - menu <b>Kursus Saya</b> anda setelah masuk untuk menerima atau menolak permintaan. Jika sampai dengan 2 hari setelah email
								ini kami kirimkan tidak ada respon, maka secara otomatis data permintaan akan ditolak oleh sistem.
								<br/><br/>
								Demikian pemberitahuan dari kami.
								</p>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Pemberitahuan Pemesanan Paket Kursus", $message);
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data permintaan kursus terkirim"));
		} else {
			echo json_encode(array("respon_code"=>"00004", "respon_msg"=>"Gagal menyimpan, silakan coba lagi nanti"));
		}
		die();
	}
	
	header('Content-type: text/html; charset=utf-8');

?>

<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<title>TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!</title>
		<meta name="description" content="TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!">
		<meta name="author" content="inDesign Project">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile">
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
    
		<style>
			.tab_list{margin-bottom: 4px !important; width: 100%;}
			
			.lokasi{background:url("<?=APP_IMG_URL?>icon/lokasi.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
			.materi{background:url("<?=APP_IMG_URL?>icon/materi_paket.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
			.jenispaket{background:url("<?=APP_IMG_URL?>icon/jenis_paket.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
			.harga{background:url("<?=APP_IMG_URL?>icon/harga.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
			.waktu{background:url("<?=APP_IMG_URL?>icon/waktu.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
			.namapaket{background:url("<?=APP_IMG_URL?>icon/nama_paket.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
		</style>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login)?>
        
        <div class="container">
        	<h3 class="text-left text_kursusles page-header">
            	BELI PAKET PENGAJAR
            </h3>
        	<?php
				if($body <> ''){
					echo $body;
				} else {
			?>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="beli-container">
                <div class="boxSquare">
                    <div class="panel panel-default">
                        <div class="panel-heading"><b>Detail Paket</b></div>
                        <div class="panel-body" id="detailPaket">
                        	<?=$detailPaket?>
                        </div>
                    </div>
                </div>
                <div class="boxSquare">
                    <div class="panel panel-default">
                        <div class="panel-heading"><b>Form Pembelian</b></div>
                        <div class="panel-body">
                            <form action="" id="beli" method="post">
                                Saldo Deposit : Rp. <?=number_format($saldo_deposit, 0, ',', '.')?>,-<br/>
                                Status Deposit : <?=$status_deposit?><br/><br/>
                                <div class="boxSquare">
                                	<b>Syarat & Ketentuan :</b><br/>
                                    <ul>
                                    	<li>Anda menyatakan bahwa sudah mengetahui jadwal mengajar dari pengajar pemilik paket ini</li>
                                    	<li>Deposit anda akan secara otomatis berkurang sesuai dengan harga paket yang dibeli dan disimpan oleh admin sebagai pihak ke-tiga</li>
                                    	<li>Deposit dapat dikembalikan jika permintaan pembelian paket ditolak oleh pangajar</li>
                                    	<li>Paket akan berlaku selama 6 (enam) bulan terhitung sejak pembelian paket</li>
                                    	<li>Harga paket per pertemuan dalam hal ini harga total (Rp. <?=number_format($resultPaket['HARGA'],0,',','.')?>) dibagi jumlah pertemuan (<?=$resultPaket['TOTAL_PERTEMUAN']?>) adalah Rp. <?=number_format($hargaperpertemuan,0,',','.')?>,-</li>
                                    	<li>Harga paket per pertemuan tersebut di atas akan dipindahkan kepada pengajar jika ada verifikasi kegiatan belajar - mengajar dari kedua belah pihak (dalam hal ini adalah anda sebagai Murid dan Pengajar pemilik paket)</li>
                                    	<li>Admin secara berkala akan mengirimkan email peringatan jika selama 2 bulan tidak ada kegiatan belajar-mengajar</li>
                                    	<li>Apabila tidak ada kegiatan yang berlangsung selama masa berlaku voucher, maka voucher akan dipindahkan otomatis kepada pengajar dan admin dengan prosentase yang telah ditentukan oleh admin</li>
                                    	<li>Selanjutnya, untuk jadwal kegiatan belajar - mengajar ke-dua belah pihak yang akan menentukan</li>
                                    </ul>
                                </div><br/><br/>
                                <input name="stuju" value="1" id="stuju" type="checkbox"/> Saya menyetujui semua Syarat & Ketentuan diatas, kirim permintaan kursus saya ke pengajar sekarang..<br/>
                                <div class="form-group">
                                    <div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
                                    <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script>
                                </div>
    
                                <span class="devider"></span>
                                <input type="hidden" id="idp" name="idp" value="<?=$_GET['q']?>" />
                                <input type="hidden" id="usr" name="usr" value="<?=$enkripsi->encode($idmurid)?>" />
                                <input type="button" id="submit" name="submit" value="Beli" class="btn btn-sm btn-custom2 pull-right" onClick="submitBeli()"/>
                            </form>                            
                        </div>
                    </div>
                </div>
            </div>
            <?php
				}
			?>
        </div>
		<br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
		<script>
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
						"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
						"<strong><small id='message_response'>"+msg+"</small></strong>"+
					"</div>";
			}
			function submitBeli(){
				var data	=	$('#beli input, #beli textarea, #beli select, #beli radio, #beli hidden').serialize();
				
				$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
				$.post( "<?=APP_URL?>belipaket?func=<?=$enkripsi->encode('submitBeli')?>", data)
				.done(function( data ) {
					
					data	=	JSON.parse(data);
					if(data['respon_code'] != '00000'){
						$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
						grecaptcha.reset();
					} else {
						$('#message_response_container').slideDown('fast').html("");
						$('#beli-container').html("<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'><div class='boxSquare'><center><b>Data permintaan pembelian sudah terkirim.<br/>Silakan tunggu persetujuan dari pengajar. Kami akan memberikan notifikasinya via email</b></center></div></div>");
					}
		
				});
				return true;
			}
        </script>
        
    	<?=$session->getTemplate('footer')?>

    </body>
</html>