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
	$show_login	=	$session->cekSession() == 2 ? "false" : "true";
	
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

	//JUMLAH LOWONGAN PER BIDANG
	$sqlbidang	=	sprintf("SELECT COUNT(A.IDLOWONGAN) AS JML_LOWONGAN, B.NAMA_BIDANG, B.IDBIDANG
							 FROM t_lowongan A
							 LEFT JOIN m_bidang B ON A.IDBIDANG = B.IDBIDANG
							 WHERE A.STATUS = 1
							 GROUP BY A.IDBIDANG");
	$resbidang	=	$db->query($sqlbidang);
	
	if($resbidang <> '' && $resbidang <> false){
		$listBidang		=	'';
		foreach($resbidang as $key){
			$idbidang	=	$enkripsi->encode($key['IDBIDANG']);
			$listBidang	.=	"	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
									<div class='tab-mapel'>
										<a href='".APP_URL."search_category?t=1&q=".$idbidang."'>".$key['NAMA_BIDANG']." <small><em>(".$key['JML_LOWONGAN'].")</em></small></a>
									</div>
								</div>";
		}
	} else {
		$listBidang	=	"<center><b>Tidak ada data</b></center>";
	}

	//JUMLAH LOWONGAN PER POSISI
	$sqlposisi	=	sprintf("SELECT COUNT(A.IDLOWONGAN) AS JML_LOWONGAN, B.NAMA_POSISI, B.IDPOSISI
							 FROM t_lowongan A
							 LEFT JOIN m_posisi B ON A.IDPOSISI = B.IDPOSISI
							 WHERE A.STATUS = 1
							 GROUP BY A.IDPOSISI");
	$resposisi	=	$db->query($sqlposisi);
	
	if($resposisi <> '' && $resposisi <> false){
		$listposisi		=	'';
		foreach($resposisi as $key){
			$idposisi	=	$enkripsi->encode($key['IDPOSISI']);
			$listposisi	.=	"	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
									<div class='tab-mapel'>
										<a href='".APP_URL."search_category?t=2&q=".$idposisi."'>".$key['NAMA_POSISI']." <small><em>(".$key['JML_LOWONGAN'].")</em></small></a>
									</div>
								</div>";
		}
	} else {
		$listposisi	=	"<center><b>Tidak ada data</b></center>";
	}
	
	//JUMLAH LOWONGAN PER LOKASI
	$sqlpropinsi=	sprintf("SELECT COUNT(A.IDLOWONGAN) AS JML_LOWONGAN, B.NAMA_PROPINSI, B.IDPROPINSI
							 FROM t_lowongan A
							 LEFT JOIN m_propinsi B ON A.IDPROPINSI = B.IDPROPINSI
							 WHERE A.STATUS = 1
							 GROUP BY A.IDPROPINSI");
	$respropinsi=	$db->query($sqlpropinsi);
	
	if($respropinsi <> '' && $respropinsi <> false){
		$listpropinsi		=	'';
		foreach($respropinsi as $key){
			$idpropinsi		=	$enkripsi->encode($key['IDPROPINSI']);
			$listpropinsi	.=	"	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
									<div class='tab-mapel'>
										<a href='".APP_URL."search_category?t=3&q=".$idpropinsi."'>".$key['NAMA_PROPINSI']." <small><em>(".$key['JML_LOWONGAN'].")</em></small></a>
									</div>
								</div>";
		}
	} else {
		$listpropinsi	=	"<center><b>Tidak ada data</b></center>";
	}
	
	switch($_GET['t']){
		case "1"	:	$field	=	"A.IDBIDANG"; break;
		case "2"	:	$field	=	"A.IDPOSISI"; break;
		case "3"	:	$field	=	"A.IDPROPINSI"; break;
		default		:	$field	=	"UNKNOWN"; break;
	}
	$condition	=	$enkripsi->decode($_GET['q']);
	
	$sqlfilter	=	sprintf("SELECT A.JUDUL, B.NAMA_PERUSAHAAN, A.TGL_PUBLISH, A.TGL_KADALUARSA,
									A.TGL_MULAI_KERJA, A.EMAIL_LOWONGAN, A.LEVEL_KARIR, C.NAMA_PROPINSI,
									D.NAMA_POSISI, A.IDPERUSAHAAN, A.IDLOWONGAN
							 FROM t_lowongan A
							 LEFT JOIN m_perusahaan B ON A.IDPERUSAHAAN = B.IDPERUSAHAAN
							 LEFT JOIN m_propinsi C ON A.IDPROPINSI = C.IDPROPINSI
							 LEFT JOIN m_posisi D ON A.IDPOSISI= D.IDPOSISI
							 WHERE %s = %s AND A.STATUS = 1
							 ORDER BY TGL_PUBLISH DESC"
							, $field
							, $condition
							);
	$result		=	$db->query($sqlfilter);
	
	if($result <> "" && $result <> false){
		
		$data	=	"";

		foreach($result as $key){

			switch($key['LEVEL_KARIR']){
				case "1"	:	$level	=	"Awal"; break;
				case "2"	:	$level	=	"Pertengahan"; break;
				case "3"	:	$level	=	"Senior"; break;
				case "4"	:	$level	=	"Teratas"; break;
				default		:	$level	=	"Tidak ada data"; break;
			}
			$idperusahaan	=	$enkripsi->encode($key['IDPERUSAHAAN']);
			$idlowongan		=	$enkripsi->encode($key['IDLOWONGAN']);
			
			if(isset($_SESSION['KursusLesLoker']) && $_SESSION['KursusLesLoker']['TYPEUSER'] == 2){
				$bookmark	=	"<a class='btn btn-custom btn-xs pull-right' onclick='addBookmark(\"".$idlowongan."\")' style='padding: 4px; margin-right: 3px'>
									 <i class='fa fa-bookmark'></i> Bookmark
								 </a>";
			} else {
				$bookmark	=	"";
			}
			
			$data	.=	"<div class='row'>
                            <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
                                <div class='boxSquareWhite'>
                                    <h4>".$key['JUDUL']."</h4><br>
                                    <span>Dipublikasi oleh : <strong><a href='".APP_URL."profil_pers?q=".$idperusahaan."'>".$key['NAMA_PERUSAHAAN']."</a></strong></span><br>
                                    <i class='fa fa-file-text-o'></i> <strong>".$key['NAMA_POSISI']."</strong><br/>
                                    <i class='fa fa-map-marker'></i> <strong>".$key['NAMA_PROPINSI']."</strong><br/>
                                    <i class='fa fa-calendar'></i> Tanggal Publikasi <strong>".$key['TGL_PUBLISH']." s/d ".$key['TGL_KADALUARSA']."</strong> - 
                                    <i class='fa fa-calendar'></i> Tanggal Mulai Kerja <strong>".$key['TGL_MULAI_KERJA']."</strong><br>";
			if($show_login == 'false'){
					//var_dump($show_login);
					$data .= "<i class='fa fa-envelope'></i> Email Lowongan <strong>".$key['EMAIL_LOWONGAN']."</strong><br>
					<i class='fa fa-external-link'></i> Level Karir <strong>".$level."</strong><br/>";
			}
			$data .= $bookmark."
									<a class='btn btn-custom btn-xs pull-right' href='".APP_URL."lowongan?i=".$idlowongan."' style='padding: 4px; margin-right: 3px' target='_blank'>
										<i class='fa fa-link'></i> Lihat Detail
									</a>
									<a class='btn btn-custom btn-xs pull-right' href='".APP_URL."lamar?i=".$idlowongan."' style='padding: 4px; margin-right: 3px' target='_blank'>
										<i class='fa fa-check'></i> Lamar
									</a><br/>
								</div>
                            </div>
                        </div><hr/>";
			
		}
		
	} else {
		$data	=	"	<div class='row'>
                            <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
                                <div class='boxSquareWhite'>
									<center><b>Tidak ada data yang ditampilkan</b></center>
								</div>
                            </div>
                        </div>";
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
        <br/><br/>
		<div class="container">
            <div role="tabpanel">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#bidang" aria-controls="bidang" role="tab" data-toggle="tab">BIDANG</a></li>
                    <li role="presentation"><a href="#posisi" aria-controls="posisi" role="tab" data-toggle="tab">POSISI</a></li>
                    <li role="presentation"><a href="#lokasi" aria-controls="lokasi" role="tab" data-toggle="tab">LOKASI</a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="bidang">
                        <div class="row">
                            <?=$listBidang?>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="posisi">
                        <div class="row">
                            <?=$listposisi?>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="lokasi">
                        <div class="row">
                            <?=$listpropinsi?>
                        </div>
                    </div>
                </div>
            </div>
		</div><hr>
        
		<div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
                        <h3>Daftar Lowongan Kerja</h3>
                        <div class="row">
                            <?=$data?>
                        </div>
                    </div>
                </div>
            </div>
        </div><br/><br/>
        
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
    	<?=$session->getTemplate('footer')?>
        <script>
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
							"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
							"<strong><small id='message_response'>"+msg+"</small></strong>"+
						"</div>";
			}
			function addBookmark(value){
				$('#message_response_container').slideUp('fast').html("");
				$('#message_response_container').slideDown('fast').html(generateMsg("Sedang menyimpan.."));
				$.post("<?=APP_URL?>search_category?func=<?=$enkripsi->encode('addBookmark')?>", {iddata : value})
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

    </body>
</html>