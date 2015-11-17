<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	
	$txtSearch	=	"";
	
	//CEK DATA KIRIMAN GET KODE JENJANG
	if(isset($_GET['kdj']) || $_GET['kdj'] != ""){
		
		$idjenjang	=	$enkripsi->decode($_GET['kdj']);
		$sqljenjang	=	sprintf("SELECT NAMA_JENJANG FROM m_jenjang WHERE IDJENJANG = %s", $idjenjang);
		$resultJnj	=	$db->query($sqljenjang);
	
		//JIKA DATA DITEMUKAN
		if(isset($resultJnj) || $resultJnj != false){
			$txtSearch	.=	"Jenjang : ".$resultJnj[0]['NAMA_JENJANG']."; ";
		}
		
	}

	//CEK DATA KIRIMAN GET MAPEL
	if(isset($_GET['mp']) || $_GET['mp'] != ""){
		
		$idmapel	=	$enkripsi->decode($_GET['mp']);
		$sqlmapel	=	sprintf("SELECT NAMA_MAPEL FROM m_mapel WHERE IDMAPEL = %s", $idmapel);
		$resultMpl	=	$db->query($sqlmapel);
	
		//JIKA DATA DITEMUKAN
		if(isset($resultJnj) || $resultJnj != false){
			$txtSearch	.=	"Pelajaran : ".$resultMpl[0]['NAMA_MAPEL']."; ";
		}
		
	}
	//HABIS - CEK DATA KIRIMAN GET
	
	//QUERY DATA PER LEVEL DAN MAPEL
	if($idjenjang <> "" && $idmapel <> ""){
		
		$sqlperlvl	=	sprintf("SELECT B.*
								 FROM t_mapel_pengajar A
								 LEFT JOIN (
									SELECT A.NAMA, CONCAT(A.ALAMAT, ' Kec. ', D.NAMA_KECAMATAN, ' - ', E.NAMA_KOTA) AS ALAMAT_LENGKAP,
										A.JNS_KELAMIN, A.TELPON, A.TGL_LAHIR, A.EMAIL, A.CURRENT_BALANCE, IFNULL(A.FOTO,'_default.jpg') AS FOTO, A.GPS_L, A.GPS_B,
										IFNULL(SUM(B.RATE) / COUNT(B.IDPENGAJAR),0) AS RATING, RADIUS, A.VERIFIED_STATUS, A.IDPENGAJAR
									FROM m_pengajar A
									LEFT JOIN t_rating B ON A.IDPENGAJAR = B.IDPENGAJAR
									LEFT JOIN m_kelurahan C ON A.IDKELURAHAN = C.IDKELURAHAN
									LEFT JOIN m_kecamatan D ON A.IDKECAMATAN = D.IDKECAMATAN
									LEFT JOIN m_kota E ON A.IDKOTA_TINGGAL = E.IDKOTA
									GROUP BY A.IDPENGAJAR
									) AS B ON A.IDPENGAJAR = B.IDPENGAJAR
								 WHERE A.IDMAPEL = %s
								 GROUP BY A.IDPENGAJAR"
								, $idmapel
								);
		$resultperlvl=	$db->query($sqlperlvl);
		$dataresult	 =	"";
		
		if($resultperlvl <> false && $resultperlvl <> ''){
			
			foreach($resultperlvl as $key){
				
				$rating		=	'';
				for($j=1; $j<=$key['RATING']; $j++){
					$rating	.= "<i class='fa fa-star'></i>";
				}
				
				if($key['VERIFIED_STATUS'] == 1){
					$virefied	=	"<img src='".APP_IMG_URL."icon/verified.png' class='img-responsive img-verified' alt='verified member' style='margin: 0 !important'>";
				} else {
					$verified	=	"";
				}
				
				//QUERY DAFTAR MAPEL
				$sqlMapel		=	sprintf("SELECT IFNULL(GROUP_CONCAT(NAMA_MAPEL SEPARATOR ', '), 'Tidak ada data') AS LIST_MAPEL
											 FROM (
												 SELECT B.NAMA_MAPEL
												 FROM t_mapel_pengajar A
												 LEFT JOIN m_mapel B ON A.IDMAPEL = B.IDMAPEL
												 WHERE A.IDPENGAJAR = %s
												 GROUP BY B.NAMA_MAPEL
											 ) AS A"
											, $key['IDPENGAJAR']
											);
				$resultMapel	=	$db->query($sqlMapel);
				$resultMapel	=	str_replace($resultMpl[0]['NAMA_MAPEL'],"<span style='background: none repeat scroll 0% 0% rgb(224, 255, 19);'>".$resultMpl[0]['NAMA_MAPEL']."</span>",$resultMapel[0]['LIST_MAPEL']);
				
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
											, $key['IDPENGAJAR']
											);
				$resultLevel	=	$db->query($sqlLevel);
				$resultLevel	=	str_replace($resultJnj[0]['NAMA_JENJANG'],"<span style='background: none repeat scroll 0% 0% rgb(224, 255, 19);'>".$resultJnj[0]['NAMA_JENJANG']."</span>",$resultLevel[0]['LIST_LEVEL']);
				
				//QUERY DAFTAR PAKET
				$sqlPaket		=	sprintf("SELECT A.NAMA_PAKET, A.IDPAKET
											 FROM t_paket A
											 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
											 WHERE B.IDPENGAJAR = %s AND A.STATUS = 1
											 LIMIT 0,4"
											, $key['IDPENGAJAR']
											);
				$resultPaket	=	$db->query($sqlPaket);
				
				$dataPaket		=	"";
				if($resultPaket <> false && $resultPaket <> ''){
					foreach($resultPaket as $keyy){
						$dataPaket	.=	"<div class='tab-paket'>
											<a href='detail_paket?q=".$enkripsi->encode($key['IDPENGAJAR'])."&idp=".$enkripsi->encode($keyy['IDPAKET'])."'>".$keyy['NAMA_PAKET']."</a>
										 </div>";
					}
					$dataPaket		.=	"<div class='tab-paket' style='background: #f00 !important'>
											<a href='detail_paket?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>Lihat Detail Selengkapnya</a>
										 </div>";
				} else {
					$dataPaket	=	"<center><b><small>Pengajar tidak memliki data paket</small></b></center>";
				}
				
				$dataresult	.=	"<div class='boxSquareWhite' style='padding: 1%'>
                                  <div class='row'>
                                  	<div class='col-lg-8 col-md-8 col-sm-8 col-xs-12' >
                                        <div class='col-lg-3 col-md-3 col-sm-3 col-xs-12' style='text-align: center;'>
											<a href='".APP_URL."pengajar_profil.php?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>
												<img src='".APP_IMG_URL."generate_pic.php?type=pr&q=".$enkripsi->encode($key['FOTO'])."&w=90&h=90' class='img-responsive img-circle img-profile' style='margin-left:auto; margin-right:auto'>
											</a>
										</div>
                                        <div class='col-lg-9 col-md-9 col-sm-9 col-xs-12'>
                                          <h4><a href='".APP_URL."pengajar_profil.php?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>".$key['NAMA']."</a></h4>
                                          <div class='row'>
                                            <div class='col-lg-3 col-md-3 col-sm-3 col-xs-12'>
                                              <div class='rating text_gold'>
											  	".$rating."
											  </div>
                                            </div>
                                            <div class='col-lg-9 col-md-9 col-sm-9 col-xs-12'>
                                            	".$verified."
											</div>
                                          </div>
                                          <hr>
                                          <div class='info'>
                                            <div class='infolist'>
                                              <ul class='list-inline'>
                                                <li class='address'> <small>".$key['ALAMAT_LENGKAP']."</small> </li>
                                              </ul>
                                              <ul class='list-inline'>
                                                <li class='materi'> <small>".$resultMapel."</small> </li>
                                              </ul>
                                              <ul class='list-inline'>
                                                <li class='level'> <small>".$resultLevel."</small> </li>
                                              </ul>
                                            </div>
                                          </div>
                                    	</div>
                                    </div>
                                  	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12' >
                                    	<div role='tabpanel' class='tab-pane'>
                                            <div class='panel panel-default'>
                                                <div class='panel-heading'><b>Daftar Paket</b></div>
                                                <div class='panel-body'>
                                                   ".$dataPaket."
                                                </div>
                                        	</div>
                                        </div>
                                    </div>
                                  </div>
                                </div><hr></hr>";
			}
		} else {
			$dataresult	=	"<center><b>Tidak ada data yang ditemukan</b></center>";
		}
		
	}
	
	//QUERY CUSTOM
	if($_POST['key'] <> '' && isset($_POST['key'])){
		
		$txtSearch	=	$_POST['key'];
		$condition	=	"'%".$_POST['key']."%'";
		$sqlcustom	=	sprintf("SELECT * FROM (
									SELECT A.NAMA, CONCAT(A.ALAMAT, ' Kec. ', D.NAMA_KECAMATAN, ' - ', E.NAMA_KOTA) AS ALAMAT_LENGKAP,
										   A.JNS_KELAMIN, A.TELPON, A.TGL_LAHIR, A.EMAIL, A.CURRENT_BALANCE, IFNULL(A.FOTO,'_default.jpg') AS FOTO, A.GPS_L, A.GPS_B,
										   IFNULL(SUM(B.RATE) / COUNT(B.IDPENGAJAR),0) AS RATING, RADIUS, A.VERIFIED_STATUS, A.IDPENGAJAR,    
										   G.NAMA_MAPEL, E.NAMA_KOTA, I.NAMA_JENJANG, H.NAMA_PAKET, H.MATERI, A.ALAMAT
									FROM m_pengajar A
									LEFT JOIN t_rating B ON A.IDPENGAJAR = B.IDPENGAJAR
									LEFT JOIN m_kelurahan C ON A.IDKELURAHAN = C.IDKELURAHAN
									LEFT JOIN m_kecamatan D ON A.IDKECAMATAN = D.IDKECAMATAN
									LEFT JOIN m_kota E ON A.IDKOTA_TINGGAL = E.IDKOTA
									LEFT JOIN t_mapel_pengajar F ON A.IDPENGAJAR = F.IDPENGAJAR
									LEFT JOIN m_mapel G ON F.IDMAPEL = G.IDMAPEL
									LEFT JOIN t_paket H ON F.IDMAPELPENGAJAR = H.IDMAPELPENGAJAR    
									LEFT JOIN m_jenjang I ON G.IDJENJANG = I.IDJENJANG
									GROUP BY A.IDPENGAJAR
								 ) AS A
								 WHERE NAMA LIKE %s OR ALAMAT LIKE %s OR NAMA_KOTA LIKE %s OR 
								 	   NAMA_MAPEL LIKE %s OR NAMA_JENJANG LIKE %s OR
								 	   NAMA_PAKET LIKE %s OR MATERI LIKE %s"
								, $condition
								, $condition
								, $condition
								, $condition
								, $condition
								, $condition
								, $condition
								);
		$resultcustom=	$db->query($sqlcustom);
		$dataresult	 =	"";
		
		if($resultcustom <> false && $resultcustom <> ''){
			
			foreach($resultcustom as $key){
				
				$rating		=	'';
				for($j=1; $j<=$key['RATING']; $j++){
					$rating	.= "<i class='fa fa-star'></i>";
				}
				
				if($key['VERIFIED_STATUS'] == 1){
					$virefied	=	"<img src='".APP_IMG_URL."icon/verified.png' class='img-responsive img-verified' alt='verified member' style='margin: 0 !important'>";
				} else {
					$verified	=	"";
				}
				
				//QUERY DAFTAR MAPEL
				$sqlMapel		=	sprintf("SELECT IFNULL(GROUP_CONCAT(NAMA_MAPEL SEPARATOR ', '), 'Tidak ada data') AS LIST_MAPEL
											 FROM (
												 SELECT B.NAMA_MAPEL
												 FROM t_mapel_pengajar A
												 LEFT JOIN m_mapel B ON A.IDMAPEL = B.IDMAPEL
												 WHERE A.IDPENGAJAR = %s
												 GROUP BY B.NAMA_MAPEL
											 ) AS A"
											, $key['IDPENGAJAR']
											);
				$resultMapel	=	$db->query($sqlMapel);
				$resultMapel	=	str_replace($resultMpl[0]['NAMA_MAPEL'],"<span style='background: none repeat scroll 0% 0% rgb(224, 255, 19);'>".$resultMpl[0]['NAMA_MAPEL']."</span>",$resultMapel[0]['LIST_MAPEL']);
				
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
											, $key['IDPENGAJAR']
											);
				$resultLevel	=	$db->query($sqlLevel);
				$resultLevel	=	str_replace($resultJnj[0]['NAMA_JENJANG'],"<span style='background: none repeat scroll 0% 0% rgb(224, 255, 19);'>".$resultJnj[0]['NAMA_JENJANG']."</span>",$resultLevel[0]['LIST_LEVEL']);
				
				//QUERY DAFTAR PAKET
				$sqlPaket		=	sprintf("SELECT A.NAMA_PAKET, A.IDPAKET
											 FROM t_paket A
											 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
											 WHERE B.IDPENGAJAR = %s AND A.STATUS = 1
											 LIMIT 0,4"
											, $key['IDPENGAJAR']
											);
				$resultPaket	=	$db->query($sqlPaket);
				
				$dataPaket		=	"";
				if($resultPaket <> false && $resultPaket <> ''){
					foreach($resultPaket as $keyy){
						$dataPaket	.=	"<div class='tab-paket'>
											<a href='detail_paket?q=".$enkripsi->encode($key['IDPENGAJAR'])."&idp=".$enkripsi->encode($keyy['IDPAKET'])."'>".$keyy['NAMA_PAKET']."</a>
										 </div>";
					}
					$dataPaket		.=	"<div class='tab-paket' style='background: #f00 !important'>
											<a href='detail_paket?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>Lihat Detail Selengkapnya</a>
										 </div>";
				} else {
					$dataPaket	=	"<center><b><small>Pengajar tidak memliki data paket</small></b></center>";
				}
				
				$dataresult	.=	"<div class='boxSquareWhite' style='padding: 1%'>
                                  <div class='row'>
                                  	<div class='col-lg-8 col-md-8 col-sm-8 col-xs-12' >
                                        <div class='col-lg-3 col-md-3 col-sm-3 col-xs-12' style='text-align: center;'>
											<a href='".APP_URL."pengajar_profil.php?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>
												<img src='".APP_IMG_URL."generate_pic.php?type=pr&q=".$enkripsi->encode($key['FOTO'])."&w=90&h=90' class='img-responsive img-circle img-profile' style='margin-left:auto; margin-right:auto'>
											</a>
										</div>
                                        <div class='col-lg-9 col-md-9 col-sm-9 col-xs-12'>
                                          <h4><a href='".APP_URL."pengajar_profil.php?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>".$key['NAMA']."</a></h4>
                                          <div class='row'>
                                            <div class='col-lg-3 col-md-3 col-sm-3 col-xs-12'>
                                              <div class='rating text_gold'>
											  	".$rating."
											  </div>
                                            </div>
                                            <div class='col-lg-9 col-md-9 col-sm-9 col-xs-12'>
                                            	".$verified."
											</div>
                                          </div>
                                          <hr>
                                          <div class='info'>
                                            <div class='infolist'>
                                              <ul class='list-inline'>
                                                <li class='address'> <small>".$key['ALAMAT_LENGKAP']."</small> </li>
                                              </ul>
                                              <ul class='list-inline'>
                                                <li class='materi'> <small>".$resultMapel."</small> </li>
                                              </ul>
                                              <ul class='list-inline'>
                                                <li class='level'> <small>".$resultLevel."</small> </li>
                                              </ul>
                                            </div>
                                          </div>
                                    	</div>
                                    </div>
                                  	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12' >
                                    	<div role='tabpanel' class='tab-pane'>
                                            <div class='panel panel-default'>
                                                <div class='panel-heading'><b>Daftar Paket</b></div>
                                                <div class='panel-body'>
                                                   ".$dataPaket."
                                                </div>
                                        	</div>
                                        </div>
                                    </div>
                                  </div>
                                </div><hr></hr>";
			}
		} else {
			$dataresult	=	"<center><b>Tidak ada data yang ditemukan</b></center>";
		}
		
	}
	
	//CEK DATA KIRIMAN GET ALL
	if(isset($_GET['w']) && $_GET['w'] <> "" && $enkripsi->decode($_GET['w']) == "all"){
	
		$sqlgroup		=	sprintf("SELECT COUNT(A.IDPENGAJAR) AS JML_PENGAJAR, LEFT(A.NAMA,1) AS ALIAS
									 FROM m_pengajar A
									 LEFT JOIN m_user B ON A.IDUSER = B.IDUSER
									
									 GROUP BY LEFT(A.NAMA,1)
									 ORDER BY A.NAMA");
		$resultgroup	=	$db->query($sqlgroup);
		$i				=	0;
		$header			=	'';
		$tabcontent		=	'';
		$class			=	'';
		
		foreach($resultgroup as $key){
			
			if($i==0){
				$class	=	"active";
				$tabcontent	.=	"<div role='tabpanel' class='tab-pane active' id='".$key['ALIAS']."'>";
			} else {
				$class	=	"";
				$tabcontent	.=	"<div role='tabpanel' class='tab-pane' id='".$key['ALIAS']."'>";
			}
			$header		.=	"<li role='presentation' class='".$class."'>
									<a href='#".$key['ALIAS']."' aria-controls='".$key['ALIAS']."' role='tab' data-toggle='tab' style='padding: 4px;'>
										<b>
											<center>
												".$key['ALIAS']."<br/>
												<span class='color: #09BE1F'>(".$key['JML_PENGAJAR'].")</span></br>
											</center>
										</b>
									</a>
								 </li>";
			
			$sqlDetail		=	sprintf("SELECT A.NAMA, CONCAT(A.ALAMAT, ' Kec. ', D.NAMA_KECAMATAN, ' - ', E.NAMA_KOTA) AS ALAMAT_LENGKAP,
												A.JNS_KELAMIN, A.TELPON, A.TGL_LAHIR, A.EMAIL, A.CURRENT_BALANCE, IFNULL(A.FOTO,'_default.jpg') AS FOTO, A.GPS_L, A.GPS_B,
												IFNULL(SUM(B.RATE) / COUNT(B.IDPENGAJAR),0) AS RATING, RADIUS, A.IDPENGAJAR, A.VERIFIED_STATUS
										 FROM m_pengajar A
										 LEFT JOIN t_rating B ON A.IDPENGAJAR = B.IDPENGAJAR
										 LEFT JOIN m_kelurahan C ON A.IDKELURAHAN = C.IDKELURAHAN
										 LEFT JOIN m_kecamatan D ON A.IDKECAMATAN = D.IDKECAMATAN
										 LEFT JOIN m_kota E ON A.IDKOTA_TINGGAL = E.IDKOTA
										 WHERE LEFT(A.NAMA,1) = '%s'
										 GROUP BY IDPENGAJAR
										 LIMIT 0,10"
										, $key['ALIAS']
										);
			$resultDetail	=	$db->query($sqlDetail);
			
			foreach($resultDetail as $keyx){
				
				$rating		=	'';
				for($j=1; $j<=$keyx['RATING']; $j++){
					$rating	.= "<i class='fa fa-star'></i>";
				}
				
				if($keyx['VERIFIED_STATUS'] == 1){
					$virefied	=	"<img src='".APP_IMG_URL."icon/verified.png' class='img-responsive img-verified' alt='verified member' style='margin: 0 !important'>";
				} else {
					$verified	=	"";
				}
				
				//QUERY DAFTAR MAPEL
				$sqlMapel		=	sprintf("SELECT IFNULL(GROUP_CONCAT(NAMA_MAPEL SEPARATOR ', '), 'Tidak ada data') AS LIST_MAPEL
											 FROM (
												 SELECT B.NAMA_MAPEL
												 FROM t_mapel_pengajar A
												 LEFT JOIN m_mapel B ON A.IDMAPEL = B.IDMAPEL
												 WHERE A.IDPENGAJAR = %s
												 GROUP BY B.NAMA_MAPEL
											 ) AS A"
											, $keyx['IDPENGAJAR']
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
											, $keyx['IDPENGAJAR']
											);
				$resultLevel	=	$db->query($sqlLevel);
				$resultLevel	=	$resultLevel[0]['LIST_LEVEL'];
				
				//QUERY DAFTAR PAKET
				$sqlPaket		=	sprintf("SELECT A.NAMA_PAKET, A.IDPAKET
											 FROM t_paket A
											 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
											 WHERE B.IDPENGAJAR = %s AND A.STATUS = 1
											 LIMIT 0,4"
											, $keyx['IDPENGAJAR']
											);
				$resultPaket	=	$db->query($sqlPaket);
				
				$dataPaket		=	"";
				if($resultPaket <> false && $resultPaket <> ''){
					foreach($resultPaket as $keyy){
						$dataPaket	.=	"<div class='tab-paket'>
											<a href='detail_paket?q=".$enkripsi->encode($keyx['IDPENGAJAR'])."&idp=".$enkripsi->encode($keyy['IDPAKET'])."'>".$keyy['NAMA_PAKET']."</a>
										 </div>";
					}
					$dataPaket		.=	"<div class='tab-paket' style='background: #f00 !important'>
											<a href='detail_paket?q=".$enkripsi->encode($keyx['IDPENGAJAR'])."'>Lihat Detail Selengkapnya</a>
										 </div>";
				} else {
					$dataPaket	=	"<center><b><small>Pengajar tidak memliki data paket</small></b></center>";
				}
				
				$tabcontent	.=	"<div class='boxSquareWhite' style='padding: 1%'>
                                  <div class='row'>
                                  	<div class='col-lg-8 col-md-8 col-sm-8 col-xs-12' >
                                        <div class='col-lg-3 col-md-3 col-sm-3 col-xs-12' style='text-align: center;'>
											<a href='".APP_URL."pengajar_profil.php?q=".$enkripsi->encode($keyx['IDPENGAJAR'])."'>
												<img src='".APP_IMG_URL."generate_pic.php?type=pr&q=".$enkripsi->encode($keyx['FOTO'])."&w=90&h=90' class='img-responsive img-circle img-profile' style='margin-left:auto; margin-right:auto'>
											</a>
										</div>
                                        <div class='col-lg-9 col-md-9 col-sm-9 col-xs-12'>
                                          <h4><a href='".APP_URL."pengajar_profil.php?q=".$enkripsi->encode($keyx['IDPENGAJAR'])."'>".$keyx['NAMA']."</a></h4>
                                          <div class='row'>
                                            <div class='col-lg-3 col-md-3 col-sm-3 col-xs-12'>
                                              <div class='rating text_gold'>
											  	".$rating."
											  </div>
                                            </div>
                                            <div class='col-lg-9 col-md-9 col-sm-9 col-xs-12'>
                                            	".$verified."
											</div>
                                          </div>
                                          <hr>
                                          <div class='info'>
                                            <div class='infolist'>
                                              <ul class='list-inline'>
                                                <li class='address'> <small>".$keyx['ALAMAT_LENGKAP']."</small> </li>
                                              </ul>
                                              <ul class='list-inline'>
                                                <li class='materi'> <small>".$resultMapel."</small> </li>
                                              </ul>
                                              <ul class='list-inline'>
                                                <li class='level'> <small>".$resultLevel."</small> </li>
                                              </ul>
                                            </div>
                                          </div>
                                    	</div>
                                    </div>
                                  	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12' >
                                    	<div role='tabpanel' class='tab-pane'>
                                            <div class='panel panel-default'>
                                                <div class='panel-heading'><b>Daftar Paket</b></div>
                                                <div class='panel-body'>
                                                   ".$dataPaket."
                                                </div>
                                        	</div>
                                        </div>
                                    </div>
                                  </div>
                                </div><hr></hr>";
			}
			
			$tabcontent	.=	"</div>";
			$i++;
		}
	
	}
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
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
    <style>
		.address{background:url("<?=APP_IMG_URL?>icon/street.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
		.materi{background:url("<?=APP_IMG_URL?>icon/materi.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
		.level{background:url("<?=APP_IMG_URL?>icon/level.png") no-repeat left top; line-height: 26px; padding-left: 30px !important;}
		.btn-custom{border-radius: 3px !important; margin-bottom:4px;}
		.list-inline > li {width: 100%;}
		hr{margin: 10px 0 !important;}
    </style>
        
    	<?=$session->getTemplate('header', $show_login)?>
        
		<div class="container">
        	<h3 class="text-left text_kursusles page-header">PENCARIAN PENGAJAR</h3>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Pencarian</b></div>
                            <div class="panel-body">
                                <form id="searchForm" method="POST" action="search_result">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <div class="form-group">
                                                <input type="text" name="key" class="form-control" placeholder="Masukkan kata kunci" value="<?=$txtSearch?>" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        	<input type="submit" name="search" id="search" value="Cari" class="btn btn-sm btn-custom2" />
                                        </div>
                                	</div>
                            	</form>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Hasil Pencarian</b></div>
                            <div class="panel-body">
							<?php
							if(!isset($_GET['w']) || $_GET['w'] == ""){
								echo $dataresult;
							} else {
						  ?>
                            <div role="tabpanel">
                                <ul class="nav nav-tabs" role="tablist">
                                    <?=$header?>
                                </ul>
                                <div class="tab-content">
                                    <?=$tabcontent?>
                                </div>
                            </div>
						  <?php
							}
						  ?>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
        </div><br/>
        
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
    	<?=$session->getTemplate('footer')?>

    </body>
</html>