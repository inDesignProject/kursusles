<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	
	//CARI DAFTAR JENJANG DAN MATA PELAJARAN
	$sql		=	sprintf("SELECT B.KODE_JENJANG, A.NAMA_MAPEL, B.NAMA_JENJANG, A.IDMAPEL, B.IDJENJANG
							 FROM m_mapel A
							 LEFT JOIN m_jenjang B ON A.IDJENJANG = B.IDJENJANG
							 WHERE A.STATUS = 1
							 ORDER BY A.IDJENJANG, A.NAMA_MAPEL"
							);
	$result		=	$db->query($sql);
	
	//JIKA DATA DITEMUKAN
	if(isset($result) || $result != false){
		
		$arrJenjang	=	array();
		$tab		=	'';
		$content_tab=	'';
		$i			=	0;
		
		//LOOP
		foreach($result as $key){

			$idjenjang			=	$enkripsi->encode($key['IDJENJANG']);
			$idmapel			=	$enkripsi->encode($key['IDMAPEL']);
			
			if(!in_array($key['KODE_JENJANG'],$arrJenjang)){
				
				$active			=	$i == 0 ? "active" : "";
				$tab			.=	"<li role='presentation' class='".$active."'>
										<a href='#".$key['KODE_JENJANG']."' aria-controls='".$key['KODE_JENJANG']."' role='tab' data-toggle='tab'>
											<span class='icon-".$key['KODE_JENJANG']."'>
												<img src='".APP_IMG_URL."icon/".$key['KODE_JENJANG'].".png' alt='preschool' class='img-responsive'/>
											</span>
											".$key['NAMA_JENJANG']."
										</a>
									 </li>";
				
				if($i == 0){
					$content_tab.=	"<div role='tabpanel' class='tab-pane ".$active."' id='".$key['KODE_JENJANG']."'>
											<div class='row'>";
				} else {
					$content_tab.=	"	</div>
									 </div>
									 <div role='tabpanel' class='tab-pane ".$active."' id='".$key['KODE_JENJANG']."'>
										<div class='row'>";
				}
				
				array_push($arrJenjang, $key['KODE_JENJANG']);
			}
			
			$content_tab		.=	"<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
										 <div class='tab-mapel'>
											 <a href='".APP_URL."search_result.php?kdj=".$idjenjang."&mp=".$idmapel."'>".$key['NAMA_MAPEL']."</a>
										 </div>
									 </div>";
			
			$i++;
		}
		
	}
	//HABIS - DATA JENJANG DAN MATA PELAJARAN
	
	//RANDOM DAFTAR PENGAJAR DAN JUMLAH PENGAJAR
	$sqlPengajar	=	sprintf("SELECT IDPENGAJAR, FOTO FROM m_pengajar A
								 LEFT JOIN m_user B ON A.IDUSER = B.IDUSER
								 WHERE B.STATUS = 1
								 ORDER BY RAND()"
								);
	$sqlDftrPengajar=	"SELECT * FROM (".$sqlPengajar.") AS A WHERE FOTO <> '".$enkripsi->encode('default').".jpg' AND FOTO IS NOT NULL LIMIT 0,4";
	$sqlJumPengajar	=	"SELECT COUNT(IDPENGAJAR) AS JUMLAH_PENGAJAR FROM (".$sqlPengajar.") AS A";
	
	$resultPengajar	=	$db->query($sqlDftrPengajar);
	$JumPengajar	=	$db->query($sqlJumPengajar);
	$JumPengajar	=	$JumPengajar[0]['JUMLAH_PENGAJAR'];
	//HABIS - DAFTAR PENGAJAR
	
	//JUMLAH PENGGUNA ONLINE
	$dateSearch		=	date('Y-m-d H:i:s', strtotime( '-60 minute' , strtotime(date('Y-m-d H:i:s'))));
	
	$sqlOnline		=	sprintf("SELECT COUNT(IDUSER) AS JUMLAH_ONLINE
								 FROM m_user
								 WHERE LAST_LOGIN >= '%s'"
								, $dateSearch
								);
	$JumOnline		=	$db->query($sqlOnline);
	$JumOnline		=	$JumOnline[0]['JUMLAH_ONLINE'];
	//HABIS - JUMLAH PENGGUNA ONLINE
	
	//JUMLAH MURID
	$sqlMurid		=	sprintf("SELECT COUNT(IDMURID) AS JUMLAH_MURID
								 FROM m_murid A
								 LEFT JOIN m_user B ON A.IDUSER = B.IDUSER
								 WHERE B.STATUS = 1"
								);
	$JumMurid		=	$db->query($sqlMurid);
	$JumMurid		=	$JumMurid[0]['JUMLAH_MURID'];
	//HABIS - JUMLAH MURID
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
	//CEK KIRIMAN AUTHRESULT DARI URL SAAT LOGIN
	switch($enkripsi->decode($_GET['authResult'])){
		//JIKA USERNAME DAN PASSWORDNYA TIDAK DITEMUKAN
		case	"1"	:	$pesan			=	"Username dan atau password salah";
						$username		=	$enkripsi->decode($_GET['u']);
						$password		=	$enkripsi->decode($_GET['p']);
						break;
		//JIKA AKAN MASUK KE INDEX PENGAJAR / MURID, TAPI BELUM LOGIN
		case	"2"	:	$pesan			=	"Anda belum masuk / login, silakan masukkan username dan password";
						$username		=	"";
						$password		=	"";
						break;
		//JIKA TIDAK MELAKUKAN AKTIVITAS SELAMA >= 1 JAM
		case	"3"	:	$pesan			=	"Anda tidak melakukan aktivitas selama lebih dari 1 jam. Silakan login ulang";
						$username		=	"";
						$password		=	"";
						break;
		//JIKA TIDAK MEMILIH LOGIN TYPE
		case	"4"	:	$pesan			=	"Anda belum memilih login sebagai <strong>Murid</strong> atau <strong>Pengajar</strong>";
						$username		=	$enkripsi->decode($_GET['u']);
						$password		=	$enkripsi->decode($_GET['p']);
						break;
		default		:	$pesan			=	"";
						$username		=	"";
						$password		=	"";
						break;	
	}
	$param_t1	=	$_GET['t'] <> "1" ? "" : "checked";
	$param_t2	=	$_GET['t'] == "2" ? "checked" : "";
	//HABIS - CEK AUTHRESULT LOGIN

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
    	<?php if($pesan <> ""){ ?>
            <div class="alert alert-success alert-dismissible" role="alert" style="text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><small id="login_msg"><?=$pesan?></small></strong>
            </div>
        <? } ?>
        
    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>
        
		<div class="container">
			<center>
				<div class="blackboard">
					<p class="textBlackboard">Cari guru dan tempat kursus <br/>dengan mudah di KursusLes.com</p><br/>
					<form method="POST" action="search_result" class="form-search">
						<div class="form-group">
							<input type="text" class="form-control input-lg" name="key" placeholder="Guru apa yang kamu cari ? atau Tempat les apa yang kamu cari ?" required />
						</div>
						<div class="tombolSearch text-right">
							<a href="search_detail" class="btn btn-link">< Pencarian detail</a>
							<button type="submit" class="btn btn-search">Cari</button>
						</div>
					</form>
				</div>
			</center>
		</div>
		<div class="coklatslogan">
			<div class="container">
				<h3 class=" text-left">CARI GURU ATAU TEMPAT KURSUS SESUAI PENDIDIKAN</h3>
			</div>
		</div><br/><br/><br/>
		<div class="container">
			<div role="tabpanel">
				<ul class="nav nav-tabs" role="tablist">
					<?=$tab?>
                </ul>
				<div class="tab-content">
							<?=$content_tab?>
						</div>
                	</div>
                </div>
			</div>
		</div>
		<div class="coklatslogan">
			<div class="container">
				<h3 class=" text-left">CARI GURU ATAU PENGAJAR DI KURSUSLES.COM</h3>
			</div>
		</div>
		<div class="container">
			<div class="guru">
				<div class="row">
                	<?php
                    //JIKA DAFTAR PENGAJAR DITEMUKAN
                   
                    if(isset($resultPengajar) && $resultPengajar != false){
                        
						foreach($resultPengajar as $key){
							echo "	<div class='col-lg-3 col-md-3 col-sm-3 col-xs-12'>
										<a href='".APP_URL."pengajar_profil.php?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>
											<img src='images/generate_pic.php?type=pr&w=300&h=300&q=".$enkripsi->encode($key['FOTO'])."' class='img-responsive img-circle' target='_blank' />
										</a>
									</div>";
						}
						
					}
					
					?>
				</div><br/><br/>
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
						<a href="search_result.php?w=<?=$enkripsi->encode('all')?>">LIHAT SEMUA</a>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
						<div class="lineGuru">&nbsp;</div>
					</div>
				</div>
			</div>
		</div>
		<div class="infoslogan text-center">
			<div class="container">
				<div class="row">
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3><?=$JumPengajar?><br/>PENGAJAR</h3></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3><?=$JumOnline?><br/>PENGGUNA ONLINE</h3></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3><?=$JumMurid?><br/>MURID</h4></div>
				</div>
			</div>
		</div><br/><br/><br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
    	<?=$session->getTemplate('footer')?>

    </body>
</html>