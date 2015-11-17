<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
		
	//FUNGSI DIGUNAKAN UNTUK TERIMA PERMINTAAN
	if( $enkripsi->decode($_GET['func']) == "terimaPermintaan" && isset($_GET['func'])){
		$iddata		=	$enkripsi->decode($_POST['id']);
		
		$sqlupd		=	sprintf("UPDATE t_pengajuan_jasa SET STATUS = 1 WHERE IDPENGAJUANJASA = %s", $iddata);
		$affected	=	$db->execSQL($sqlupd, 0);
		
		if($affected > 0){
			
			$sqlDet		=	sprintf("SELECT B.NAMA, B.EMAIL, C.NAMA_PAKET, E.NAMA AS NAMA_PENGAJAR, E.IDPENGAJAR, C.IDPAKET
									 FROM t_pengajuan_jasa A
									 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
									 LEFT JOIN t_paket C ON A.IDPAKET = C.IDPAKET
									 LEFT JOIN t_mapel_pengajar D ON C.IDMAPELPENGAJAR = D.IDMAPELPENGAJAR
									 LEFT JOIN m_pengajar E ON D.IDPENGAJAR = E.IDPENGAJAR
									 WHERE A.IDPENGAJUANJASA = %s
									 LIMIT 0,1"
									, $iddata
									);
			$resultDet	=	$db->query($sqlDet);
			$resultDet	=	$resultDet[0];
			$email		=	$resultDet['EMAIL'];
			$nama		=	$resultDet['NAMA'];
			$idpeng		=	$enkripsi->encode($resultDet['IDPENGAJAR']);
			$idpkt		=	$enkripsi->encode($resultDet['IDPAKET']);
			
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								
								<p>
								kami informasikan bahwa pemesanan paket <a target='_blank' href='".APP_URL."detail_paket?q=".$idpeng."&idp=".$idpkt."'>".$resultDet['NAMA_PAKET']."</a> di KursusLes.com telah diterima oleh pengajar <a target='_blank' href='".APP_URL."pengajar_profil?q=".$idpeng."'>".$resultDet['NAMA_PENGAJAR']."</a>. Untuk
								selanjutnya anda dapat menghubungi pihak pengajar untuk memulai kegiatan belajar sesuai dengan kesepakatan.
								Anda dapat menghubungi pihak pengajar melalui kontak yang ada pada profil pengajar, email, maupun private
								message yang telah kami sediakan.<br/><br/>
								Lakukan konfimasi setiap setelah melakukan kegiatan belajar agar dana pembelian paket dapat kami teruskan kepada pihak pengajar.
								Konfirmasi kegiatan belajar dapat anda lakukan dengan masuk ke menu <b>Kursus Saya</b> pada halaman utama anda setelah masuk. 
								Anda dapat langsung melakukan konfirmasi dengan atau tanpa adanya klaim sebelumnya dari pihak pengajar bahwa 
								sudah melakukan kegiatan mengajar.
								<br/><br/>
								Jika ternyata pihak pengajar tidak melakukan kewajibannya untuk mengajar, harap segera laporkan agar dapat
								kami tindak lanjuti.
								<br/><br/>
								Demikian pemberitahuan dari kami.
								</p>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Pemberitahuan Pemesanan Paket Kursus", $message);
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data perubahan disimpan"));
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menyimpan perubahan, silakan coba lagi nanti"));
		}
		die();
	}
	
	//FUNGSI DIGUNAKAN UNTUK TOLAK PERMINTAAN
	if( $enkripsi->decode($_GET['func']) == "tolakPermintaan" && isset($_GET['func'])){
		$iddata		=	$enkripsi->decode($_POST['id']);
		
		$sqlupd		=	sprintf("UPDATE t_pengajuan_jasa SET STATUS = 2 WHERE IDPENGAJUANJASA = %s", $iddata);
		$affected	=	$db->execSQL($sqlupd, 0);
		
		if($affected > 0){
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data perubahan disimpan"));
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menyimpan perubahan, silakan coba lagi nanti"));
		}
		die();
	}
	
	//FUNGSI DIGUNAKAN UNTUK FORM KLAIM
	if( $enkripsi->decode($_GET['func']) == "getFormKlaim" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['id']);
		$sqlform	=	sprintf("SELECT B.JUMLAH_PERTEMUAN, IFNULL(A.PEMBAYARAN_KE, 0) AS PEMBAYARAN_KE,
										IFNULL(SUM(A.JUMLAH_BAYAR),0) AS SUDAH_BAYAR, B.RP_PERPERTEMUAN, B.RP_TOTAL
								 FROM t_pengajuan_jasa B
								 LEFT JOIN (SELECT * FROM log_kursus WHERE IDPENGAJUANJASA = %s) 
								 	  A ON A.IDPENGAJUANJASA = B.IDPENGAJUANJASA
								 WHERE B.IDPENGAJUANJASA = %s
								 GROUP BY B.IDPENGAJUANJASA
								 ORDER BY A.PEMBAYARAN_KE DESC
								 LIMIT 0,1"
								, $iddata
								, $iddata
								);
		$resultform	=	$db->query($sqlform);
		$resultform	=	$resultform[0];
		$jml_pert	=	$resultform['JUMLAH_PERTEMUAN'];
		$byr_ke		=	$resultform['PEMBAYARAN_KE'] + 1;
		$txtke		=	$jml_pert == $byr_ke ? $jml_pert : $byr_ke;
		$nom_byr	=	$jml_pert == $byr_ke ? $resultform['RP_TOTAL'] - $resultform['SUDAH_BAYAR'] : $resultform['RP_PERPERTEMUAN'];
		
		echo	"Mohon untuk segera konfirmasikan untuk biaya kursus :<br/>
				 Pertemuan Ke : ".$txtke."<br/>
				 Sejumlah : Rp. ".number_format($nom_byr, 0, ',', '.').",-<br/>
				 Waktu Pelaksanaan : <input name='tgldata' id='tgldata' maxlength='19' autocomplete='off' readonly value='".date('Y-m-d H:i:s')."' style='width:175px; text-align: center;' type='text'><br/>
				 Sesuai dengan kegiatan yang telah dilaksanakan pada waktu tersebut idatas";
		
		die();
		
	}
	
	//FUNGSI DIGUNAKAN UNTUK DETAIL DATA
	if( $enkripsi->decode($_GET['func']) == "detailData" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['iddata']);
		$sqlDetail	=	sprintf("SELECT A.TGL_AWAL, A.RP_PERPERTEMUAN, B.MATERI, A.STATUS, D.FOTO, D.IDMURID, D.NAMA
								 FROM t_pengajuan_jasa A
								 LEFT JOIN t_paket B ON A.IDPAKET = B.IDPAKET
								 LEFT JOIN t_mapel_pengajar C ON B.IDMAPELPENGAJAR = C.IDMAPELPENGAJAR
								 LEFT JOIN m_murid D ON A.IDMURID = D.IDMURID
								 WHERE A.IDPENGAJUANJASA = %s
								 LIMIT 0,1"
								, $iddata
								);
		$resultDet	=	$db->query($sqlDetail);
	
		if($resultDet <> false && $resultDet <> ""){
			
			$resultDet	=	$resultDet[0];
			switch($resultDet['STATUS']){
				case "0"	:	$ketstatus	=	"<b style='color: #E8A40C'>Menunggu Persetujuan Pengajar</b>"; break;
				case "1"	:	$ketstatus	=	"<b style='color: green'>Aktif</b>"; break;
				case "2"	:	$ketstatus	=	"<b style='color: red'>Pengajuan Ditolak</b>"; break;
				case "3"	:	$ketstatus	=	"<b style='color: red'>Kursus Kadaluarsa</b>"; break;
				case "4"	:	$ketstatus	=	"<b style='color: red'>Pembatalan Otomatis</b>"; break;
				default		:	$ketstatus	=	"<b style='color: red'>-</b>"; break;
			}
			
			if($resultDet['STATUS'] == 1){
				$sqllog			=	sprintf("SELECT PEMBAYARAN_KE,TGL_PELAKSANAAN,TGL_KLAIM,TGL_BAYAR,JUMLAH_BAYAR,STATUS
											 FROM log_kursus
											 WHERE IDPENGAJUANJASA = %s"
											, $iddata
											);
				$resultlog		=	$db->query($sqllog);
				
				if($resultlog <> '' && $resultlog <> false){
					foreach($resultlog as $keylog){
						switch($keylog['STATUS']){
							case "0"	:	$ketstatuslog	=	"<b style='color: #E8A40C'>Menunggu Klaim</b>"; break;
							case "1"	:	$ketstatuslog	=	"<b style='color: green'>Klaim Tuntas</b>"; break;
							default		:	$ketstatuslog	=	"<b style='color: red'>-</b>"; break;
						}
						$dataRiwayat	.=	"<tr>
												<td align='right'>".$keylog['PEMBAYARAN_KE']."</td>
												<td align='center'>".$keylog['TGL_PELAKSANAAN']."</td>
												<td align='center'>".$keylog['TGL_KLAIM']."</td>
												<td align='center'>".$keylog['TGL_BAYAR']."</td>
												<td align='right'>".number_format($keylog['JUMLAH_BAYAR'], 0, ',', '.')."</td>
												<td>".$ketstatuslog."</td>
											 </tr>";
					}
				} else {
					$dataRiwayat=	"<tr><td colspan='6' align='center'><b>Tidak ada riwayat yang ditampilkan</b></td></tr>";
				}
				
				$listRiwayat	=	"<div class='boxSquareWhite'>
										<div class='row'>
											<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
												<h4>
													Riwayat Kursus dan Pembayaran
													<input type='button' value='Tambah Klaim' class='btn btn-sm btn-custom2 pull-right' onclick='setKlaim(\"".$enkripsi->encode($iddata)."\")'>
												</h4><br/>
												<table class='table table-bordered'>
													<thead>
														<tr align='center'>
															<td>Pembayaran Ke</td>
															<td>Tgl Kursus</td>
															<td>Tgl Klaim</td>
															<td>Tgl Pembayaran</td>
															<td>Jumlah Rp</td>
															<td>Status</td>
														</tr>
													</thead>
													<tbody>
														".$dataRiwayat."
													</tbody>
												</table>
											</div>
										</div>
									</div>";
			}
			
			$foto	=	$enkripsi->encode($resultDet['FOTO']);
			$dataD	=	"<div class='boxSquareWhite'>
							<div class='row'>
								<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
									<a href='#' target='_blank'>
										<img src='".APP_IMG_URL."generate_pic.php?type=pr&q=".$foto."&w=48&h=48' class='img-circle'/>
										<b>".$resultDet['NAMA']."</b><br/>
									</a><br/>
									Status : ".$ketstatus."<br/><br/>
									<i class='fa fa-calendar'></i><small> Tanggal Awal : ".date('Y-m-d',strtotime($resultDet['TGL_AWAL']))."</small><br/>
									<i class='fa fa-tags'></i><small> Biaya Per Pertemuan : RP. ".number_format($resultDet['RP_PERPERTEMUAN'],0,',','.')."</small><br/>
									<i class='fa fa-files-o'></i><small> Materi : <br/>".$resultDet['MATERI']."</small>
								</div>
							</div>
						</div>
						<br/>
						".$listRiwayat;
			
		} else {
			$dataD	=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
		}
		
		echo $dataD;
		die();
	}

	$idpengajar	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	$sql		=	sprintf("SELECT D.NAMA, B.NAMA_PAKET, A.JUMLAH_PERTEMUAN, A.JUMLAH_PEMBAYARAN, B.JUMLAH_MURID, B.TEMPAT, A.RP_TOTAL,
									A.TGL_AWAL, A.TGL_KADALUARSA, A.IDPENGAJUANJASA, A.STATUS
							 FROM t_pengajuan_jasa A
							 LEFT JOIN t_paket B ON A.IDPAKET = B.IDPAKET
							 LEFT JOIN t_mapel_pengajar C ON B.IDMAPELPENGAJAR = C.IDMAPELPENGAJAR
							 LEFT JOIN m_murid D ON A.IDMURID = D.IDMURID
							 WHERE C.IDPENGAJAR = %s AND A.STATUS NOT IN (0,4)
							 ORDER BY A.STATUS, A.TGL_KADALUARSA DESC"
							, $idpengajar
							);
	$result		=	$db->query($sql);
	
	if($result <> '' && $result <> false){
		foreach($result as $key){
			switch($key['STATUS']){
				case "0"	:	$ketstatus	=	"<b style='color: #E8A40C'>Menunggu Persetujuan Pengajar</b>"; break;
				case "1"	:	$ketstatus	=	"<b style='color: green'>Aktif</b>"; break;
				case "2"	:	$ketstatus	=	"<b style='color: red'>Ditolak</b>"; break;
				case "3"	:	$ketstatus	=	"<b style='color: red'>Kadaluarsa</b>"; break;
				case "4"	:	$ketstatus	=	"<b style='color: red'>Pembatalan Otomatis</b>"; break;
				default		:	$ketstatus	=	"<b style='color: red'>-</b>"; break;
			}
			$listdata	.=	"<tr id='row".$enkripsi->encode($key['IDPENGAJUANJASA'])."'>
								<td>".$key['NAMA']."</td>
								<td>".$key['NAMA_PAKET']."</td>
								<td align='right'>".$key['JUMLAH_PERTEMUAN']."</td>
								<td align='right'>".$key['JUMLAH_PEMBAYARAN']."</td>
								<td align='right'>".$key['JUMLAH_MURID']."</td>
								<td>".$key['TEMPAT']."</td>
								<td align='right'>Rp. ".number_format($key['RP_TOTAL'],0 ,',','.')."</td>
								<td align='center'>".$key['TGL_AWAL']."</td>
								<td align='center'>".$key['TGL_KADALUARSA']."</td>
								<td>".$ketstatus."</td>
								<td align='center'>
									<input type='button' value='Detail' class='btn btn-sm btn-custom2' onclick='showDetail(\"".$enkripsi->encode($key['IDPENGAJUANJASA'])."\")'>
								</td>
							 </tr>";
		}
	} else {
		$listdata	=	"<tr><td colspan='11'><center><b>Tidak ada data yang ditemukan</b></center></td></tr>";
	}
	
	$sqlnews	=	sprintf("SELECT D.NAMA, A.IDMURID, B.NAMA_PAKET, A.IDPENGAJUANJASA, A.IDPAKET, C.IDPENGAJAR
							 FROM t_pengajuan_jasa A
							 LEFT JOIN t_paket B ON A.IDPAKET = B.IDPAKET
							 LEFT JOIN t_mapel_pengajar C ON B.IDMAPELPENGAJAR = C.IDMAPELPENGAJAR
							 LEFT JOIN m_murid D ON A.IDMURID = D.IDMURID
							 WHERE C.IDPENGAJAR = %s AND A.STATUS IN (0,4)"
							, $idpengajar
							);
	$resultnews	=	$db->query($sqlnews);
	
	if($resultnews <> '' && $resultnews <> false){
		
		$listNews	=	'<ul id="news-list">';
		foreach($resultnews as $keynews){
			$idp		=	$enkripsi->encode($keynews['IDPENGAJAR']);
			$idpaket	=	$enkripsi->encode($keynews['IDPAKET']);
			$idpengajuan=	$enkripsi->encode($keynews['IDPENGAJUANJASA']);
			$listNews	.=	"<li>
								<a href='#' target='_blank'>".$keynews['NAMA']."</a>
								Membeli paket <a href='detail_paket?q=".$idp."&idp=".$idpaket."' target='_blank'>".$keynews['NAMA_PAKET']."</a>
								Apa yang akan anda lakukan?
								<a href='#' class='btn btn-kursusles btn-sm pull-right' style='margin-right: 8px;' onclick='setApproval(\"".$idpengajuan."\", false)'>
									<i class='fa fa-times-circle'></i> Tolak
								</a>
								<a href='#' class='btn btn-kursusles btn-sm pull-right' style='margin-right: 8px;' onclick='setApproval(\"".$idpengajuan."\", true)'>
									<i class='fa fa-check-circle'></i> Terima
								</a>
							</li>
							<hr style='margin:8px !important'></hr>";
		}
		$listNews	.=	"</ul>";
		
	} else {
		$listNews	=	"Tidak ada pemberitahuan";
	}

	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
		
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
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssKjadwal');?>.cssfile">
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssdatepicker');?>.cssfile" />
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

		<style>
            .rowDetail{
                border-bottom: 3px solid #ccc;
                border-left: 3px solid #ccc;
                border-right: 3px solid #ccc;
                max-height: 300px;
                overflow:scroll;
            }
            .rowData-show{
                border-top: 3px solid #ccc;
                border-left: 3px solid #ccc;
                border-right: 3px solid #ccc;
            }
        </style>
    
        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

		<?=$session->getTemplate('header', $show_login)?>
    	
        <div class="container">
        	<h3 class="text-left text_kursusles page-header">KURSUS SAYA</h3>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
                        <div id="data_container">
                        	<div class="boxSquareWhite">
                               <b><i class="fa fa-star"></i> Pemberitahuan</b><br>
                               <?=$listNews?><br>
                           </div>
                           <h4>Daftar Kursus</h4>
                           <table class="table table-bordered" id="data_table">
                                <thead>
                                    <tr>
                                        <th class="text-center">Nama Murid</th>
                                        <th class="text-center">Nama Paket</th>
                                        <th class="text-center">Jumlah<br/>Pertemuan</th>
                                        <th class="text-center">Jumlah<br/>Bayar</th>
                                        <th class="text-center">Jumlah<br/>Murid</th>
                                        <th class="text-center">Tempat</th>
                                        <th class="text-center">Harga</th>
                                        <th class="text-center">Tgl Awal</th>
                                        <th class="text-center">Tgl Kadaluarsa</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="bodyData">
                                    <?=$listdata?>
                                    </tr>
                                </tbody>
                            </table>
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
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
		<script>
			function setApproval(id, status){

				$('#message_response_container').slideDown('fast').html('');
				if(status == true){
					$("#dialog-confirm").dialog({
						closeOnEscape: false,
						modal: true,
						width: "50%",
						title: "Konfirmasi",
						draggable: false,
						resizable: false,
						position: { my: "center", at: "top" },
						open: function() {
						  $(this).html("Yakin menerima data permintaan kursus?<br/>Jika ya, maka anda akan menyetujui syarat :<ul><li>Anda akan memberikan jasa kursus kepada pihak murid</li><li>Jika ternyata kewajiban anda tidak dipenuhi, maka kami akan mengembalikan dana kursus ke pihak murid berdasarkan laporan yang kami terima</li><li>Komunikasikan dengan pihak murid mengenai kegiatan belajar melalui kontak yang tersedia atau melalui private message yang kami sediakan</li><li>Kami akan memberikan email notifikasi kepada pihak murid sebagai pemberitahuan bahwa anda menyetujui permintaan kursus dan kegiatan mengajar sudah dapat dilakukan sesuai dengan ketersediaan jadwal anda</li></ul>");
						},
						close: function() {
							$(this).dialog( "close" );
						},
						buttons: {
							"Ya": function() {
								$(this).dialog( "close" );
								$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang menyimpan..'));
								$.post( "<?=APP_URL?>pengajar_kursus?func=<?=$enkripsi->encode('terimaPermintaan')?>", {id: id})
								.done(function( data ) {
									
									data			=	JSON.parse(data);
									if(data['respon_code'] != "00000"){
										$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
									} else {
										window.location.reload();
									}
									
								});
							},
							"Batal": function() {
								$(this).dialog( "close" );
							}
						}
					});
				} else {
					$("#dialog-confirm").dialog({
						closeOnEscape: false,
						modal: true,
						width: "33%",
						title: "Konfirmasi",
						draggable: false,
						resizable: false,
						position: { my: "center", at: "top" },
						open: function() {
						  $(this).html("Yakin menolak data permintaan kursus?");
						},
						close: function() {
							$(this).dialog( "close" );
						},
						buttons: {
							"Ya": function() {
								$(this).dialog( "close" );
								$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang menyimpan..'));
								$.post( "<?=APP_URL?>pengajar_kursus?func=<?=$enkripsi->encode('tolakPermintaan')?>", {id: id})
								.done(function( data ) {
									
									data			=	JSON.parse(data);
									if(data['respon_code'] != "00000"){
										$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
									} else {
										window.location.reload();
									}
									
								});
							},
							"Batal": function() {
								$(this).dialog( "close" );
							}
						}
					});
				}
			}
			function setKlaim(id){
				$.post( "<?=APP_URL?>pengajar_kursus?func=<?=$enkripsi->encode('getFormKlaim')?>", {id: id})
				.done(function( dataconf ) {
					$("#dialog-confirm").dialog({
						closeOnEscape: false,
						modal: true,
						width: "50%",
						title: "Permintaan Konfirmasi",
						draggable: false,
						resizable: false,
						position: { my: "center", at: "top" },
						open: function() {
							
							$(this).html(dataconf);
							$('#tgldata').datetimepicker({
								format:'Y-m-d H:i:s',
								lang:'id',
								closeOnDateSelect:true
							});
							
						},
						close: function() {
							$(this).dialog( "close" );
						},
						buttons: {
							"Ya": function() {
								$(this).dialog( "close" );
								$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang menyimpan..'));
								$.post( "<?=APP_URL?>pengajar_kursus?func=<?=$enkripsi->encode('terimaPermintaan')?>", {id: id})
								.done(function( data ) {
									
									data			=	JSON.parse(data);
									if(data['respon_code'] != "00000"){
										$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
									} else {
										window.location.reload();
									}
									
								});
							},
							"Batal": function() {
								$(this).dialog( "close" );
							}
						}
					});
				});
			}
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
						"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
						"<strong><small id='message_response'>"+msg+"</small></strong>"+
					"</div>";
			}
			function showDetail(value){
				$('.rowDetail').slideUp('fast').remove();
				$('#bodyData').find('tr').removeClass('rowData-show');
				$("#row"+value).after("<tr class='rowDetail'><td colspan ='11' id='con-"+value+"'><center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>").addClass('rowData-show');
				
				$.post( "<?=APP_URL?>pengajar_kursus?func=<?=$enkripsi->encode('detailData')?>", {iddata : value})
				.done(function( data ) {
					$("#con-"+value).html(data);
				});
				
			}
		</script>
        <br><br><br>
    	<?=$session->getTemplate('footer')?>
	</body>
</html>