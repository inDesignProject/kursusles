<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	
	//FUNGSI ADD BOOKMARK
	if( $enkripsi->decode($_GET['func']) == "addBookmark" && isset($_GET['func'])){
		
		if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 2){
			echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
			die();
		} else {
			$idkary	=	$_SESSION['KursusLesLoker']['IDUSER'];
		}

		$idp		=	$enkripsi->decode($_POST['iddata']);
		$sqlCek		=	sprintf("SELECT IDBOOKMARK FROM t_bookmark
								 WHERE IDPEMILIK = %s AND JNSPEMILIK = 2 AND JNSBOOKMARK = 1 AND IDCHILD = %s"
								, $idkary
								, $idp
								);
		$resultCek	=	$db->query($sqlCek);
		
		if($resultCek <> false && $resultCek <> ''){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Data bookmark sudah ada sebelumnya"));
			die();
		} else {
			
			$sqlInsB=	sprintf("INSERT t_bookmark
								 (JNSPEMILIK, JNSBOOKMARK, IDPEMILIK, IDCHILD, TGLTAMBAH)
								 VALUES
								 (2, 1, %s, %s, NOW())"
								, $idkary
								, $idp
								);	
			$affected	=	$db->execSQL($sqlInsB, 0);

			//JIKA DATA SUDAH MASUK, KIRIM RESPON
			if($affected > 0){
				echo json_encode(array("respon_code"=>"00000", "respon_msg"=>""));
				die();
			} else {
				echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Gagal menambahkan data bookmark. Silakan coba lagi nanti"));
				die();
			}

		}

	}	

	//CEK SESSION UNTUK FORM LOGIN
	$show_login	=	$session->cekSession() == 2 ? "false" : "true";
	
	$idloker	=	$enkripsi->decode($_GET['i']);
	//CARI BERDASARKAN ID LOWONGAN
	$sql		=	sprintf("SELECT B.NAMA_PERUSAHAAN, A.JUDUL, C.NAMA_BIDANG, D.NAMA_POSISI, E.NAMA_PROPINSI, F.NAMA_KOTA, 
									A.STATUS_KONTRAK, A.TGL_PUBLISH, A.TGL_KADALUARSA, A.TGL_MULAI_KERJA, A.TIPE_GAJI, A.GAJI_MIN,
									A.GAJI_MAX, A.TAMPIL_GAJI, A.USIA_MAX, A.JKP, A.JKW, A.FASILITAS, A.LEVEL_KARIR, G.NAMA_PENDIDIKAN,
									A.EMAIL_LOWONGAN, A.FRESH_GADUATE, A.PENGALAMAN_KERJA, A.KETERANGAN_TAMBAHAN
							 FROM t_lowongan A
							 LEFT JOIN m_perusahaan B ON A.IDPERUSAHAAN = B.IDPERUSAHAAN
							 LEFT JOIN m_bidang C ON A.IDBIDANG = C.IDBIDANG
							 LEFT JOIN m_posisi D ON A.IDPOSISI = D.IDPOSISI
							 LEFT JOIN m_propinsi E ON A.IDPROPINSI = E.IDPROPINSI
							 LEFT JOIN m_kota F ON A.IDKOTA = F.IDKOTA
							 LEFT JOIN m_pendidikan G ON A.IDPENDIDIKAN = G.IDPENDIDIKAN
							 WHERE A.IDLOWONGAN = %s AND A.STATUS = 1
							 LIMIT 0,1"
							, $idloker
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	
	$sqlfas		=	sprintf("SELECT NAMAFASILITAS FROM m_fasilitas WHERE IDFASILITAS IN (%s)", $result['FASILITAS']);
	$resultfas	=	$db->query($sqlfas);
	
	if($resultfas <> '' && $resultfas <> false){
		$datafas		=	"";
		foreach($resultfas as $key){
			$datafas	.=	"<small style='margin-left: 6px'>- ".$key['NAMAFASILITAS']."</small><br/>";
		}
	} else {
		$datafas		=	"<small style='margin-left: 6px'><center>Tidak ada data</center></small>";
	}
	
	$tglmulai	=	$result['TGL_MULAI_KERJA'] == "" ? "Tidak ditentukan" : $result['TGL_MULAI_KERJA'];
	$usiamaks	=	$result['USIA_MAX'] == "0" ? "Tidak ditentukan" : $result['USIA_MAX']." Tahun";
	$pendidikan	=	$result['NAMA_PENDIDIKAN'] == "" ? "Tidak ditentukan" : $result['NAMA_PENDIDIKAN'];
	$freshgrad	=	$result['FRESH_GADUATE'] == "1" ? "dipersilakan" : "tidak diijinkan";
	$pengalaman	=	$result['PENGALAMAN_KERJA'] == "" ? "Tidak ditentukan" : $result['PENGALAMAN_KERJA']." Tahun";
	$keterangan	=	$result['KETERANGAN_TAMBAHAN'] == "" ? "Tidak ada data" : $result['KETERANGAN_TAMBAHAN'];
	$gajiawal	=	number_format($result['GAJI_MIN'], 0, ',', '.');
	$gajiakhir	=	number_format($result['GAJI_MAX'], 0, ',', '.');
	
	switch($result['STATUS_KONTRAK']){
		case "1"	:	$statuskerja	=	"Tetap"; break;
		case "2"	:	$statuskerja	=	"Kontrak"; break;
		case "3"	:	$statuskerja	=	"Berjangka"; break;
		case "4"	:	$statuskerja	=	"Freelance"; break;
		case "5"	:	$statuskerja	=	"Paruh Waktu"; break;
		case "6"	:	$statuskerja	=	"Bekerja dirumah"; break;
		default		:	$statuskerja	=	"Tidak diketahui"; break;
	}
	
	switch($result['LEVEL_KARIR']){
		case "0"	:	$levelkarir		=	"Awal"; break;
		case "1"	:	$levelkarir		=	"Pertengahan"; break;
		case "2"	:	$levelkarir		=	"Senior"; break;
		case "3"	:	$levelkarir		=	"Teratas"; break;
		default		:	$levelkarir		=	"Tidak diketahui"; break;
	}
	
	switch($result['TIPE_GAJI']){
		case "1"	:	$tipegaji		=	"Bulanan"; break;
		case "2"	:	$tipegaji		=	"Mingguan"; break;
		case "3"	:	$tipegaji		=	"Per Jam"; break;
		default		:	$tipegaji		=	"Tidak diketahui"; break;
	}
	
	if($result['JKP'] == 1 && $result['JKW'] == 1){
		$jnskelamin	=	"Pria atau Wanita";
	} else if($result['JKP'] == 1 && $result['JKW'] == 0){
		$jnskelamin	=	"Pria";
	} else if($result['JKP'] == 0 && $result['JKW'] == 1){
		$jnskelamin	=	"Wanita";
	} else {
		$jnskelamin	=	"Pria atau Wanita";
	}
	
	//HABIS - CEK SESSION
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
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>
        
		<div class="container" style="font-family: Verdana,Arial,Helvetica,sans-serif;">
			<h3 class="text-left text_kursusles page-header">
            	DETAIL LOWONGAN KERJA
                <?php
					if($session->cekSession() == 2 && $_SESSION['KursusLesLoker']['TYPEUSER'] == 2){
				?>
                <span class="pull-right">
                    <a class="btn btn-custom btn-xs" onclick="addBookmark('<?=$_GET['i']?>')" style="padding: 4px">
                        <i class="fa fa-bookmark"></i> Bookmark
                    </a>
                    <a class="btn btn-custom btn-xs" href="<?=APP_URL."lamar?i=".$_GET['i']?>" style="padding: 4px">
                        <i class="fa fa-check-square-o"></i> Lamar Sekarang
                    </a>
                </span>
                <?php
					}
				?>
            </h3>
			
            <?php
			if($result <> false && $result <> ''){
			?>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="boxSquareWhite">
						<h4><?=$result['JUDUL']?></h4><br/>
                        <span>Dipublikasi oleh : <strong><?=$result['NAMA_PERUSAHAAN']?></strong></span><br/>
                        <i class="fa fa-calendar"></i> Tanggal Publikasi <strong><?=$result['TGL_PUBLISH']?> s/d <?=$result['TGL_KADALUARSA']?></strong> - 
                        <i class="fa fa-calendar"></i> Tanggal Mulai Kerja <strong><?=$tglmulai?></strong><br/>
                        <i class="fa fa-envelope"></i> Email Lowongan <strong><?=$result['EMAIL_LOWONGAN']?></strong><br/>
                        <i class="fa fa-external-link"></i> Level Karir <strong><?=$levelkarir?></strong>
					</div>
                </div>
            </div><br/>

			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="height: 100%">
					<div class="boxSquareWhite">
                    	<h4>Penjelasan</h4><br/>
                        - Bidang Pekerjaan : <?=$result['NAMA_BIDANG']?><br/>
                        - Posisi : <?=$result['NAMA_POSISI']?><br/>
                        - Status Kerja : <?=$statuskerja?><br/>
                        - Penempatan : <?=$result['NAMA_KOTA']?>, <?=$result['NAMA_PROPINSI']?><br/>
					</div>
                </div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
					<div class="boxSquareWhite">
                    	<h4>Kualifikasi</h4><br/>
                        - <?=$jnskelamin?><br/>
                        - Usia maksimal <?=$usiamaks?><br/>
                        - Pendidikan minimal <?=$pendidikan?><br/>
                        - Fresh graduate <?=$freshgrad?> untuk melamar<br/>
                        - Pengalaman kerja minimal <?=$pengalaman?><br/>
					</div>
                </div>
            </div><br/>
            
            <?php
			if($result['TAMPIL_GAJI'] == 1){
            ?>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="boxSquareWhite">
                    	<h4>Gaji Yang Ditawarkan</h4><br/>
                        Jenis Gaji : <?=$tipegaji?>
                        Dengan range gaji sebesar Rp. <?=$gajiawal?>,- s/d Rp. <?=$gajiakhir?>,-
					</div>
                </div>
            </div><br/>
            <?php
			}
            ?>
            
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="boxSquareWhite">
                    	<h4>Fasilitas Yang Didapat</h4><br/>
                        <?=$datafas?>
					</div>
                </div>
            </div><br/>
            
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="boxSquareWhite">
                    	<h4>Keterangan Tambahan</h4><br/>
                        <p><?=$keterangan?></p><br/>
					</div>
                </div>
            </div><br/>
			<?php
			} else {
			?>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="boxSquareWhite">
                    <center><b>Halaman tidak ditemukan</b></center>
                </div>
            </div><br/><br/>
			<?php
			}
            ?>            

        </div>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
        <script>
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
							"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
							"<strong><small id='message_response'>"+msg+"</small></strong>"+
						"</div>";
			}
			function addBookmark(value){
				$('#message_response_container').slideUp('fast').html("");
				$.post("<?=APP_URL?>lowongan?func=<?=$enkripsi->encode('addBookmark')?>", {iddata : value})
				.done(function( data ) {
					
					data			=	JSON.parse(data);
					if(data['respon_code'] == "00000"){
						$('#message_response_container').slideDown('fast').html(generateMsg("Bookmark sudah ditambahkan. Cek bookmark di halaman utama pada tab Bookmark"));
					} else {
						$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
					}
			
				});
				
			}
		</script>
    	<?=$session->getTemplate('footer')?>

    </body>
</html>