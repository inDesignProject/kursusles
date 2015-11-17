<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	require_once "php/lib/recaptchalib.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	if($session->cekSession() <> 2 && $enkripsi->decode($_GET['func']) <> "submitForm"){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('3')."'</script>";
		 die();
	}

	//if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 1){
	//	 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
	//	 die();
	//}

	$sqlFas		=	sprintf("SELECT IDFASILITAS, NAMAFASILITAS FROM m_fasilitas ORDER BY NAMAFASILITAS");
	$resultFas	=	$db->query($sqlFas);
	$listFas	=	'';
	
	foreach($resultFas as $key){
		
		$idFas		=	$enkripsi->encode($key['IDFASILITAS']);
		$listFas	.=	"<input type='checkbox' name='cek[".$idFas."]' id='cek".$idFas."' value='".$idFas."'> ".$key['NAMAFASILITAS']."<br/>";
		
	}

		$sqlXyz = '';
		$sqlXyz .= "SELECT idperusahaan FROM m_perusahaan WHERE iduser = '".$_SESSION['KursusLesLoker']['IDPRIME']."'";
		$resultXyz	=	$db->query($sqlXyz);
		
		
	var_dump($resultXyz);
	if ($resultXyz !== false){
	//FUNGSI DIGUNAKAN UNTUK SIMPAN DATA
	if( $enkripsi->decode($_GET['func']) == "submitForm" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				$respon_msg	=	'';
				switch($key){
					case "judul"		:	$respon_msg	=	"Harap isikan judul lowongan"; break;
					case "bidang"		:	$respon_msg	=	"Pilih bidang pekerjaan terlebih dahulu"; break;
					case "posisi"		:	$respon_msg	=	"Pilih Posisi yang ditawarkan ke pencari kerja"; break;
					case "statuspen"	:	$respon_msg	=	"Berikan informasi status pekerjaan dengan memilih status kerja"; break;
					case "propinsi"		:	$respon_msg	=	"Harap pilih propinsi penempatan kerja"; break;
					case "kota"			:	$respon_msg	=	"Harap pilih kota penempatan kerja"; break;
					case "pengalaman"	:	$respon_msg	=	"Isikan minimal lama pengalaman kerja"; break;
					case "tgltfrom"		:	$respon_msg	=	"Harap pilih tanggal awal penayangan"; break;
					case "tgltto"		:	$respon_msg	=	"Harap pilih tanggal akhir penayangan"; break;
					case "email"		:	$respon_msg	=	"Harap isi email untuk memberikan pesan pemberitahuan"; break;
				}

				if($respon_msg <> ''){
					echo json_encode(array("respon_code"=>"00001", "respon_msg"=>$respon_msg));
					die();
				}
		
			} else {
				${$key}		=	str_replace("'","",$value);
			}
		}

		//CEK DATA KIRIMAN CAPTCHA
		if($_POST['g-recaptcha-response'] == "" || !isset($_POST['g-recaptcha-response'])){
			echo json_encode(array("respon_code"=>"000002", "respon_msg"=>"Untuk melanjutkan harap centang pada kotak Recaptcha"));
			die();
		} else {
			$cekdata	=	$session->cekGCaptcha($_POST['g-recaptcha-response']);
			if($cekdata <> 2){
				echo json_encode(array("respon_code"=>"000003", "respon_msg"=>"Kiriman Recaptcha tidak valid"));
				die();
			}
		}
		
		//CEK KIRIMAN YANG DIENKRIPSI
		$bidang			=	$enkripsi->decode($bidang);
		$posisi			=	$enkripsi->decode($posisi);
		$propinsi		=	$enkripsi->decode($propinsi);
		$kota			=	$enkripsi->decode($kota);
		$pendidikan		=	$enkripsi->decode($pendidikan);
		
		//CEK KIRIMAN BERUPA CHECKBOX
		$jkp			=	isset($_POST['cekjkp']) && $_POST['cekjkp'] <> '' ? "1" : "0";
		$jkw			=	isset($_POST['cekjkw']) && $_POST['cekjkw'] <> '' ? "1" : "0";
		$cekfg			=	isset($_POST['cekfg']) && $_POST['cekfg'] <> '' ? "1" : "0";
		$cekgaji		=	isset($_POST['cekgaji']) && $_POST['cekgaji'] <> '' ? "1" : "0";
		$cekFas			=	'';
		
		if(isset($_POST['cek'])){
			foreach($_POST['cek'] as $key => $value){
				$value		=	$enkripsi->decode($value);
				$cekFas		.=	$value.",";
			}
			$cekFas			=	$cekFas <> '' ? substr($cekFas, 0, strlen($cekFas) - 1) : "";
		}
		
		
		$sqlIns			=	sprintf("INSERT INTO t_lowongan
									 (IDPERUSAHAAN, JUDUL, IDBIDANG, IDPOSISI, IDPROPINSI,IDKOTA,
									  STATUS_KONTRAK, TGL_PUBLISH, TGL_KADALUARSA, TGL_MULAI_KERJA,
									  TIPE_GAJI, GAJI_MIN, GAJI_MAX, TAMPIL_GAJI,
									  USIA_MAX, JKP, JKW, FASILITAS, LEVEL_KARIR,
									  IDPENDIDIKAN, EMAIL_LOWONGAN, FRESH_GADUATE,
									  PENGALAMAN_KERJA, KETERANGAN_TAMBAHAN, SKILL_KEYWORDS)
									 VALUES
									 ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 
									  '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
									  '%s', '%s', '%s', '%s', '%s')"
									, $_SESSION['KursusLesLoker']['IDUSER']
									, $db->db_text($judul)
									, $bidang
									, $posisi
									, $propinsi
									, $kota
									, $statuspen
									, $tgltfrom
									, $tgltto
									, $tglkerja
									, $jnsgaji
									, $gajiawal
									, $gajiakhir
									, $cekgaji
									, $usia
									, $jkp
									, $jkw
									, $cekFas
									, $levelkarir
									, $pendidikan
									, $db->db_text($email)
									, $cekfg
									, $pengalaman
									, $db->db_text($keterangan)
									, $db->db_text($keywords)
									);

		//EKSEKUSI SQL, DAPATKAN LAST ID
		$lastID			=	$db->execSQL($sqlIns, 1);
		
		//JIKA LAST ID > 0
		if($lastID > 0){
			echo json_encode(array("respon_code"=>"000000", "respon_msg"=>"Data tersimpan"));
			die();
		} else{
			echo json_encode(array("respon_code"=>"000004", "respon_msg"=>"Gagal menyimpan data. Silakan coba lagi nanti".$sqlIns));
			die();
		}
		
		die();
		
	}
} // endnya jika belum daftar jadi perusahaan
	//CEK SESSION UNTUK FORM LOGIN
	$show_login	=	$session->cekSession() == 2 ? "false" : "true";
	
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
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile" />
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssdatepicker');?>.cssfile" />
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>

		<div class="container">
			<h3 class="text-left text_kursusles page-header">POSTING LOWONGAN</h3>
            <form action="" id="postjob" autocomplete="off" class="form-horizontal">

                <!-- UTAMA -->
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
                            <div class="form-group">
                                <label for="judul" class="col-sm-3 control-label">
                                    Judul 
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="judul" name="judul" maxlength="75" placeholder="Judul Lowongan" required />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bidang" class="col-sm-3 control-label">
                                    Bidang Kerja
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <select id="bidang" name="bidang" class="form-control" required>
                                        <option value="">- Pilih Bidang Kerja -</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="posisi" class="col-sm-3 control-label">
                                    Posisi / Jabatan
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <select id="posisi" name="posisi" class="form-control" required>
                                        <option value="">- Pilih Posisi -</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cekjk" class="col-sm-3 control-label">
                                    Jenis Pelamar
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                	<input type="checkbox" name="cekjkp" id="cekjkp" value="1" checked> Pria
                                	<input type="checkbox" name="cekjkw" id="cekjkw" value="1" checked> Wanita
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="usia" class="col-sm-3 control-label">
                                    Usia Maksimal 
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="usia" name="usia" maxlength="2" placeholder="Usia Maksimal" required style="text-align:right; width: 60%; display:inline" /> Tahun
                                </div>
                            </div>
                        </div><br/>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="boxSquare segmenDaftar" style="padding: 12px">
                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                            <p>
                            	* Judul akan memudahkan pencari kerja untuk menemukan iklan anda<br/>
                                * Kosongkan umur maksimal untuk mengabaikan syarat umur pancari kerja
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- STATUS PENEMPATAN -->
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
                            <div class="form-group">
                                <label for="statuskerja" class="col-sm-3 control-label">
                                    Status Kerja
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <div class="col-sm-4">
                                        <input name="statuspen" value="1" id="status1" type="radio" checked/> <small>Tetap</small> <br/>
                                        <input name="statuspen" value="2" id="status2" type="radio"/> <small>Kontrak</small> <br/>
                                        <input name="statuspen" value="3" id="status3" type="radio"/> <small>Berjangka</small>
                                    </div>
                                    <div class="col-sm-5">
                                        <input name="statuspen" value="4" id="status4" type="radio"/> <small>Freelance</small> <br/>
                                        <input name="statuspen" value="5" id="status5" type="radio"/> <small>Paruh Waktu</small> <br/>
                                        <input name="statuspen" value="5" id="status6" type="radio"/> <small>Kerja Dirumah</small> <br/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="penempatankerja" class="col-sm-3 control-label">
                                    Penempatan Kerja
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <select id="propinsi" name="propinsi" class="form-control" required onChange="getDataOpt('getDataKota','propinsi='+this.value,'kota','','- Pilih Kota Penempatan Kerja -');">
                                        <option value="">- Pilih Penempatan Kerja -</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="kotakerja" class="col-sm-3 control-label">
                                    Kota
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <select id="kota" name="kota" class="form-control" required>
                                        <option value="">- Pilih Kota Penempatan Kerja -</option>
                                    </select>
                                </div>
                            </div>
                        </div><br/>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    </div>
                </div>
                        
                <!-- PENDIDIKAN & PENGALAMAN -->
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
                            <div class="form-group">
                                <label for="levelkarir" class="col-sm-3 control-label">
                                    Level Karir
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <select id="levelkarir" name="levelkarir" class="form-control" required>
                                        <option value="1">Awal</option>
                                        <option value="2">Pertengahan</option>
                                        <option value="3">Senior</option>
                                        <option value="4">Teratas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pendidikan" class="col-sm-3 control-label">
                                    Pendidikan Minimal
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <select id="pendidikan" name="pendidikan" class="form-control" required>
                                        <option value="">- Pilih Minimal Pendidikan -</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cekFG" class="col-sm-3 control-label">
                                    Terima Fresh Graduate
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                	<input type="checkbox" name="cekfg" id="cekfg" value="1"> Ya
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pengalaman" class="col-sm-3 control-label">
                                    Pengalaman Kerja 
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="pengalaman" name="pengalaman" maxlength="2" placeholder="Pengalaman Kerja Minimum" required style="text-align:right; width:60%; display:inline" /> Tahun
                                </div>
                            </div>
                        </div><br/>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    </div>
                </div>

                <!-- TANGGAL -->
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
                            <div class="form-group">
                                <label for="tanggaltayang" class="col-sm-3 control-label">
                                    Tanggal Tayang
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="tgltfrom" id="tgltfrom" class="form-control" maxlength="10" autocomplete="off" readonly style="width:40%; display:inline" /> s/d 
                                    <input type="text" name="tgltto" id="tgltto" class="form-control" maxlength="10" autocomplete="off" readonly style="width:40%; display:inline" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tanggalkerja" class="col-sm-3 control-label">
                                    Tanggal Mulai Kerja
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="tglkerja" id="tglkerja" class="form-control" maxlength="10" autocomplete="off" readonly style="width:40%" />
                                </div>
                            </div>
                        </div><br/>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="boxSquare segmenDaftar" style="padding: 12px">
                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                            <p>
                            	* Tanggal tayang akan menentukan waktu penayangan iklan. Setelah melebihi waktu tayang, maka iklan akan dianggap kadaluarsa<br/>
                                * Tanggal mulai kerja akan memberikan informasi kepada pencari kerja kapan mereka akan siap untuk bekerja (Opsional)
                            </p>
                        </div>
                    </div><br/>
                </div>
                <br/>
                  
                <!-- FASILITAS -->
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
                            <div class="form-group">
                                <label for="fasilitas" class="col-sm-3 control-label">
                                    Fasilitas
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                	<?=$listFas?>
                                </div>
                            </div>
                        </div><br/>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="boxSquare segmenDaftar" style="padding: 12px">
                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                            <p>
                            	Beritahukan pada para pencari kerja, fasilitas apa saja yang akan mereka dapatkan jika diterima bekerja (Opsional)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- GAJI -->
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
                            <div class="form-group">
                                <label for="jnsgaji" class="col-sm-3 control-label">
                                    Periode Gaji
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <select id="jnsgaji" name="jnsgaji" class="form-control" required>
                                        <option value="1">Bulanan</option>
                                        <option value="2">Mingguan</option>
                                        <option value="3">Per Jam</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rangegaji" class="col-sm-3 control-label">
                                    Range Gaji
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="gajiawal" id="gajiawal" class="form-control" maxlength="10" autocomplete="off" style="width:40%; display:inline; text-align:right" placeholder="Gaji Minimum" /> s/d 
                                    <input type="text" name="gajiakhir" id="gajiakhir" class="form-control" maxlength="10" autocomplete="off" style="width:40%; display:inline; text-align:right" placeholder="Gaji Maksimum" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cekgaji" class="col-sm-3 control-label">
                                    Tampilkan Gaji
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                	<input type="checkbox" name="cekgaji" id="cekgaji" value="1"> Ya
                                </div>
                            </div>
                        </div><br/>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="boxSquare segmenDaftar" style="padding: 12px">
                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                            <p>
                            	Gaji sebagai informasi opsional sebagai bahan pertimbangan para pencari kerja yang bisa ditampilkan atau tidak.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- PENDIDIKAN & PENGALAMAN -->
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
                            <div class="form-group">
                                <label for="email" class="col-sm-3 control-label">
                                    Email Lowongan 
                                    <small class="text-danger">*</small>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="email" name="email" maxlength="75" placeholder="Email yang digunakan untuk menerima kiriman resume" required />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="keterangan" class="col-sm-3 control-label">
                                    Keterangan Tambahan 
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <textarea id="keterangan" name="keterangan" maxlength="400" class="form-control" placeholder="Keterangan tambahan terkait iklan lowongan anda" required></textarea>
                                </div>
                            </div>
                            
                             <div class="form-group">
                                <label for="Keywords" class="col-sm-3 control-label">
                                    Keywords 
                                    <small class="text-danger"></small>
                                </label>
                                <div class="col-sm-9">
                                    <textarea id="keywords" name="keywords" maxlength="400" class="form-control" placeholder="Keyword yang terkait iklan lowongan anda contoh (db2, c++) "></textarea>
                                </div>
                            </div>
                            
						</div><br/>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="boxSquare segmenDaftar" style="padding: 12px">
                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                            <p>
                            	Kami akan memberitahukan anda jika iklan tayangan anda mendapat respon dari pencari kerja melalui email.<br/>
                                Silakan masukkan keterangan tambahan yang tidak termasuk dalam isian sebelumnya.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <div class="boxSquare segmenDaftar">
							<div class="form-group" style="padding:28px">
                                Klik pada kotak untuk verifikasi
                                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
                                <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script><br/>
                                <input type="button" name="submit" id="submit" value="DAFTAR" onclick="submitData()" class="btn btn-custom" />
                                <input type="reset" name="reset" id="reset" value="RESET" class="btn btn-custom" />
                            </div>
                        </div>
                    </div>
                </div>
                        
            </form>
        </div><br/><br/>
        
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssvalidate');?>.jsfile"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
        <script>
			
			getDataOpt('getDataBidang','noparam','bidang','','- Pilih Bidang Pekerjaan -');
			getDataOpt('getDataPosisi','noparam','posisi','','- Pilih Posisi Pekerjaan -');
			getDataOpt('getDataPendidikan','noparam','pendidikan','','- Pilih Pendidikan Minimum -');
			getDataOpt('getDataPropinsi','noparam','propinsi','','- Pilih Penempatan Kerja -');

			$('#tgltfrom, #tgltto, #tglkerja').datetimepicker({
				timepicker:false,
				format:'Y-m-d',
				lang:'id',
				closeOnDateSelect:true,
				scrollInput : false
			});

			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
							"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
							"<strong><small id='message_response'>"+msg+"</small></strong>"+
						"</div>";
			}
			
			function submitData(){
				
				$('#message_response_container').slideDown('fast').html(generateMsg("Sedang Mengirim..."));
				var data		=	$('#postjob input, #postjob textarea, #postjob select, #postjob radio').serialize();
			
				$.ajax({
					beforeSend	: function(){
						$('#postjob input, #postjob textarea, #postjob select, #postjob radio').prop('disabled', true);
					},
					complete	: function(){
					},
					type	: "POST",
					url		: "<?=APP_URL?>posting_job?func=<?=$enkripsi->encode('submitForm')?>",
					data	: data,
					success : function(result) {
						
						data	=	JSON.parse(result);
						if(data['respon_code'] == "000000"){
							$('#postjob input, #postjob textarea').val('');
							getDataOpt('getDataBidang','noparam','bidang','','- Pilih Bidang Pekerjaan -');
							getDataOpt('getDataPosisi','noparam','posisi','','- Pilih Posisi Pekerjaan -');
							getDataOpt('getDataPendidikan','noparam','pendidikan','','- Pilih Pendidikan Minimum -');
							$('#submit').val('KIRIM');
							$('#reset').val('RESET');
						}
						
						grecaptcha.reset();
						$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
						$('#postjob input, #postjob textarea, #postjob select, #postjob radio').prop('disabled', false);
					},
					error: function(){
						$('#message_response_container').slideDown('fast').html(generateMsg('Error di server. Silakan coba lagi nanti'));
					}
				});
			}
		</script>
		<?=$session->getTemplate('footer')?>

	</body>
</html>