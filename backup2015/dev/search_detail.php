<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
	//JIKA ADA KIRIMAN DATA PENCARIAN
	if($enkripsi->decode($_GET['func']) == "searchData" && isset($_GET['func'])){
		
		$dataPerPage	=	10;
		$pageCount		=	1;
		
		//CEK KONDISIONAL DAERAH
		if(isset($_POST['daerah']) && $_POST['daerah'] <> ""){
			$con_daerah		=	"(
									A.ALAMAT LIKE '%".$db->db_text($_POST['daerah'])."%' OR
									C.NAMA_KELURAHAN LIKE '%".$db->db_text($_POST['daerah'])."%' OR
									D.NAMA_KECAMATAN LIKE '%".$db->db_text($_POST['daerah'])."%' OR
									E.NAMA_KOTA LIKE '%".$db->db_text($_POST['daerah'])."%'
								)";
		} else {
			$con_daerah		=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL DAERAH

		//CEK NAMA KOTA UNTUK TENTUKAN IDKOTA
		if(isset($_POST['kota']) && $_POST['kota'] <> ""){
			$sqlKota	=	sprintf("SELECT IDKOTA FROM m_kota WHERE NAMA_KOTA = '%s' LIMIT 0,1", $_POST['kota']);
			$resultKota	=	$db->query($sqlKota);
			if($resultKota <> "" && $resultKota <> false){
				$idKota		=	$resultKota[0]['IDKOTA'];
				$con_kota	=	"A.IDKOTA_TINGGAL = '".$idKota."'";
			} else{
				$con_kota	=	"1=1";
			}
		} else {
			$con_kota		=	"1=1";
		}
		//HABIS -- CEK NAMA KOTA UNTUK TENTUKAN IDKOTA
		
		//CEK KONDISIONAL JENJANG
		if(isset($_POST['jenjang']) && $_POST['jenjang'] <> ""){
			$con_jenjang	=	"G.IDJENJANG = ".$enkripsi->decode($_POST['jenjang']);
		} else {
			$con_jenjang	=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL JENJANG

		//CEK KONDISIONAL MAPEL
		if(isset($_POST['mapel']) && $_POST['mapel'] <> ""){
			$con_mapel		=	"F.IDMAPEL = ".$enkripsi->decode($_POST['mapel']);
		} else {
			$con_mapel		=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL MAPEL

		//CEK KONDISIONAL MATERI
		if(isset($_POST['materi']) && $_POST['materi'] <> ""){
			$con_materi		=	"H.MATERI LIKE '%".$db->db_text($_POST['materi'])."%' AND H.STATUS = 1";
		} else {
			$con_materi		=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL MATERI

		//CEK KONDISIONAL RATING
		if(isset($_POST['rating']) && $_POST['rating'] <> ""){
			$con_rating		=	"RATING >= ".$_POST['rating'];
		} else {
			$con_rating		=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL RATING

		//CEK KONDISIONAL JENIS KELAMIN
		if(isset($_POST['jk_p']) && $_POST['jk_p'] <> "" && (!isset($_POST['jk_w']) || $_POST['jk_w'] == "")){
			$con_jk		=	"A.JNS_KELAMIN = 1";
		} else if(isset($_POST['jk_w']) && $_POST['jk_w'] <> "" && (!isset($_POST['jk_p']) || $_POST['jk_p'] == "")){
			$con_jk		=	"A.JNS_KELAMIN = 2";
		} else {
			$con_jk		=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL JENIS KELAMIN
		
		//CEK KONDISIONAL VERIFIED
		if(isset($_POST['ver_t']) && $_POST['ver_t'] <> "" && (!isset($_POST['ver_f']) || $_POST['ver_f'] == "")){
			$con_vs		=	"A.VERIFIED_STATUS = 1";
		} else if(isset($_POST['ver_f']) && $_POST['ver_f'] <> "" && (!isset($_POST['ver_t']) || $_POST['ver_t'] == "")){
			$con_vs		=	"A.VERIFIED_STATUS = 0";
		} else {
			$con_vs		=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL VERIFIED
		
		//QUERY PENCARIAN
		$sqlSearch		=	sprintf("SELECT * FROM (
										 SELECT A.NAMA, CONCAT(A.ALAMAT, ' Kec. ', D.NAMA_KECAMATAN, ' - ', E.NAMA_KOTA) AS ALAMAT_LENGKAP,
												A.JNS_KELAMIN, A.TELPON, A.TGL_LAHIR, A.EMAIL, A.CURRENT_BALANCE, 
												IFNULL(A.FOTO,'_default.jpg') AS FOTO, A.GPS_L, A.GPS_B,
												IFNULL(SUM(B.RATE) / COUNT(B.IDPENGAJAR),0) AS RATING, A.RADIUS, A.VERIFIED_STATUS, A.IDPENGAJAR,
												A.IDKOTA_TINGGAL
										 FROM m_pengajar A
										 LEFT JOIN t_rating B ON A.IDPENGAJAR = B.IDPENGAJAR
										 LEFT JOIN m_kelurahan C ON A.IDKELURAHAN = C.IDKELURAHAN
										 LEFT JOIN m_kecamatan D ON A.IDKECAMATAN = D.IDKECAMATAN
										 LEFT JOIN m_kota E ON A.IDKOTA_TINGGAL = E.IDKOTA
										 LEFT JOIN t_mapel_pengajar F ON A.IDPENGAJAR = F.IDPENGAJAR
										 LEFT JOIN m_mapel G ON F.IDMAPEL = G.IDMAPEL
										 LEFT JOIN t_paket H ON F.IDMAPELPENGAJAR = H.IDMAPELPENGAJAR
										 WHERE %s AND %s AND %s AND %s AND %s AND %s AND %s
										 GROUP BY A.IDPENGAJAR
									 ) AS A
									 WHERE %s
									 ORDER BY NAMA"
									, $con_daerah
									, $con_jk
									, $con_kota
									, $con_vs
									, $con_materi
									, $con_mapel
									, $con_jenjang
									, $con_rating
									);
		$sqlCSearch		=	sprintf("SELECT COUNT(IDPENGAJAR) AS JUMLAH_DATA FROM (%s) AS X", $sqlSearch);
		$resultCSearch	=	$db->query($sqlCSearch);
		
		if($resultCSearch <> "" && $resultCSearch <> false && $resultCSearch[0]['JUMLAH_DATA'] <> 0){ 
			$totData	=	$resultCSearch[0]['JUMLAH_DATA'];
			$pageCount	=	ceil($totData / $dataPerPage);
			$startData	=	$_POST['page'] * $dataPerPage - $dataPerPage + 1;
			$endData	=	$_POST['page'] == $pageCount ? $totData : $_POST['page'] * $dataPerPage;
		} else {
			$totData	=	0;
			$pageCount	=	0;
			$startData	=	0;
			$endData	=	0;
		}
		
		$sqlSearch		=	sprintf("SELECT * FROM (%s) AS A LIMIT %s, %s", $sqlSearch, ($startData -1), $dataPerPage);
		$resultSearch	=	$db->query($sqlSearch);
		
		if($resultSearch <> "" && $resultSearch <> false){
			
			foreach($resultSearch as $key){
				
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
				//$resultMapel	=	str_replace($resultMpl[0]['NAMA_MAPEL'],"<span style='background: none repeat scroll 0% 0% rgb(224, 255, 19);'>".$resultMpl[0]['NAMA_MAPEL']."</span>",$resultMapel[0]['LIST_MAPEL']);
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
											, $key['IDPENGAJAR']
											);
				$resultLevel	=	$db->query($sqlLevel);
				//$resultLevel	=	str_replace($resultJnj[0]['NAMA_JENJANG'],"<span style='background: none repeat scroll 0% 0% rgb(224, 255, 19);'>".$resultJnj[0]['NAMA_JENJANG']."</span>",$resultLevel[0]['LIST_LEVEL']);
				$resultLevel	=	$resultLevel[0]['LIST_LEVEL'];
				
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
			$dataresult	=	"<center><b> Tidak ada data yang ditemukan </b></center>";
		}
		
		$pageData		=	"<div id='ketData'>
								<div style='float:left'>
									<small><b>Menampilkan data ke ".$startData." sampai ".$endData."</b></small>
								</div>
								<div style='float:right'>
									<small><b>".$totData." Data ditemukan.</b></small>
								</div>
							 </div>";
		
		//PAGING SEARCH RESULT
		for($x=1; $x<=$pageCount; $x++){
			$pageButtonList	.=	$x.",";
		}
		
		if($_POST['page'] == 1 && $pageCount > 1){
			if($pageCount == 2){
				$pageButtonList	=	$pageButtonList."next,";
			} else {
				$pageButtonList	=	$pageButtonList."next,last,";
			}
		} else if($_POST['page'] > 1 && $pageCount > 2 && $_POST['page'] <> $pageCount){
			$pageButtonList	=	"first,prev,".$pageButtonList."next,last,";
		} else if($totData <> 0 && $pageCount > 1) {
			if($pageCount == 2){
				$pageButtonList	=	"prev,".$pageButtonList;
			} else {
				$pageButtonList	=	"first,prev,".$pageButtonList;
			}
		} else if($pageCount == 1){
			$pageButtonList	=	"first,prev,".$pageButtonList;
		} else {
			$pageButtonList	=	"";
		}
		
		if($pageButtonList <> ""){
			$expl_pageButtonList	=	explode(",", substr($pageButtonList,0,strlen($pageButtonList)-1));
			foreach($expl_pageButtonList as $keyz){
				if($keyz == "first"){
					$numSearchData	=	1;
					$txtSearchData	=	" <i class='fa fa-angle-double-left'></i> ";
				} else if($keyz == "prev") {
					$numSearchData	=	$_POST['page'] - 1;
					$txtSearchData	=	" <i class='fa fa-angle-left'></i> ";
				} else if($keyz == "next") {
					$numSearchData	=	$_POST['page'] + 1;
					$txtSearchData	=	" <i class='fa fa-angle-right'></i> ";
				} else if($keyz == "last") {
					$numSearchData	=	$pageCount;
					$txtSearchData	=	" <i class='fa fa-angle-double-right'></i> ";
				} else {
					$numSearchData	=	$keyz;
					$txtSearchData	=	$keyz;
				}

				$active		=	$keyz == $_POST['page'] ? "class='active'" : "";				
				$pageButton	.=	"</li><li role='presentation' ".$active.">
									<a href='#' role='tab' data-toggle='tab' style='padding: 4px 10px;' onClick='searchData(".$numSearchData.")'>
										<b><center>".$txtSearchData."</center></b>
									</a>
								</li>";
				
			}
		} else {
			$pageButton	=	"";
		}
		//HABIS -- PAGGING SEARCH RESULT
		
		echo json_encode(array("result"=>$dataresult,
							   "pageData"=>$pageData,
							   "pageCount"=>$pageCount,
							   "pageButton"=>$pageButton
							  )
						);
		die();
	}
	
	//RANDOM DAFTAR PENGAJAR DAN JUMLAH PENGAJAR
	$sqlPengajar	=	sprintf("SELECT IDPENGAJAR, FOTO FROM m_pengajar A
								 LEFT JOIN m_user B ON A.IDPENGAJAR = B.IDUSER_CHILD
								 WHERE B.IDLEVEL = 1 AND B.STATUS = 1
								 ORDER BY RAND()"
								);
	$sqlDftrPengajar=	"SELECT * FROM (".$sqlPengajar.") AS A WHERE FOTO <> '".$enkripsi->encode('default').".jpg' AND FOTO IS NOT NULL LIMIT 0,4";
	$sqlJumPengajar	=	"SELECT COUNT(IDPENGAJAR) AS JUMLAH_PENGAJAR FROM (".$sqlPengajar.") AS A";
	
	$resultPengajar	=	$db->query($sqlDftrPengajar);
	$JumPengajar	=	$db->query($sqlJumPengajar);
	$JumPengajar	=	$JumPengajar[0]['JUMLAH_PENGAJAR'];
	//HABIS - DAFTAR PENGAJAR
	
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
		hr{margin: 10px 0 !important;}
		.list-inline li{line-height: 20px;}
    </style>
    	<?=$session->getTemplate('header', $show_login)?>
        <div class="container">
        	<h3 class="text-left text_kursusles page-header">PENCARIAN PENGAJAR DETAIL</h3>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Filter Pencarian</b></div>
                            <div class="panel-body">
                                <form id="searchForm" method="POST" action="search_detail">
                                    <div class="form-group">
                                        <input type="text" name="daerah" class="form-control" placeholder="Nama Daerah" />
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="kota" class="form-control" placeholder="Kota / Kabupaten" id="kota" onkeyup="getDataKotaByInput(this.value, this.id)" autocomplete="off"/>
                                    </div>
                                    <div class="form-group">
                                        <select name="jenjang" class="form-control" id="jenjang" onchange="emptyOpt('mapel', '- Pilih Mata Pelajaran -');getDataOpt('getDataMapel','jenjang','mapel',this.value,'- Pilih Mata Pelajaran -');">
                                            <option value="">-Pilih Jenjang-</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <select name="mapel" class="form-control" id="mapel">
                                            <option value="">-Pilih Mata Pelajaran-</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="materi" class="form-control" placeholder="Materi yang diberikan" />
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            	<small><b>Minimal Rating</b></small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div id="rating">
                                                    <input type="radio" name="rating" class="star-1" id="star-1" value="1" />
                                                    <label class="star-1" for="star-1">1</label>
                                                    <input type="radio" name="rating" class="star-2" id="star-2" value="2" />
                                                    <label class="star-2" for="star-2">2</label>
                                                    <input type="radio" name="rating" class="star-3" id="star-3" value="3" />
                                                    <label class="star-3" for="star-3">3</label>
                                                    <input type="radio" name="rating" class="star-4" id="star-4" value="4" />
                                                    <label class="star-4" for="star-4">4</label>
                                                    <input type="radio" name="rating" class="star-5" id="star-5" value="5" />
                                                    <label class="star-5" for="star-5">5</label>
                                                    <span></span>
                                                </div>
                                        	</div>
                                        </div>
                                    </div>
                                    <hr></hr>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            	<small><b>Jenis Kelamin</b></small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                <input name="jk_p" value="1" id="jk_p" type="checkbox"/> <small>Pria</small>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                <input name="jk_w" value="2" id="jk_w" type="checkbox"/> <small>Wanita</small>
                                            </div>
                                        </div>
                                    </div>
                                    <hr></hr>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            	<small><b>Status Pengajar</b></small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            	<input name="ver_t" value="1" id="ver_t" type="checkbox"/> <small>Terverifikasi</small>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            	<input name="ver_f" value="2" id="ver_f" type="checkbox"/> <small>Reguler</small>
                                            </div>
                                        </div>
                                    </div>
                                    <hr></hr>
                                    <div class="form-group">
                                        <input type="hidden" name="page" id="page" value="1" />
                                        <input type="button" name="search" id="search" value="Cari Pengajar" class="btn btn-sm btn-custom2" onClick="searchData('default')" />
									</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Hasil Pencarian</b></div>
                            <div class="panel-body">
                            	<div class="row" style="margin: 5px 0;" id="pageData">
									<div id="ketData">
										<div style="float:left">
											<small><b>-</b></small>
										</div>
										<div style="float:right">
											<small><b>0 Data ditemukan.</b></small>
										</div>
									</div>
								</div>
								<nav>
									<ul class="nav nav-tabs pagination" role="tablist" style="margin: 0 auto" id="pageButton">
									</ul>
								</nav><br/>
                            	<div id="searchResult">
                            		<center><b> Silakan isi filter pencarian</b></center>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141214'.'jssajax');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssindex');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141214'.'jsssearchdetail');?>.jsfile"></script>
        <script>
			$(document).ready(function() {
				getDataOpt('getDataJenjang','noparam','jenjang','','- Pilih Jenjang -');
			
			});
		</script>
    	<?=$session->getTemplate('footer')?>

    </body>
</html>