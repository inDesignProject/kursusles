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
	
	//DAFTAR PAKET PENGAJAR
	$idpengajar		=	$enkripsi->decode($_GET['q']);
	$sqlDaftarPaket	=	sprintf("SELECT A.IDPAKET, A.NAMA_PAKET, C.NAMA_MAPEL, D.FOTO
								 FROM t_paket A
								 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
								 LEFT JOIN m_mapel C ON B.IDMAPEL = C.IDMAPEL
								 LEFT JOIN m_pengajar D ON B.IDPENGAJAR = D.IDPENGAJAR
								 WHERE B.IDPENGAJAR = %s AND A.STATUS = 1"
								, $idpengajar
								);
	$resultDaftar	=	$db->query($sqlDaftarPaket);
	if(isset($_GET['idp']) && $_GET['idp'] <> ''){
		$clicked	=	$_GET['idp'];
	} else {
		$clicked	=	$enkripsi->encode($resultDaftar[0]['IDPAKET']);
	}
	//HABIS -- DAFTAR PAKET PENGAJAR
	
	//FUNGSI DIGUNAKAN MELIHAT DETAIL DATA PAKET
	if( $enkripsi->decode($_GET['func']) == "detailPaket" && isset($_GET['func'])){
		$idpaket		=	$enkripsi->decode($_POST['idpaket']);
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
			
			$detailPaket=	"<ul style='list-style:none'>".
							"	<li class='namapaket'>".$resultPaket['NAMA_PAKET']." - ".$resultPaket['NAMA_MAPEL']."</li>".
							"	<li class='jenispaket'>".$jenis." ( ".$resultPaket['JUMLAH_MURID']." Murid ) - ".$resultPaket['TOTAL_PERTEMUAN']." Pertemuan </li>".
							"	<li class='harga'>Rp. ".number_format($resultPaket['HARGA'],0,',','.')." </li>".
							"	<li class='waktu'>".$resultPaket['WAKTU']." Jam </li>".
							"	<li class='lokasi'>".$resultPaket['TEMPAT']." </li>".
							"	<li class='materi'>".$resultPaket['MATERI']."</li>".
							"</ul><br/>";
			if($_SESSION['KursusLes']['TYPEUSER'] != 2 && isset($_SESSION['KursusLes']['IDUSER'])){
				$detailPaket.=	"<a class='btn btn-custom btn-xs' onclick='addBookmark(\"".$_POST['idpaket']."\")'>
									<i class='fa fa-bookmark'></i> Bookmark
								 </a> &nbsp; ";
			}
			$detailPaket.=	"<a href='belipaket?q=".$enkripsi->encode($resultPaket['IDPAKET'])."' class='btn btn-custom btn-xs pull-right'>Pilih Paket >> </a>";
		} else {
			$detailPaket=	"<center><b>Tidak ada detail yang ditampilkan</b></center>";
		}
		
		echo $detailPaket;
		die();
		
	}

	//FUNGSI ADD BOOKMARK
	if( $enkripsi->decode($_GET['func']) == "addBookmark" && isset($_GET['func'])){
		$idp		=	$enkripsi->decode($_POST['iddata']);
		$sqlCek		=	sprintf("SELECT IDBOOKMARK FROM t_bookmark
								 WHERE IDMURID = %s AND TYPE = 2 AND IDJOIN= %s"
								, $_SESSION['KursusLes']['IDUSER']
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
								 (%s,2,%s)"
								, $_SESSION['KursusLes']['IDUSER']
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
            	DAFTAR PAKET PENGAJAR
            	<img src="<?=APP_IMG_URL?>generate_pic.php?type=pr&q=<?=$enkripsi->encode($resultDaftar[0]['FOTO'])?>&w=48&h=48" class="img-responsive img-circle img-profile pull-right" style="margin-top: -15px;">
            </h3>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Daftar Paket</b></div>
                            <div class="panel-body">
                                <?php
									if($resultDaftar <> false && $resultDaftar <> ''){
								?>
                                        <div role="tabpanel">
                                            <ul class="nav nav-tabs" role="tablist">
                                                <?php
													$i		=	0;
													$list	=	'';
													foreach($resultDaftar as $key){
														$active	=	$i == 0 ? "active" : "";
														$list	.=	"<li role='presentation' class='".$active." tab_list'>
																		<a href='#' id='".$enkripsi->encode($key['IDPAKET'])."' onclick='getDetail(this.id)' aria-controls='".$enkripsi->encode($key['IDPAKET'])."' role='tab' data-toggle='tab'>
																			".$key['NAMA_PAKET']."<br/>
																		</a>
																	 </li><br/>";
														$i++;
													}
													echo $list;
												?>
                                            </ul>
                                        </div>
                                <?php
									} else {
										echo "<center><b>Tidak ada paket yang ditampilkan untuk pengajar ini</b></center>";
									}
								?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Detail Paket</b></div>
                            <div class="panel-body" id="detailPaket">
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
		<script>
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
						"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
						"<strong><small id='message_response'>"+msg+"</small></strong>"+
					"</div>";
			}
			function getDetail(id){
				$('.tab_list').removeClass('active');
				$('#'+id).closest('li').addClass('active');
				$('#detailPaket').html("<center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang Memuat...</center>");
				$.post( "detail_paket?func=<?=$enkripsi->encode('detailPaket')?>", {idpaket: id})
				.done(function( data ) {
					
					$('#detailPaket').html(data);
					
				});
		
			}
			function addBookmark(value){
				$('#message_response_container').slideUp('fast').html("");
				$.post("<?=APP_URL?>detail_paket?func=<?=$enkripsi->encode('addBookmark')?>", {iddata : value})
				.done(function( data ) {
					
					data			=	JSON.parse(data);
					if(data['respon_code'] == "00000"){
						$('#message_response_container').slideDown('fast').html(generateMsg("Bookmark sudah ditambahkan. Cek bookmark di halaman utama pada tab Bookmark"));
					} else {
						$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
					}
			
				});
				
			}

			$(document).ready(function(){
				getDetail('<?=$clicked?>');
			});
        </script>
        
    	<?=$session->getTemplate('footer')?>

    </body>
</html>