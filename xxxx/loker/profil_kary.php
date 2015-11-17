<?php
	
	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	//CEK SESSION UNTUK FORM LOGIN
	$show_login	=	$session->cekSession() == 2 ? "false" : "true";
	
	if($_GET['q'] <> "" && isset($_GET['q'])){
		$idkary		=	$enkripsi->decode($_GET['q']);
	} else {
		if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 2){
			echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
			die();
		} else {
			$idkary	=	$_SESSION['KursusLesLoker']['IDUSER'];
		}
	}
	
	//DATA UTAMA
	$sql		=	sprintf("SELECT A.NAMA, A.ALAMAT, A.TELPON, A.EMAIL, A.FOTO, A.TGL_LAHIR, A.JK, B.NAMA_POSISI, C.NAMA_BIDANG,
									D.NAMA_PENDIDIKAN, A.TGL_AWALKERJA, A.TGL_AKHIRKERJA, A.TENTANG, A.IDBIDANG, 
									A.IDPOSISI, A.IDPENDIDIKAN, A.JURUSAN
							 FROM m_karyawan A
							 LEFT JOIN m_posisi B ON A.IDPOSISI = B.IDPOSISI
							 LEFT JOIN m_bidang C ON A.IDBIDANG = C.IDBIDANG
							 LEFT JOIN m_pendidikan D ON A.IDPENDIDIKAN = D.IDPENDIDIKAN
							 WHERE A.IDKARYAWAN = %s
							 LIMIT 0,1"
							, $idkary
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	
	switch($result['JK']){
		case "L"	:	$jeniskelamin	=	"Laki-Laki"; break;
		case "P"	:	$jeniskelamin	=	"Perempuan"; break;
		default		:	$jeniskelamin	=	"Tidak Diketahui"; break;
	}
	$tentang	=	$result['TENTANG'] == "" ? "Tidak ada yang ditampilkan" : $result['TENTANG'];

	//DATA RESUME
	$sqlresume	=	sprintf("SELECT A.STATUS_NIKAH, B.NAMA_RAS, C.NAMA_AGAMA,
									A.KENDARAAN, A.KARTU_KREDIT, A.STATUS_TINGGAL, A.JML_ANAK, 
									A.KODEPOS, A.TINGKAT_KERJA, A.GAJI_TERAKHIR, A.GAJI_DIHARAPKAN, A.BISA_NEGO
							 FROM t_resume A
							 LEFT JOIN m_ras B ON A.IDRAS = B.IDRAS
							 LEFT JOIN m_agama C ON A.IDAGAMA = C.IDAGAMA
							 WHERE A.IDKARYAWAN = %s
							 LIMIT 0,1"
							, $idkary
							);
	$resultres	=	$db->query($sqlresume);
	$resultres	=	$resultres[0];
	
	switch($resultres['STATUS_NIKAH']){
		case "0"	:	$statusnikah	=	"Lajang"; break;
		case "1"	:	$statusnikah	=	"Menikah"; break;
		case "2"	:	$statusnikah	=	"Berpisah"; break;
		case "3"	:	$statusnikah	=	"Cerai"; break;
		case "4"	:	$statusnikah	=	"Janda / Duda"; break;
		case "5"	:	$statusnikah	=	"Tidak dijelaskan"; break;
		default		:	$statusnikah	=	"Tidak Diketahui"; break;
	}

	switch($resultres['KENDARAAN']){
		case "0"	:	$kendaraan		=	"Tidak ada"; break;
		case "1"	:	$kendaraan		=	"Sepeda Motor"; break;
		case "2"	:	$kendaraan		=	"Mobil"; break;
		default		:	$kendaraan		=	"Tidak Diketahui"; break;
	}

	switch($resultres['KARTU_KREDIT']){
		case "0"	:	$kartukredit	=	"Tidak ada"; break;
		case "1"	:	$kartukredit	=	"Classic"; break;
		case "2"	:	$kartukredit	=	"Gold"; break;
		case "3"	:	$kartukredit	=	"Platinum"; break;
		default		:	$kartukredit	=	"Tidak Diketahui"; break;
	}

	switch($resultres['STATUS_TINGGAL']){
		case "1"	:	$statustinggal	=	"Sewa"; break;
		case "2"	:	$statustinggal	=	"Mortgaged"; break;
		case "3"	:	$statustinggal	=	"Properti tanpa hipotik"; break;
		case "4"	:	$statustinggal	=	"Quarters"; break;
		case "5"	:	$statustinggal	=	"Dengan Orang Tua"; break;
		case "6"	:	$statustinggal	=	"Lainnya"; break;
		default		:	$statustinggal	=	"Tidak Diketahui"; break;
	}

	switch($resultres['TINGKAT_KERJA']){
		case "0"	:	$tingkatkarir	=	"Awal"; break;
		case "1"	:	$tingkatkarir	=	"Pertengahan"; break;
		case "2"	:	$tingkatkarir	=	"Senior"; break;
		case "3"	:	$tingkatkarir	=	"Paling Atas"; break;
		default		:	$tingkatkarir	=	"Tidak Diketahui"; break;
	}

	//GET LIST PENDIDIKAN
	$sqlpen		=	sprintf("SELECT B.NAMA_JENJANG, A.BIDANG_STUDI, A.NAMA_INSTITUSI,
									A.TAHUN_AWAL, A.TAHUN_AKHIR, A.TAMPIL
							 FROM t_riwayat_pendidikan A
							 LEFT JOIN m_jenjang B ON A.IDJENJANG = B.IDJENJANG
							 WHERE A.IDKARYAWAN = %s AND TAMPIL = 1
							 ORDER BY A.IDJENJANG DESC"
							, $idkary
							);
	$resultpen		=	$db->query($sqlpen);
	
	if($resultpen <> false && $resultpen <> ""){
		
		foreach($resultpen as $key){
			$datapen.=	"<tr class='rowdata'>
							<td>".$key['NAMA_JENJANG']."</td>
							<td>".$key['BIDANG_STUDI']."</td>
							<td>".$key['NAMA_INSTITUSI']."</td>
							<td align='center'>".$key['TAHUN_AWAL']."</td>
							<td align='center'>".$key['TAHUN_AKHIR']."</td>
						 </tr>";
		}
		
	} else {
		$datapen=	"<tr id='rownodata'><td colspan ='5'><center>Tidak ada data</center></td></tr>";
	}

	//GET LIST SERTIFIKAT
	$sqlser		=	sprintf("SELECT NAMA_SERTIFIKAT, DIKELUARKAN_OLEH, TAHUN_PEROLEHAN
							 FROM t_sertifikasi
							 WHERE IDKARYAWAN = %s"
							, $idkary
							);
	$resultser		=	$db->query($sqlser);
	
	if($resultser <> false && $resultser <> ""){
		
		foreach($resultser as $key){
			$dataser.=	"<tr class='rowdata'>
							<td>".$key['NAMA_SERTIFIKAT']."</td>
							<td>".$key['DIKELUARKAN_OLEH']."</td>
							<td>".$key['TAHUN_PEROLEHAN']."</td>
						 </tr>";
		}
		
	} else {
		$dataser=	"<tr id='rownodata'><td colspan ='3'><center>Tidak ada data</center></td></tr>";
	}

	//GET LIST PENGALAMAN
	$sqlpeng	=	sprintf("SELECT B.NAMA_BIDANG, C.NAMA_POSISI, A.MULAI_KERJA, A.SELESAI_KERJA, A.NAMA_PERUSAHAAN, 
									A.GAJI_BULANAN, A.DESKRIPSI_KERJA, A.IDPENGALAMAN
							 FROM t_pengalaman_kerja A
							 LEFT JOIN m_bidang B ON A.IDBIDANG = B.IDBIDANG
							 LEFT JOIN m_posisi C ON A.IDPOSISI = C.IDPOSISI
							 WHERE A.IDKARYAWAN = %s"
							, $idkary
							);
	$resultpeng	=	$db->query($sqlpeng);
	
	if($resultpeng <> false && $resultpeng <> ""){
		
		foreach($resultpeng as $key){
			
			$datapeng.=	"<tr class='rowdata'>
							<td>".$key['NAMA_PERUSAHAAN']."</td>
							<td>".$key['NAMA_BIDANG']."</td>
							<td>".$key['NAMA_POSISI']."</td>
							<td align='center'>".$key['MULAI_KERJA']."</td>
							<td align='center'>".$key['SELESAI_KERJA']."</td>
							<td align='right'>Rp. ".number_format($key['GAJI_BULANAN'], 0, ',', '.').",-</td>
						 </tr>";
		}
		
	} else {
		$datapeng	=	"<tr id='rownodata'><td colspan ='6'><center>Tidak ada data</center></td></tr>";
	}

	header("Content-type: text/html; charset=utf-8");

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
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssbootstrap');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssfontawesome');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssdatepicker');?>.cssfile" />
        <!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

    	<?=$session->getTemplate('header', $show_login)?>
		
		<div class="container" style="font-family: Verdana,Arial,Helvetica,sans-serif;">
			<h3 class="text-left text_kursusles page-header">PROFIL</h3>
           
            <div class="boxSquareWhite">
            	<div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" style="text-align: center;">
                        <img src="<?=APP_IMG_URL?>generate_pic.php?type=kr&q=<?=$enkripsi->encode($result['FOTO'])?>&w=180&h=180" class="img-responsive img-circle img-profile" style="margin-left:auto; margin-right:auto">
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                        <span class="tutor_name"><?=$result['NAMA']?></span>
                        <hr>
                        <div class="info">
                            <div class="infolist" id="toplist">
                                <i class="fa fa-home"></i> &nbsp; <b id="text-alamat"><?=$result['ALAMAT']?></b><br/>
                                <i class="fa fa-envelope-o"></i> &nbsp; <b><?=$result['EMAIL']?></b><br/>
                                <i class="fa fa-phone"></i> &nbsp; <b id="text-telpon"><?=$result['TELPON']?></b><br/>
                                <i class="fa fa-user"></i> &nbsp; <b id="text-jk"><?=$jeniskelamin?></b><br/>
                                <i class="fa fa-calendar"></i> &nbsp; <b id="text-tgllahir"><?=$result['TGL_LAHIR'] == "" ? "Tidak Diketahui" : $result['TGL_LAHIR']?></b><br/>
                            </div>
                        </div>
                    </div>
                </div>
            </div><hr>

            <div class="boxSquareWhite">
                <h4><i class="fa fa-info-circle"></i> Tentang</h4>
                <div id="ptentang">
                    <p id="txttentang">
                        <?=$tentang?><br/><br/>
                    </p>
                </div>
            </div><hr/>
            
            <div class="boxSquareWhite" id="statusinfo">
                <h4><i class="fa fa-user"></i> Data Pribadi</h4>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Status Pernikahan</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$statusnikah?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Ras</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$resultres['NAMA_RAS'] == "" ? "Tidak Ada Data" : $resultres['NAMA_RAS']?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Agama</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$resultres['NAMA_AGAMA'] == "" ? "Tidak Ada Data" : $resultres['NAMA_AGAMA']?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Kendaraan</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$kendaraan?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Kartu Kredit</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$kartukredit?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Tempat Tinggal</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$statustinggal?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Jumlah Anak</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$resultres['JML_ANAK'] == "0" ? "Tidak Ada Data" : $resultres['JML_ANAK']." Anak"?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Kode Pos</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$resultres['KODEPOS'] == "0" ? "Tidak Ada Data" : $resultres['KODEPOS']?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Tingkatan Karir</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$tingkatkarir?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Gaji Terakhir</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$resultres['GAJI_TERAKHIR'] == "" ? "Tidak Ada Data" : "Rp. ".number_format($resultres['GAJI_TERAKHIR'], 0, ',', '.').",-"?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Gaji Diharapkan</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$resultres['GAJI_DIHARAPKAN'] == "" ? "Tidak Ada Data" : "Rp. ".number_format($resultres['GAJI_DIHARAPKAN'], 0, ',', '.').",-"?>
                    	<?=$resultres['BISA_NEGO'] == "1" ? "<b>Nego</b>" : ""?>
                    </div>
                </div>
            </div><hr />

            <div class="boxSquareWhite">
                <h4><i class="fa fa-question-circle"></i> Status Terakhir</h4>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Bidang Terakhir</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$result['NAMA_BIDANG'] == "" ? "Tidak Ada Data" : $result['NAMA_BIDANG']?>
                    </div>
                </div>    
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Posisi Terakhir</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$result['NAMA_POSISI'] == "" ? "Tidak Ada Data" : $result['NAMA_POSISI']?>
                    </div>
                </div>    
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Tanggal Kerja</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?=$result['TGL_AWALKERJA'] == "" ? "Tidak Ada Data" : $result['TGL_AWALKERJA']." s/d ".$result['TGL_AKHIRKERJA']?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Pendidikan</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
						<?=$result['NAMA_PENDIDIKAN'] == "" ? "Tidak Ada Data" : $result['NAMA_PENDIDIKAN']?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Jurusan</div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
						<?=$result['JURUSAN'] == "" ? "Tidak Ada Data" : $result['JURUSAN']?>
                    </div>
                </div>
            </div><hr />
            
            <div class="boxSquareWhite">
                <h4><i class="fa fa-graduation-cap"></i> Riwayat Pendidikan</h4>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                     <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Jenjang</th>
                                <th class="text-center">Bidang Studi</th>
                                <th class="text-center">Nama Institusi</th>
                                <th class="text-center">Tahun<br/>Awal</th>
                                <th class="text-center">Tahun<br/>Berakhir</th>
                            </tr>
                        </thead>
                        <tbody id="tableData">
                            <?=$datapen?>
                        </tbody>
                      </table>
                    </div>
                </div>
            </div><hr/>

            <div class="boxSquareWhite">
                <h4><i class="fa fa-certificate"></i> Sertifikat</h4>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                     <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Nama Sertifikat</th>
                                <th class="text-center">Dikeluarkan Oleh</th>
                                <th class="text-center">Tahun Sertifikat</th>
                            </tr>
                        </thead>
                        <tbody id="tableData">
                            <?=$dataser?>
                        </tbody>
                      </table>
                    </div>
                </div>
            </div><hr/>

            <div class="boxSquareWhite">
                <h4><i class="fa fa-file-text"></i> Pengalaman Kerja</h4>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                     <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Nama Perusahaan</th>
                                <th class="text-center">Bidang</th>
                                <th class="text-center">Posisi</th>
                                <th class="text-center">Mulai Kerja</th>
                                <th class="text-center">Berakhir</th>
                                <th class="text-center">Gaji Bulanan</th>
                            </tr>
                        </thead>
                        <tbody id="tableData">
                            <?=$datapeng?>
                        </tbody>
                      </table>
                    </div>
                </div>
            </div>

        </div><br/><br/>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<?=$session->getTemplate('footer')?>

        <div id="dialog-confirm">
          <p id="text_dialog"></p>
        </div>		

    </body>
</html>