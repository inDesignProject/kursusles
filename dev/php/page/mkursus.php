<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	$sql = '';
	$sql .= 'select idmurid from m_murid where iduser ='.$_SESSION['KursusLes']['IDPRIME'];
	
	$ismurid	=	$db->query($sql);
	
	$idmurid	=	$_GET['q'] == "" && !isset($_GET['q']) ? $ismurid[0]['idmurid'] : $enkripsi->decode($_GET['q']) ;
	
	//FUNGSI DIGUNAKAN UNTUK MENOLAK KLAIM
	if( $enkripsi->decode($_GET['func']) == "ignoreKlaim" && isset($_GET['func'])){
		
		$iddata			=	$enkripsi->decode($_POST['id']);
		
		$_POST['alasan']=	$_POST['alasan'] == "" ? "-" : $_POST['alasan'];
		$sqlupd			=	sprintf("UPDATE log_kursus
									 SET STATUS = -1, ISREAD = 1 ALASAN_TOLAK = '%s'
									 WHERE IDLOG = %s"
									 , $db->db_text($_POST['alasan'])
									 , $iddata
									);
		$affected		=	$db->execSQL($sqlupd, 0);
		
		if($affected > 0){
			$sqlDet		=	sprintf("SELECT E.NAMA, E.EMAIL, C.NAMA_PAKET, B.NAMA AS NAMA_MURID, X.JUMLAH_BAYAR, X.PEMBAYARAN_KE,
											X.TGL_PELAKSANAAN
									 FROM log_kursus X
									 LEFT JOIN t_pengajuan_jasa A ON X.IDPENGAJUANJASA = A.IDPENGAJUANJASA
									 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
									 LEFT JOIN t_paket C ON A.IDPAKET = C.IDPAKET
									 LEFT JOIN t_mapel_pengajar D ON C.IDMAPELPENGAJAR = D.IDMAPELPENGAJAR
									 LEFT JOIN m_pengajar E ON D.IDPENGAJAR = E.IDPENGAJAR
									 WHERE X.IDLOG = %s
									 LIMIT 0,1"
									, $iddata
									);
			$resultDet	=	$db->query($sqlDet);
			$resultDet	=	$resultDet[0];
			$email		=	$resultDet['EMAIL'];
			$nama		=	$resultDet['NAMA'];
			
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								
								<p>
								Data klaim dana jasa yang anda ajukan dengan data:<br/><br/>
								Nama Murid : ".$resultDet['NAMA_MURID']."<br/>
								Paket : ".$resultDet['NAMA_PAKET']."<br/>
								Jumlah Pertemuan : ".$resultDet['JUMLAH_PERTEMUAN']."<br/>
								Pertemuan Ke- : ".$resultDet['PEMBAYARAN_KE']."<br/>
								Waktu Kursus : ".$resultDet['TGL_PELAKSANAAN']."<br/>
								Biaya pertemuan : Rp. ".number_format($resultDet['JUMLAH_BAYAR'],0,',','.').",-<br/><br/>
								ditolak oleh pihak murid dengan alasan : ".$_POST['alasan'].". Dengan demikian dana tidak dapat kami teruskan dan menunggu untuk klaim dan konfirmasi selanjutnya.
								Demikian pemberitahuan dari kami.
								</p><br/><br/>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Pemberitahuan Pembayaran Jasa mengajar", $message);
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data perubahan disimpan"));
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menyimpan perubahan, silakan coba lagi nanti"));
		}
		die();
	}
	
	//FUNGSI DIGUNAKAN UNTUK KONFIRMASI KLAIM
	if( $enkripsi->decode($_GET['func']) == "konfirmKlaim" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['id']);
		$sqlupd		=	sprintf("UPDATE log_kursus A
								 LEFT JOIN t_pengajuan_jasa B ON A.IDPENGAJUANJASA = B.IDPENGAJUANJASA
								 SET A.STATUS = 1, A.TGL_BAYAR = NOW(), B.JUMLAH_PEMBAYARAN = A.PEMBAYARAN_KE
								 WHERE A.IDLOG = %s"
								 , $iddata
								);
		$affected	=	$db->execSQL($sqlupd, 0);
		
		if($affected > 0){
			
			$sqlDet		=	sprintf("SELECT E.NAMA, E.EMAIL, C.NAMA_PAKET, B.NAMA AS NAMA_MURID, X.JUMLAH_BAYAR, X.PEMBAYARAN_KE,
											X.IDPENGAJUANJASA, E.IDPENGAJAR, A.JUMLAH_PERTEMUAN, X.TGL_PELAKSANAAN
									 FROM log_kursus X
									 LEFT JOIN t_pengajuan_jasa A ON X.IDPENGAJUANJASA = A.IDPENGAJUANJASA
									 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
									 LEFT JOIN t_paket C ON A.IDPAKET = C.IDPAKET
									 LEFT JOIN t_mapel_pengajar D ON C.IDMAPELPENGAJAR = D.IDMAPELPENGAJAR
									 LEFT JOIN m_pengajar E ON D.IDPENGAJAR = E.IDPENGAJAR
									 WHERE X.IDLOG = %s
									 LIMIT 0,1"
									, $iddata
									);
			$resultDet	=	$db->query($sqlDet);
			$resultDet	=	$resultDet[0];
			$email		=	$resultDet['EMAIL'];
			$nama		=	$resultDet['NAMA'];
			$ketbalance	=	"Pembayaran biaya jasa kursus paket ".$resultDet['NAMA_PAKET']." pertemuan ke-".$resultDet['PEMBAYARAN_KE']." dari ".$resultDet['NAMA_MURID']." sejumlah Rp. ".number_format($resultDet['JUMLAH_BAYAR'],0,',','.').",-";
			$sqlInsBl	=	sprintf("INSERT INTO t_balance
									 (IDJENISTRANSAKSI, IDLOGKURSUS, JENIS_PEMILIK, IDUSER, TGL_TRANSAKSI, DEBET, KETERANGAN)
									 VALUES
									 (4, %s, 1, %s, NOW(), %s, '%s')"
									, $iddata
									, $resultDet['IDPENGAJAR']
									, $resultDet['JUMLAH_BAYAR']
									, $ketbalance
									);
			$db->execSQL($sqlInsBl, 0);
			
			$sqlupdBl	=	sprintf("UPDATE m_pengajar 
									 SET CURRENT_BALANCE = CURRENT_BALANCE + %s 
									 WHERE IDPENGAJAR = %s"
									 , $resultDet['JUMLAH_BAYAR']
									 , $resultDet['IDPENGAJAR']);
			$db->execSQL($sqlupdBl, 0);
			
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								
								<p>
								Data klaim dana jasa yang anda ajukan dengan data:<br/><br/>
								Paket : ".$resultDet['NAMA_PAKET']."<br/>
								Jumlah Pertemuan : ".$resultDet['JUMLAH_PERTEMUAN']."<br/>
								Pertemuan Ke- : ".$resultDet['PEMBAYARAN_KE']."<br/>
								Waktu Kursus : ".$resultDet['TGL_PELAKSANAAN']."<br/>
								Biaya pertemuan : Rp. ".number_format($resultDet['JUMLAH_BAYAR'],0,',','.').",-<br/><br/>
								sudah dikonfirmasi dan dana secara otomatis masuk ke dalam balance anda.
								Demikian pemberitahuan dari kami.
								</p><br/><br/>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Pemberitahuan Pembayaran Jasa mengajar", $message);
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data perubahan disimpan"));
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menyimpan perubahan, silakan coba lagi nanti"));
		}
		die();
	}
	
	//FUNGSI DIGUNAKAN UNTUK FORM KONFIRMASI
	if( $enkripsi->decode($_GET['func']) == "getFormKonfirm" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['id']);
		$sqlform	=	sprintf("SELECT B.JUMLAH_PERTEMUAN, IFNULL(A.PEMBAYARAN_KE, 0) AS PEMBAYARAN_KE,
										IFNULL(SUM(A.JUMLAH_BAYAR),0) AS SUDAH_BAYAR, B.RP_PERPERTEMUAN, B.RP_TOTAL,
										B.JUMLAH_PEMBAYARAN
								 FROM t_pengajuan_jasa B
								 LEFT JOIN (SELECT * FROM log_kursus WHERE IDPENGAJUANJASA = %s AND STATUS = 1) 
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
		
		if($resultform['JUMLAH_PEMBAYARAN'] < $resultform['JUMLAH_PERTEMUAN']){
			
			$jml_pert	=	$resultform['JUMLAH_PERTEMUAN'];
			$byr_ke		=	$resultform['JUMLAH_PEMBAYARAN'] * 1 + 1;
			$txtke		=	$jml_pert == $byr_ke ? $jml_pert : $byr_ke;
			$nom_byr	=	$jml_pert == $byr_ke ? $resultform['RP_TOTAL'] - $resultform['SUDAH_BAYAR'] : $resultform['RP_PERPERTEMUAN'];
			
			$respon		=	"Konfirmasi biaya kursus :<br/>
							 Pertemuan Ke : ".$txtke."<br/>
							 Sejumlah : Rp. ".number_format($nom_byr, 0, ',', '.').",-<br/>
							 Waktu Kegiatan : <input name='tgldata' id='tgldata' maxlength='19' autocomplete='off' readonly value='".date('Y-m-d H:i:s')."' style='width:180px; text-align: center;' type='text'><br/>
							 <input type='hidden' name='ke' id='ke' value='".$txtke."'>
							 <input type='hidden' name='nominal' id='nominal' value='".$nom_byr."'>
							 Saya menyetujui bahwa kegiatan belajar - mengajar sudah dilaksanakan";
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>$respon));
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Jumlah pembayaran sudah terpenuhi semua."));
		}
		
		die();
		
	}

	//FUNGSI DIGUNAKAN UNTUK KIRIM KONFIRMASI
	if( $enkripsi->decode($_GET['func']) == "kirimKonfirm" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['id']);
		
		$sqlcek		=	sprintf("SELECT IDLOG FROM log_kursus WHERE IDPENGAJUANJASA = %s AND STATUS = 0 LIMIT 0,1", $iddata);
		$resultcek	=	$db->query($sqlcek);
		
		if($resultcek <> '' && $resultcek <> false){

			$idlog	=	$resultcek[0]['IDLOG'];

		} else {
			
			$sqlIns	=	sprintf("INSERT INTO log_kursus
								 (IDPENGAJUANJASA,PEMBAYARAN_KE,TGL_PELAKSANAAN,TGL_KLAIM,TGL_BAYAR,JUMLAH_BAYAR,STATUS)
								 VALUES
								 (%s, %s, '%s', NOW(), NOW(), %s,0)"
								, $iddata
								, $_POST['ke']
								, str_replace("+"," ",$_POST['tgl'])
								, $_POST['nominal']
								);
			$idlog		=	$db->execSQL($sqlIns, 1);
			
		}
		
		$sqlupd		=	sprintf("UPDATE log_kursus A
								 LEFT JOIN t_pengajuan_jasa B ON A.IDPENGAJUANJASA = B.IDPENGAJUANJASA
								 SET A.STATUS = 1, A.TGL_BAYAR = NOW(), B.JUMLAH_PEMBAYARAN = A.PEMBAYARAN_KE
								 WHERE A.IDLOG = %s"
								 , $idlog
								);
		$affected	=	$db->execSQL($sqlupd, 0);
		
		if($affected > 0){
			
			$sqlDet		=	sprintf("SELECT E.NAMA, E.EMAIL, C.NAMA_PAKET, B.NAMA AS NAMA_MURID, X.JUMLAH_BAYAR, X.PEMBAYARAN_KE,
											X.IDPENGAJUANJASA, E.IDPENGAJAR, A.JUMLAH_PERTEMUAN, X.TGL_PELAKSANAAN
									 FROM log_kursus X
									 LEFT JOIN t_pengajuan_jasa A ON X.IDPENGAJUANJASA = A.IDPENGAJUANJASA
									 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
									 LEFT JOIN t_paket C ON A.IDPAKET = C.IDPAKET
									 LEFT JOIN t_mapel_pengajar D ON C.IDMAPELPENGAJAR = D.IDMAPELPENGAJAR
									 LEFT JOIN m_pengajar E ON D.IDPENGAJAR = E.IDPENGAJAR
									 WHERE X.IDLOG = %s
									 LIMIT 0,1"
									, $idlog
									);
			$resultDet	=	$db->query($sqlDet);
			$resultDet	=	$resultDet[0];
			$email		=	$resultDet['EMAIL'];
			$nama		=	$resultDet['NAMA'];
			$ketbalance	=	"Pembayaran biaya jasa kursus paket ".$resultDet['NAMA_PAKET']." pertemuan ke-".$resultDet['PEMBAYARAN_KE']." dari ".$resultDet['NAMA_MURID']." sejumlah Rp. ".number_format($resultDet['JUMLAH_BAYAR'],0,',','.').",-";
			
			$sqlInsBl	=	sprintf("INSERT INTO t_balance
									 (IDJENISTRANSAKSI, IDLOGKURSUS, JENIS_PEMILIK, IDUSER, TGL_TRANSAKSI, DEBET, KETERANGAN)
									 VALUES
									 (4, %s, 1, %s, NOW(), %s, '%s')"
									, $idlog
									, $resultDet['IDPENGAJAR']
									, $resultDet['JUMLAH_BAYAR']
									, $ketbalance
									);
			$db->execSQL($sqlInsBl, 0);
			
			$sqlupdBl	=	sprintf("UPDATE m_pengajar 
									 SET CURRENT_BALANCE = CURRENT_BALANCE + %s 
									 WHERE IDPENGAJAR = %s"
									 , $resultDet['JUMLAH_BAYAR']
									 , $resultDet['IDPENGAJAR']);
			$db->execSQL($sqlupdBl, 0);
			
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								
								<p>
								Data klaim dana jasa yang anda ajukan dengan data:<br/><br/>
								Paket : ".$resultDet['NAMA_PAKET']."<br/>
								Jumlah Pertemuan : ".$resultDet['JUMLAH_PERTEMUAN']."<br/>
								Pertemuan Ke- : ".$resultDet['PEMBAYARAN_KE']."<br/>
								Waktu Kursus : ".$resultDet['TGL_PELAKSANAAN']."<br/>
								Biaya pertemuan : Rp. ".number_format($resultDet['JUMLAH_BAYAR'],0,',','.').",-<br/><br/>
								sudah dikonfirmasi dan dana secara otomatis masuk ke dalam balance anda.
								Demikian pemberitahuan dari kami.
								</p><br/><br/>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Pemberitahuan Pembayaran Jasa mengajar", $message);
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data perubahan disimpan"));
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menyimpan perubahan, silakan coba lagi nanti"));
		}
		
		die();
		
	}
	
	//FUNGSI DIGUNAKAN UNTUK DETAIL DATA
	if( $enkripsi->decode($_GET['func']) == "detailData" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode($_POST['iddata']);
		$sqlDetail	=	sprintf("SELECT A.TGL_AWAL, A.RP_PERPERTEMUAN, B.MATERI, A.STATUS, D.FOTO, D.IDPENGAJAR, D.NAMA
								 FROM t_pengajuan_jasa A
								 LEFT JOIN t_paket B ON A.IDPAKET = B.IDPAKET
								 LEFT JOIN t_mapel_pengajar C ON B.IDMAPELPENGAJAR = C.IDMAPELPENGAJAR
								 LEFT JOIN m_pengajar D ON C.IDPENGAJAR = D.IDPENGAJAR
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
							case "0"	:	$ketstatuslog	=	"<b style='color: #E8A40C'>Menunggu Konfirmasi Anda</b>"; break;
							case "1"	:	$ketstatuslog	=	"<b style='color: green'>Tuntas</b>"; break;
							case "-1"	:	$ketstatuslog	=	"<b style='color: green'>Ditolak</b>"; break;
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
													<input type='button' value='Konfirmasi Kursus' class='btn btn-sm btn-custom2 pull-right' onclick='setKonfirm(\"".$enkripsi->encode($iddata)."\")'>
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
			
			$idp	=	$enkripsi->encode($resultDet['IDPENGAJAR']);
			$foto	=	$enkripsi->encode($resultDet['FOTO']);
			$dataD	=	"<div class='boxSquareWhite'>
							<div class='row'>
								<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
									<a href='".APP_URL."pengajar_profil.php?q=".$idp."' target='_blank'>
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
						".$listRiwayat."";
			
		} else {
			$dataD	=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
		}
		
		echo $dataD;
		die();
	}
	
	$sql		=	sprintf("SELECT B.NAMA_PAKET, D.NAMA, E.NAMA_MAPEL, A.JUMLAH_PERTEMUAN, A.TGL_KADALUARSA, A.RP_TOTAL, A.STATUS,
									A.IDPENGAJUANJASA
							 FROM t_pengajuan_jasa A
							 LEFT JOIN t_paket B ON A.IDPAKET = B.IDPAKET
							 LEFT JOIN t_mapel_pengajar C ON B.IDMAPELPENGAJAR = C.IDMAPELPENGAJAR
							 LEFT JOIN m_pengajar D ON C.IDPENGAJAR = D.IDPENGAJAR
							 LEFT JOIN m_mapel E ON C.IDMAPEL = E.IDMAPEL
							 WHERE A.IDMURID = %s
							 ORDER BY A.TGL_AWAL DESC"
							, $idmurid
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		foreach($result as $key){
			
			switch($key['STATUS']){
				case "0"	:	$ketstatus	=	"<b style='color: #E8A40C'>Menunggu</b>"; break;
				case "1"	:	$ketstatus	=	"<b style='color: green'>Aktif</b>"; break;
				case "2"	:	$ketstatus	=	"<b style='color: red'>Ditolak</b>"; break;
				case "3"	:	$ketstatus	=	"<b style='color: red'>Kadaluarsa</b>"; break;
				default		:	$ketstatus	=	"<b style='color: red'>-</b>"; break;
			}
			
			$data	.=	"<tr id='row".$enkripsi->encode($key['IDPENGAJUANJASA'])."' class='rowdata'>
							<td>".$key['NAMA_PAKET']." (".$ketstatus.")</td>
							<td>".$key['NAMA']."</td>
							<td>".$key['NAMA_MAPEL']."</td>
							<td align='right'>".$key['JUMLAH_PERTEMUAN']."</td>
							<td align='center'>".$key['TGL_KADALUARSA']."</td>
							<td align='right'>Rp. ".number_format($key['RP_TOTAL'], 0, ',', '.')."</td>
							<td align='center'>
								<input type='button' value='Detail' class='btn btn-sm btn-custom2' onclick='showDetail(\"".$enkripsi->encode($key['IDPENGAJUANJASA'])."\")'>
							</td>
						 </tr>";
		}
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='7'><center>Tidak ada data</center></td></tr>";
	}
	
	$sqlNews	=	sprintf("SELECT E.NAMA AS NAMA_PENGAJAR, C.NAMA_PAKET, D.IDPENGAJAR, B.IDPAKET, A.IDLOG, A.TGL_PELAKSANAAN
							 FROM log_kursus A
							 LEFT JOIN t_pengajuan_jasa B ON A.IDPENGAJUANJASA = B.IDPENGAJUANJASA
							 LEFT JOIN t_paket C ON B.IDPAKET = C.IDPAKET
							 LEFT JOIN t_mapel_pengajar D ON C.IDMAPELPENGAJAR = D.IDMAPELPENGAJAR
							 LEFT JOIN m_pengajar E ON D.IDPENGAJAR = E.IDPENGAJAR
							 WHERE B.IDMURID = %s AND A.STATUS = 0"
							, $idmurid
							);
	$resultnews	=	$db->query($sqlNews);
	
	if($resultnews <> '' && $resultnews <> false){
		$listNews	=	"<ul>";
		foreach($resultnews as $keynews){
			$idp		=	$enkripsi->encode($keynews['IDPENGAJAR']);
			$idpkt		=	$enkripsi->encode($keynews['IDPAKET']);
			$idlog		=	$enkripsi->encode($keynews['IDLOG']);
			$listNews	.=	"<li>
								Pengajar <a target='_blank' href='".APP_URL."pengajar_profil.php?q=".$idp."'>".$keynews['NAMA_PENGAJAR']."</a>
								meminta konfirmasi atas jasa kursus pada paket <a target='_blank' href='".APP_URL."detail_paket?q=".$idp."&idp=".$idpkt."'>".$keynews['NAMA_PAKET']."</a>
								yang sudah dilaksanakan pada ".$keynews['TGL_PELAKSANAAN']."<br/>
								<a href='#' class='btn btn-kursusles btn-sm' onclick='setApproval(\"".$idlog."\", false)'>
									<i class='fa fa-times-circle'></i> Tolak
								</a>
								<a href='#' class='btn btn-kursusles btn-sm' onclick='setApproval(\"".$idlog."\", true)'>
									<i class='fa fa-check-circle'></i> Konfirmasi
								</a>
							</li>";
		}
		$listNews	.=	"</ul>";
	} else {
		$listNews	=	"<b>Tidak ada pemberitahuan yang ditampilkan</b>";
	}
	
?>
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
<div class="boxSquareWhite">
    <h4>Pemberitahuan</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        	<?=$listNews?>
	    </div>
    </div>
</div>
<hr />
<div class="boxSquareWhite">
    <h4>Data Kursus Yang Saya Ikuti</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered" id="data_table">
            <thead>
                <tr>
                    <th class="text-center">Paket</th>
                    <th class="text-center">Pengajar</th>
                    <th class="text-center">Mata Pelajaran</th>
                    <th class="text-center">Jumlah<br/>Pertemuan</th>
                    <th class="text-center">Jumlah Biaya</th>
                    <th class="text-center">Tgl Akhir</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody id="bodyData">
            	<?=$data?>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
<script>
	function generateMsg(msg){
		return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
				"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
				"<strong><small id='message_response'>"+msg+"</small></strong>"+
			"</div>";
	}
	function showDetail(value){
		$('.rowDetail').slideUp('fast').remove();
		$('#bodyData').find('tr').removeClass('rowData-show');
		$("#row"+value).after("<tr class='rowDetail'><td colspan ='7' id='con-"+value+"'><center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>").addClass('rowData-show');
		
		$.post( "<?=APP_URL?>php/page/mkursus.php?func=<?=$enkripsi->encode('detailData')?>", {iddata : value})
		.done(function( data ) {
			$("#con-"+value).html(data);
		});
		
	}
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
					  $(this).html("Yakin konfirmasi jasa kursus yang dilakukan pengajar?");
					},
					close: function() {
						$(this).dialog( "close" );
					},
					buttons: {
						"Ya": function() {
							$(this).dialog( "close" );
							$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang menyimpan..'));
							$.post( "<?=APP_URL?>php/page/mkursus.php?func=<?=$enkripsi->encode('konfirmKlaim')?>", {id: id})
							.done(function( data ) {
								
								data			=	JSON.parse(data);
								if(data['respon_code'] != "00000"){
									$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
								} else {
									$('#message_response_container').slideUp('fast').html("");
									$('#mkursus').click();
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
					  $(this).html("Yakin menolak konfirmasi jasa kursus?<br/>Silakan isi alasan penolakan : <br/><textarea name='alasan' id='alasan' style='width:100%'></textarea>");
					},
					close: function() {
						$(this).dialog( "close" );
					},
					buttons: {
						"Ya": function() {
							var alasan	=	$('#alasan').val();
							$(this).dialog( "close" );
							$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang menyimpan..'));
							$.post( "<?=APP_URL?>php/page/mkursus.php?func=<?=$enkripsi->encode('ignoreKlaim')?>", {id: id, alasan:alasan})
							.done(function( data ) {
								
								data			=	JSON.parse(data);
								if(data['respon_code'] != "00000"){
									$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
								} else {
									$('#message_response_container').slideUp('fast').html("");
									$('#mkursus').click();
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
	function setKonfirm(id){
		$.post( "<?=APP_URL?>php/page/mkursus.php?func=<?=$enkripsi->encode('getFormKonfirm')?>", {id: id})
		.done(function( dataconf ) {
			dataconf			=	JSON.parse(dataconf);
			
			if(dataconf['respon_code'] == '00000'){
				$("#dialog-confirm").dialog({
					closeOnEscape: false,
					modal: true,
					width: "50%",
					title: "Konfirmasi Jasa Kursus",
					draggable: false,
					resizable: false,
					position: { my: "center", at: "top" },
					open: function() {
						
						$(this).html(dataconf['respon_msg']);
						$('#tgldata').datetimepicker({
							format:'Y-m-d H:i:s',
							lang:'id',
							closeOnDateSelect:true,
							maxDate:'+1970/01/01',
						});
						
					},
					close: function() {
						$(this).dialog( "close" );
					},
					buttons: {
						"Kirim Konfirmasi": function() {
							$(this).dialog( "close" );
							$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang mengirim..'));
							var tgl		=	$('#tgldata').val();
								ke		=	$('#ke').val();
								nominal	=	$('#nominal').val();
							$.post( "<?=APP_URL?>php/page/mkursus.php?func=<?=$enkripsi->encode('kirimKonfirm')?>", {id: id, tgl:tgl, ke:ke, nominal:nominal})
							.done(function( data ) {
								
								data			=	JSON.parse(data);
								if(data['respon_code'] != "00000"){
									$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
								} else {
									$('#message_response_container').slideUp('fast').html('');
									$('#mkursus').click();
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
					width: "50%",
					title: "Pemberitahuan",
					draggable: false,
					resizable: false,
					position: { my: "center", at: "top" },
					open: function() {
						$(this).html(dataconf['respon_msg']);
					},
					close: function() {
						$(this).dialog( "close" );
					},
					buttons: {
						"Ok": function() {
							$(this).dialog( "close" );
						}
					}
				});
			}
		});
	}
</script>