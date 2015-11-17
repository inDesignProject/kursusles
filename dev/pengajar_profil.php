<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	header('Content-type: text/html; charset=utf-8');

	if($session->cekSession() == 1 && !isset($_GET['q'])){
		
		header("Location: login?authResult=".$enkripsi->encode('3')."&t=2");
		die();
		
	} else if($session->cekSession() == 2 || (isset($_GET['q']) && $_GET['q'] <> '')){
		
		$idpengajarx	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDPRIME'] : $enkripsi->decode($_GET['q']) ;
//		$idpengajarx	=	$_SESSION['KursusLes']['IDPRIME'];		
//		echo $enkripsi->decode($_GET['q']).'<br>';
		//QUERY SELECT DETAIL PROFILE
		$sqlDetail	=	sprintf("SELECT A.iduser, A.IDPENGAJAR, A.NAMA, CONCAT(A.ALAMAT, ' Kec. ', D.NAMA_KECAMATAN, ' - ', E.NAMA_KOTA) AS ALAMAT_LENGKAP,
									    A.JNS_KELAMIN, A.TELPON, A.TGL_LAHIR, A.EMAIL, A.CURRENT_BALANCE, IFNULL(A.FOTO,'_default.jpg') AS FOTO, A.GPS_L, A.GPS_B,
									    IFNULL(SUM(B.RATE) / COUNT(B.IDPENGAJAR),0) AS RATING, RADIUS, A.IDPENGAJAR, A.VERIFIED_STATUS,
										A.PROFIL
								 FROM m_pengajar A
								 LEFT JOIN t_rating B ON A.IDPENGAJAR = B.IDPENGAJAR
								 LEFT JOIN m_kelurahan C ON A.IDKELURAHAN = C.IDKELURAHAN
								 LEFT JOIN m_kecamatan D ON A.IDKECAMATAN = D.IDKECAMATAN
								 LEFT JOIN m_kota E ON A.IDKOTA_TINGGAL = E.IDKOTA
								 WHERE A.IDUSER = %s"
								, $idpengajarx
								);
		$resultDetail	=	$db->query($sqlDetail);
		$resultDetail	=	$resultDetail[0];
		$profil			=	explode(".", $resultDetail['PROFIL']);
		$profil			=	$profil[0];
		$idpengajar		=	$resultDetail['IDPENGAJAR'];
		$iduser			=	$resultDetail['iduser'];

		//QUERY DAFTAR MAPEL
		$sqlMapel		=	sprintf("SELECT IFNULL(GROUP_CONCAT(NAMA_MAPEL SEPARATOR ', '), 'Tidak ada data') AS LIST_MAPEL
									 FROM (
										 SELECT B.NAMA_MAPEL
										 FROM t_mapel_pengajar A
										 LEFT JOIN m_mapel B ON A.IDMAPEL = B.IDMAPEL
										 WHERE A.IDPENGAJAR = %s
										 GROUP BY B.NAMA_MAPEL
									 ) AS A"
									, $idpengajar
									);
		$resultMapel	=	$db->query($sqlMapel);
		$resultMapel	=	$resultMapel[0]['LIST_MAPEL'];
		
		//QUERY DAFTAR LEVEL
		$sqlLevel		=	sprintf("SELECT IFNULL(GROUP_CONCAT(NAMA_JENJANG SEPARATOR ', '), 'Tidak ada data') AS LIST_LEVEL
									 FROM(
										 SELECT C.NAMA_JENJANG
										 FROM t_mapel_pengajar A
										 LEFT JOIN m_mapel B ON A.IDMAPEL = B.IDMAPEL
										 LEFT JOIN m_jenjang C ON B.IDJENJANG = C.IDJENJANG
										 WHERE A.IDPENGAJAR = %s
										 GROUP BY C.NAMA_JENJANG
									 ) AS A"
									, $idpengajar
									);
		$resultLevel	=	$db->query($sqlLevel);
		$resultLevel	=	$resultLevel[0]['LIST_LEVEL'];
		
		//DAFTAR KEAHLIAN LEVEL & PENGAJAR
		$sql		=	sprintf("SELECT A.IDMAPEL, A.NAMA_MAPEL, B.KODE_JENJANG, B.NAMA_JENJANG, C.IDPENGAJAR FROM m_mapel A
								 LEFT JOIN m_jenjang B ON A.IDJENJANG = B.IDJENJANG
								 LEFT JOIN (SELECT * FROM t_mapel_pengajar WHERE STATUS = 1 AND IDPENGAJAR = %s) C ON A.IDMAPEL= C.IDMAPEL
								 WHERE A.STATUS = 1
								 ORDER BY A.IDJENJANG, A.NAMA_MAPEL"
								, $idpengajar
								);
		$result		=	$db->query($sql);
		$jenjang	=	array();
		$i			=	0;
		$header		=	'';
		$tabcontent	=	'';

		foreach($result as $key){
			
			if(!in_array($key['KODE_JENJANG'], $jenjang)){
				
				if($i == 0){
					$header		.=	"<li role='presentation' class='active'>
										<a href='#".$key['KODE_JENJANG']."' aria-controls='".$key['KODE_JENJANG']."' role='tab' data-toggle='tab'>
											<span class='icon-".$key['KODE_JENJANG']."'>
												<img src='".APP_IMG_URL."icon/".$key['KODE_JENJANG'].".png' alt='".$key['KODE_JENJANG']."' class='img-responsive'/>
											</span>
										</a>
									 </li>";
					$tabcontent	.=	"<div role='tabpanel' class='tab-pane active' id='".$key['KODE_JENJANG']."'>
										<ul class='list-unstyled'>";
					
				} else {
					$header		.=	"<li role='presentation'>
										<a href='#".$key['KODE_JENJANG']."' aria-controls='".$key['KODE_JENJANG']."' role='tab' data-toggle='tab'>
											<span class='icon-".$key['KODE_JENJANG']."'>
												<img src='".APP_IMG_URL."icon/".$key['KODE_JENJANG'].".png' alt='".$key['KODE_JENJANG']."' class='img-responsive'/>
											</span>
										</a>
									 </li>";
					if(substr($tabcontent, strlen($tabcontent)-26,26) == "<ul class='list-unstyled'>"){
						$tabcontent	.=	"	<small>Tidak memiliki keahlian pada level ini.</small>
										 </div><div role='tabpanel' class='tab-pane' id='".$key['KODE_JENJANG']."'>
										 	<ul class='list-unstyled'>";
					} else {
						$tabcontent	.=	"</ul></div><div role='tabpanel' class='tab-pane' id='".$key['KODE_JENJANG']."'>
											<ul class='list-unstyled'>";
					}
				}
			
				array_push($jenjang, $key['KODE_JENJANG']);
				
			}
			
			if($key['IDPENGAJAR'] <> 'NULL' && $key['IDPENGAJAR'] <> '' && $key['IDPENGAJAR'] > 0){
				$tabcontent	.=	"<li><i class='fa fa-check'></i> ".$key['NAMA_MAPEL']."</li>";
			}
			$i++;
	
		}
		
		//QUERY DAFTAR PAKET
		$sqlPaket		=	sprintf("SELECT A.NAMA_PAKET, A.HARGA, D.NAMA_JENJANG, C.NAMA_MAPEL, A.JUMLAH_MURID, 
											A.TOTAL_PERTEMUAN, A.JENIS, A.MATERI, A.IDPAKET
									 FROM t_paket A
									 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
									 LEFT JOIN m_mapel C ON B.IDMAPEL = C.IDMAPEL
									 LEFT JOIN m_jenjang D ON C.IDJENJANG = D.IDJENJANG
									 WHERE B.IDPENGAJAR = %s AND A.STATUS = 1
									 LIMIT 0,4"
									, $idpengajar
									);
		$resultPaket	=	$db->query($sqlPaket);

		//GPS - RATE
		$gps_l			=	$enkripsi->encode($resultDetail['GPS_L']);
		$gps_b			=	$enkripsi->encode($resultDetail['GPS_B']);
		$rate			=	$resultDetail['RATING'];
		
		switch($rate){
			case "0"						:	$rating	=	0; break;
			case $rate > 0 && $rate <= 1	:	$rating	=	1; break;
			case $rate > 1 && $rate <= 2	:	$rating	=	2; break;
			case $rate > 2 && $rate <= 3	:	$rating	=	3; break;
			case $rate > 3 && $rate <= 4	:	$rating	=	4; break;
			case $rate > 4 && $rate <= 5	:	$rating	=	5; break;
			default							:	$rating	=	0; break;
		}

		if(isset($_SESSION['KursusLes']['IDPRIME'])){
			$login_f	=	'false';
		} else {
			$login_f	=	'true';
		}

		//FUNGSI ADD BOOKMARK
		if( $enkripsi->decode($_GET['func']) == "addBookmark" && isset($_GET['func'])){
			$idp		=	$enkripsi->decode($_POST['iddata']);

			$sqlCek		=	sprintf("SELECT IDBOOKMARK FROM t_bookmark
									 WHERE IDMURID = %s AND TYPE = 1 AND IDJOIN= %s"
									, $iduser
									, $idp
									);
			$resultCek	=	$db->query($sqlCek);
			if($resultCek <> false && $resultCek <> ''){
				echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Data bookmark sudah ada sebelumnya"));
				die();
			} else {
				$sqlInsB=	sprintf("INSERT t_bookmark
									 (IDMURID,TYPE,IDJOIN)
									 VALUES
									 (%s,1,%s)"
									, $iduser
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
    	<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'csspengajarprofile');?>.cssfile">
    	<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile">
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
    <body>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $login_f)?>

		<div class="container">
			<h3 class="text-left text_kursusles page-header">
            	DETAIL PENGAJAR
				<?php
                if(isset($_SESSION['KursusLes']['IDPRIME'])){
                ?>
            	<button class="btn btn-custom btn-xs pull-right" onclick="addBookmark('<?=$enkripsi->encode($idpengajar)?>')">
                	<i class="fa fa-bookmark"></i> Bookmark
                </button>
				<?php
				}
				?>
            </h3>
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
					<div class="boxSquare">
						<div class="row">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-left">
								<a onClick="history.back();" class="btn btn-kursusles btn-sm">< Kembali ke halaman sebelumnya</a>
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
								<a href="#" class="btn btn-kursusles btn-sm">Cara memilih pengajar</a>
							</div>
						</div><br/>
						<div class="boxSquareWhite">
							<div class="row">
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" style="text-align: center;">
									<img src="<?=APP_IMG_URL?>generate_pic.php?type=pr&q=<?=$enkripsi->encode($resultDetail['FOTO'])?>&w=180&h=180" class="img-responsive img-circle img-profile" style="margin-left:auto; margin-right:auto" />
									<?php
                                    if(isset($_SESSION['KursusLes']['IDPRIME']) && $_SESSION['KursusLes']['IDPRIME'] == $iduser){
                                    ?>
                                    <div id="change_photo" >
                                        <a href="#" class="btn btn-kursusles btn-sm" onclick="openWindowUpload()">
                                            <? echo $resultDetail['FOTO'] == "_default.jpg" ? "Upload Foto" : "Ganti Foto"?>
                                        </a>
                                    </div>
                                    <?php
                                    }
                                    ?>
								</div>
                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
									<span class="tutor_name"><?=$resultDetail['NAMA']?></span>
									<div class="row">
										<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
											<div class="rating text_gold">
                                            	<?php
												for($i=0; $i<=$rating; $i++){
												?>
													<i class="fa fa-star"></i>
                                                <?php
												}
												?>
											</div>
										</div>
										<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                        	<?php
											if($resultDetail['VERIFIED_STATUS'] == 1){
											?>
												<img src="<?=APP_IMG_URL?>icon/verified.png" class="img-responsive img-verified" alt="verified member"/>
											<?php
											} else {
												if(isset($_SESSION['KursusLes']['IDPRIME']) && $_SESSION['KursusLes']['IDPRIME'] == $iduser){
											?>
                                            		<a href="#" class="btn btn-kursusles btn-sm" onClick="window.location.href = '<?=APP_URL?>veri_akun?q=<?=$enkripsi->encode($idpengajar)?>'">Verifikasi Akun Saya</a>
                                            <?php
												}
											}
											?>
                                        </div>
									</div><hr>
									<p class="about_text">
										<?=$profil?>
									</p>
                                    <div class="info">
                                        <div class="infolist">
                                            <ul class="list-inline">
                                                <li class="address">
                                                	<small><?=$resultDetail['ALAMAT_LENGKAP']?></small>
                                                </li>
                                            </ul>
                                            <ul class="list-inline">
                                                <li class="materi">
                                                	<small><?=$resultMapel?></small>
                                                </li>
                                            </ul>
                                            <ul class="list-inline">
                                                <li class="level">
                                                	<small><?=$resultLevel?></small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
									<?php
                                    if(isset($_SESSION['KursusLes']['IDPRIME']) && $_SESSION['KursusLes']['IDPRIME'] == $iduser){
                                    ?>
										<button class="btn btn-custom btn-xs pull-right" onclick="window.location.href='editdata?q=<?=$enkripsi->encode($iduser)?>&r=pengajar_profil'"><i class="fa"></i>Edit Data Saya</button>
                                    <?php
                                    } else {
                                    ?>
										<button class="btn btn-custom btn-xs pull-right" onclick="window.location.href='msg_compose?t=<?=$enkripsi->encode($idpengajar)?>&r=pengajar_profil'"><i class="fa fa-envelope"></i> Kirim Pesan Pribadi</button>
									<?php
									}
									?>
                                </div>
                        	</div>
                        </div><br/>
						<?php
                        //if($_SESSION['KursusLes']['TYPEUSER'] != 2 && isset($iduser)){
                        if(isset($_SESSION['KursusLes']['IDPRIME']) && ($_SESSION['KursusLes']['IDPRIME'] != $iduser)){

                        ?>
						<div class="report text-right">
                        	<a href="send_report?q=<?=$enkripsi->encode($idpengajar)?>" class="btn btn-danger btn-xs">
                                <small>
                                    <i class="fa fa-exclamation-circle"></i>
                                    LAPORKAN PENGAJAR
                                </small>
                            </a>
                        </div>
						<?php
                       }
                        ?>
						<div role="tabpanel">
							<ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                	<a href="#" id="apesan" onclick="getContent('pesan')" aria-controls="pesan-pengunjung" role="tab" data-toggle="tab">Pesan Pengunjung</a>
                                </li>
                                <li role="presentation">
                                	<a href="#" id="aprofil" onclick="getContent('profil')" aria-controls="profil" role="tab" data-toggle="tab">Profil</a>
                                </li>
                                <li role="presentation">
                                	<a href="#" id="amap" onclick="getContent('map')" aria-controls="map" role="tab" data-toggle="tab">Jangkauan</a>
                                </li>
                                <li role="presentation">
                                	<a href="#" id="atestimoni" onclick="getContent('testimoni')" aria-controls="testimoni" role="tab" data-toggle="tab">Testimonial / Review</a>
                                </li>
                                <li role="presentation">
                                	<a href="#" id="aKjadwal" onclick="getContent('Kjadwal')" aria-controls="kjadwal" role="tab" data-toggle="tab">Jadwal</a>
                                </li>
                            </ul>
                        </div>
                        <div id="tab_content" class="padding1 tab-content"></div>
					</div>
				</div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
					<div class="boxSquare">
						<div class="segmenPaket">
							<div class="panel panel-default">
								<div class="panel-heading">Daftar Paket Belajar</div>
								<div class="panel-body">
									<form role="form" method="POST" action="#" id="paket-container">
										<?php
											if($resultPaket <> "" && $resultPaket <> false){
												$i	=	1;
												foreach($resultPaket as $key){
													$checked	=	$i == 1 ? "checked" : "";
										?>
                                        <div class="form-group">
											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<?php
                                                    //if($_SESSION['KursusLes']['TYPEUSER'] <> 2){
                                                    ?>
													<input type="radio" name="paket_belajar" <?=$checked?> id="paket<?=$enkripsi->encode($key['IDPAKET'])?>" required value="<?=$enkripsi->encode($key['IDPAKET'])?>" />
													<?php
													//}
                                                    ?>
                                                    <?=$key['NAMA_PAKET']?><br/>
													<small>Biaya: Rp <?=number_format($key['HARGA'],0,",",".")?></small><br/>
													<small>Level: <?=$key['NAMA_JENJANG']?></small><br/>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<small>
														<ul>
															<li><?=$key['TOTAL_PERTEMUAN']?>x pertemuan</li>
															<li><?php 
																if($key['JENIS'] == "1"){
																	echo "1 Anak (Privat)";
																} else { 
																	echo "Maks ".$key['JUMLAH_MURID']." anak" ;
																}
																?>
                                                            </li>
															<li>Materi: <?=substr($key['MATERI'],0,25).".."?></li>
														</ul>
													</small>
												</div>
											</div>
										</div>
										<?php
													$i++;
												}
										?>
                                            <?php
											//if($_SESSION['KursusLes']['TYPEUSER'] <> 2){
											?>
                                            <div class="tab-pane">
                                                <div class="tab-paket" style="background: #f00 !important; padding: 2% !important">
                                                    <a href="#" onClick="goToPaket()">Pesan Paket</a>
                                                </div>
                                            </div>
											<?php
                                            //}
                                            ?>
                                        <div class="tab-pane">
                                            <div class="tab-paket" style="background: #f00 !important; padding: 2% !important">
                                                <?php
												if(isset($_SESSION['KursusLes']['IDPRIME']) && $_SESSION['KursusLes']['IDPRIME'] == $iduser){
												?>
													<a href="<?=APP_URL."php/page/level.php"?>">Lihat Paket Selengkapnya</a>
                                                <?php
												} else {
												?>
													<a href="<?=APP_URL."detail_paket?q=".$enkripsi->encode($idpengajar)?>">Lihat Paket Selengkapnya</a>
												<?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
											} else {
												if(isset($_SESSION['KursusLes']['IDPRIME']) && $_SESSION['KursusLes']['IDPRIME'] == $iduser){
													echo "<center><b><small>Anda belum menyediakan paket mengajar</small></b></center><br/>";
													echo "  <div class='tab-pane'>
																<div class='tab-paket' style='background: #f00 !important; padding: 2% !important'>
																	<a href='".APP_URL."php/page/level.php'>Buat Paket</a>
																</div>
															</div>";
												} else {
													echo "<center><b>Pengajar tidak memiliki paket yang disediakan</b></center>";
												}
											}
										?>
									</form>
								</div>
							</div>
						</div>
						<div class="segmenKeahlian">
							<div class="panel panel-default">
								<div class="panel-heading"><?=(isset($_GET['q']) ? 'Keahlian Pengajar' : 'Keahlian Saya')?></div>
								<div class="panel-body">
									<div role="tabpanel">
										<ul class="nav nav-tabs" role="tablist">
                                            <?=$header?>
                                        </ul>
										<div class="tab-content">
											<?=$tabcontent?>
                                            <?php
												if(substr($tabcontent, strlen($tabcontent)-26,26) == "<ul class='list-unstyled'>"){
													echo "<small>Tidak ada keahlian pada level ini.</small></div>";
												} else if(substr($tabcontent, strlen($tabcontent)-8,8) == "</small>"){
													echo "</div>";
												} else {
													echo "</ul></div>";
												}
											?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
        	</div>
        </div>
    
        <div id="dialog-confirm">
          <p id="text_dialog"></p>
        </div>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141218'.'jsspengajarprofil');?>.jsfile&PARAM=<?=$enkripsi->encode($iduser)?>"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        
    	<?=$session->getTemplate('footer')?>

	</body>
</html>
<?php
	} else {
		header("Location: ".APP_URL."login?authResult=".$enkripsi->encode('2')."&t=2");
		die();
	}
?>