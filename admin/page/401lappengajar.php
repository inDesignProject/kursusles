<?php
	include('../php/include/enkripsi.php');
	include('../php/include/session.php');
	include('../php/lib/db_connection.php');
	require "../php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	if($session->cekSession() <> 2 && !isset($_GET['func'])){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode($session->cekSession())."'</script>";
		 die();
	}
	
	//FUNGSI DIGUNAKAN DETAIL ACTION
	if( $enkripsi->decode($_GET['func']) == "getDetail" && isset($_GET['func'])){
		
		$idreport		=	$enkripsi->decode($_POST['idreport']);
		$iduser			=	$enkripsi->decode($_POST['iduser']);

		if($_POST['typeuser'] == '1'){
			$table	=	"m_pengajar";
			$field	=	"IDPENGAJAR";
			$judul	=	"Pengajar";
		} else {
			$table	=	"m_murid";
			$field	=	"IDMURID";
			$judul	=	"Murid";
		}

		if($_POST['type'] == 'blokir'){
			$sqlseldet	=	sprintf("SELECT B.NAMA AS NAMAUSER, C.USERNAME AS USERADMIN, A.TGL_BLOKIR
									 FROM log_blokir A
									 LEFT JOIN %s B ON A.IDUSER = B.%s
									 LEFT JOIN admin_user C ON A.IDUSERBLOKIR = C.IDUSER
									 WHERE A.IDREPORT = %s AND A.TYPE = %s
									 LIMIT 0,1"
									, $table
									, $field
									, $idreport
									, $_POST['typeuser']
									, $iduser
									);
			$resseldet	=	$db->query($sqlseldet);
			
			if($resseldet <> '' && $resseldet <> false){
				$resseldet	=	$resseldet[0];
				$return 	=	"<table>
									<tr><td>".$judul." diblokir </td><td> : </td><td> ".$resseldet['NAMAUSER']." </td></tr>
									<tr><td>Admin pemblokir </td><td> : </td><td> ".$resseldet['USERADMIN']." </td></tr>
									<tr><td>Tanggal posting </td><td> : </td><td> ".$resseldet['TGL_BLOKIR']." </td></tr>
								 </table>";
			} else {
				$return 	=	"Tidak ada data yang ditemukan";
			}
			
		} else {
			
			$sqlseldet	=	sprintf("SELECT B.NAMA AS NAMAUSER, C.USERNAME AS USERADMIN, A.TGL_BLOKIR, A.SUBYEK, A.PESAN
									 FROM log_pesan_peringatan A
									 LEFT JOIN %s B ON A.IDUSER = B.%s
									 LEFT JOIN admin_user C ON A.IDUSERPOSTING = C.IDUSER
									 WHERE A.IDREPORT = %s AND A.TYPE = %s
									 LIMIT 0,1"
									, $table
									, $field
									, $idreport
									, $_POST['typeuser']
									, $iduser
									);
			$resseldet	=	$db->query($sqlseldet);
			
			if($resseldet <> '' && $resseldet <> false){
				$resseldet	=	$resseldet[0];
				$return 	=	"<table>
									<tr><td>Penerima</td><td> : </td><td> ".$resseldet['NAMAUSER']." </td></tr>
									<tr><td>Pengirim </td><td> : </td><td> ".$resseldet['USERADMIN']." </td></tr>
									<tr><td>Subyek </td><td> : </td><td> ".$resseldet['SUBYEK']." </td></tr>
									<tr><td>Isi </td><td> : </td><td> ".$resseldet['PESAN']." </td></tr>
									<tr><td>Tanggal </td><td> : </td><td> ".$resseldet['TGL_BLOKIR']." </td></tr>
								 </table>";
			} else {
				$return 	=	"Tidak ada data yang ditemukan";
			}
			
		}
		
		echo $return;
		die();
		
	}
	
	//FUNGSI DIGUNAKAN SEND MSG
	if( $enkripsi->decode($_GET['func']) == "sendMsg" && isset($_GET['func'])){
		
		if($_POST['subyek'] == '' || $_POST['pesan'] == ''){
			echo -1;
			die();
		}
		
		$idpenerima	=	$enkripsi->decode($_POST['idpenerima']);
		$idreport	=	$enkripsi->decode($_POST['idreport']);
		$type		=	$_POST['typepenerima'];
		
		$sqlUpd		=	sprintf("UPDATE t_report_pengajar SET TINDAKAN_PESAN = %s WHERE IDREPORT = %s", $_POST['updactpesan'], $idreport);
		$db->execSQL($sqlUpd, 1);
		
		$sqlInslog	=	sprintf("INSERT INTO log_pesan_peringatan 
								 (IDREPORT,TYPE,IDUSER,IDUSERPOSTING,SUBYEK,PESAN,TGL_BLOKIR)
								 VALUES
								 ('%s','%s','%s','%s','%s','%s',NOW())"
								, $idreport
								, $type
								, $idpenerima
								, $_SESSION['KursusLesAdmin']['IDUSER']
								, $_POST['subyek']
								, $_POST['pesan']
								);
		$db->execSQL($sqlInslog, 0);
		
		$sqlIns		=	sprintf("INSERT INTO t_pesan_pribadi 
								 (IDUSERTUJUAN,IDUSERPENGIRIM,SUBYEK,PESAN,TGL_PESAN,TYPETUJUAN,TYPEPENGIRIM)
								 VALUES
								 ('%s','%s','%s','%s',NOW(),'%s','%s')"
								, $idpenerima
								, '0'
								, $_POST['subyek']
								, $_POST['pesan']
								, $type
								, '0'
								);
		$affected	=	$db->execSQL($sqlIns, 0);
		
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo "1";
		} else {
			echo "0";
		}
		
		die();
	}
	
	//FUNGSI DIGUNAKAN COMPOSER MSG
	if( $enkripsi->decode($_GET['func']) == "getComposer" && isset($_GET['func'])){
		
		$iddata			=	$enkripsi->decode($_POST['iddata']);
		
		switch($_POST['type']){
			case "0"	:	$setval	=	$_POST['type']; break;
			default		:	$setval	=	'3'; break;
		}
		
		$type_judul		=	$_POST['type'] == 1 ? "Pengajar" : "Murid";
		$field_sel		=	$_POST['type'] == 1 ? "IDPENGAJAR" : "IDMURID";
		
		$sqlSel			=	sprintf("SELECT %s, TINDAKAN_PESAN FROM t_report_pengajar WHERE IDREPORT = %s LIMIT 0,1", $field_sel, $iddata);
		$resultsel		=	$db->query($sqlSel);
		$resultsel		=	$resultsel[0];
		$idpenerima		=	$enkripsi->encode($resultsel[$field_sel]);
		
		switch($resultsel['TINDAKAN_PESAN']){
			case "0"	:	$actupd	=	$_POST['type']; break;
			default		:	$actupd	=	'3'; break;
		}
		
		echo "	<div style='padding: 8px' class='composeMsg' id='compose".$_POST['iddata']."'>
					  <div class='boxSquareWhite'>
						<h4>Kirim Pesan ke ".$type_judul." <span style='float: right; font-size: 14px; cursor: pointer;' onclick='$(\"#compose".$_POST['iddata']."\").remove()'>X [Tutup]</span></h4><br/><br/>
						<form id='composeForm' method='POST' action='#'>
						  <div class='row'>
							<input id='subyek' name='subyek' class='form-control' placeholder='Masukkan subyek' value='' type='text'><br/><br/>
							<textarea id='pesan' name='pesan' maxlength='2000' class='form-control' placeholder='Isi Pesan' required=''></textarea><br/><br/>
							<input name='idpenerima' id='idpenerima' value='".$idpenerima."' type='hidden'>
							<input name='typepenerima' id='typepenerima' value='".$_POST['type']."' type='hidden'>
							<input name='idreport' id='idreport' value='".$_POST['iddata']."' type='hidden'>
							<input name='updactpesan' id='updactpesan' value='".$actupd."' type='hidden'>
							<input name='kirim' id='kirim' value='Kirim' class='btn btn-sm btn-custom2' onclick='sendMsg()' type='button'>
						  </div>
						</form>
					  </div>
					</div>";		
		die();
	}
	
	//FUNGSI DIGUNAKAN BLOCK ID
	if( $enkripsi->decode($_GET['func']) == "blockID" && isset($_GET['func'])){
		
		if($_POST['type'] == 'p'){
			$typeuser	=	1;
			$table		=	"m_pengajar";
			$field		=	"IDPENGAJAR";
			$note		=	"* sisa balance yang ada di akun anda akan ditransferkan ke rekening bank sesuai data rekening yang anda miliki<br/><br/>";
		} else {
			$typeuser	=	2;
			$table		=	"m_murid";
			$field		=	"IDMURID";
			$note		=	"* sisa balance yang ada di akun anda akan ditransferkan ke rekening bank sesuai data rekening yang anda miliki<br/><br/>";
		}
		
		$iddata			=	$enkripsi->decode($_POST['iddata']);
		$sqlSelD		=	sprintf("SELECT B.EMAIL, B.NAMA, A.TINDAKAN_BLOKIR, B.%s
									 FROM t_report_pengajar A
									 LEFT JOIN %s B ON A.%s = B.%s
									 WHERE A.IDREPORT = %s
									 LIMIT 0,1"
									 , $field
									 , $table
									 , $field
									 , $field
									 , $iddata
									);
		$resSelD		=	$db->query($sqlSelD);

		if($resSelD <> '' && $resSelD <> false){
			
			$resSelD	=	$resSelD[0];
			$nama		=	$resSelD['NAMA'];
			$email		=	$resSelD['EMAIL'];

			switch($resSelD['TINDAKAN_BLOKIR']){
				case "0"	:	$tindakan_blokir = $typeuser; break;
				default		:	$tindakan_blokir = 3; break;
			}
			
			$sqlInslog	=	sprintf("INSERT INTO log_blokir
									 (IDREPORT,TYPE,IDUSER,IDUSERBLOKIR,TGL_BLOKIR)
									 VALUES
									 ('%s','%s','%s','%s',NOW())"
									, $iddata
									, $typeuser
									, $resSelD[$field]
									, $_SESSION['KursusLesAdmin']['IDUSER']
									);
			$db->execSQL($sqlInslog, 0);
			
			$sqlUpd		=	sprintf("UPDATE t_report_pengajar A
									 LEFT JOIN m_user B ON A.%s = B.IDUSER_CHILD
									 SET A.TINDAKAN_BLOKIR = %s, B.STATUS = 2, B.SESSION_ID = NULL
									 WHERE A.IDREPORT = %s AND B.IDLEVEL = %s"
									, $field
									, $tindakan_blokir
									, $iddata
									, $typeuser
									);
			$affected	=	$db->execSQL($sqlUpd, 0);
	
			//JIKA DATA BERUBAH
			if($affected > 0){
				$message	=  "<html>
								<head>
								</head>
								
								<body>
									
									<p>
									Halo ".$nama.",<br/><br/>
									kami menerima laporan dari user yang menganggap bahwa user anda sedikit mengganggu. Dan setelah<br/>
									kami cek kembali tentang laporan tersebut, maka kami memutuskan untuk memblokir akun anda demi<br/>
									kenyamanan setiap user yang menggunakan KursusLes.<br/><br />
									
									Namun, jika anda merasa tidak melakukan pelanggaran atas segala sesuatu yang dilaporkan pengguna lain<br/>
									anda dapat menghubungi kami untuk mengklarifikasikan kembali laporan tersebut dengan cara mengirim<br />
									email ke <b>kursusles@gmail.com</b> atau hubungi hotline kami di <b>081217009689</b><br/><br/>
									
									".$note."
									
									Best regards,<br /><br />
									Admin KursusLes.com
									</p>
								
								</body>
								</html>";
				$session->sendEmail($email, $nama, "Pemberitahuan Pemblokiran Akun KursusLes.com", $message);
				echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Pemblokiran berhasil"));
				die();
			} else {
				echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Gagal memblokir / tidak ada perubahan data. Silakan coba lagi nanti"));
				die();
			}
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Data yang anda pilih tidak valid"));
			die();
		}
			
	}
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA
		$sql		=	sprintf("SELECT A.IDREPORT, A.IDPENGAJAR, LEFT(C.NAMA,20) AS NAMA_PENGAJAR, C.FOTO AS FOTO_PENGAJAR,
										A.IDMURID, LEFT(B.NAMA, 20) AS NAMA_MURID, B.FOTO AS FOTO_MURID,
										D.NAMA_MASALAH, A.ALASAN, A.TGL_REPORT, A.IDSOLUSI, A.TINDAKAN_PESAN,
										A.TINDAKAN_BLOKIR
								 FROM t_report_pengajar A
								 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
								 LEFT JOIN m_pengajar C ON A.IDPENGAJAR = C.IDPENGAJAR
								 LEFT JOIN m_report_masalah D ON A.IDMASALAH = D.IDMASALAH
								 WHERE A.TGL_REPORT BETWEEN '%s' AND '%s'
								 ORDER BY A.TGL_REPORT DESC"
								, $_POST['tglfrom']
								, $_POST['tglto']
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDREPORT) AS TOTDATA FROM (%s) AS A", $sql);
		$resultC	=	$db->query($sqlCount);
		$resultC	=	$resultC[0];
		$totData	=	$resultC['TOTDATA'];
		$totpage	=	ceil($totData / $dataperpage);

		$sqlSel		=	sprintf("SELECT * FROM (%s) AS A 
								 LIMIT %s, %s"
								, $sql
								, $startLimit
								, $dataperpage
								);
		$result		=	$db->query($sqlSel);
		$data		=	'';
		
		if($result <> '' && $result <> false){
			
			$i			=	1;
			$startData	=	$startLimit + 1;
			if(($startLimit + $dataperpage) > $totData){
				$endData	=	$totData;
			} else {
				$endData	=	$startLimit + $dataperpage;
			}
			
			if($totpage == 1){
				$pagination	=	"<li class='active'><a href='#'>1</a></li>";
			} else {
				
				if($_POST['page'] <> 1 && $totpage > 1){
					$prevPage	=	($_POST['page'] *1) - 1;
					$pagination	.=	"	<li class='previous' onClick='filterData(".$prevPage.")'><a href='#'>&laquo;</a></li>";
				}
				
				for($i=1; $i<=$totpage; $i++){
					if($i == $_POST['page']){
						$pagination	.=	"	<li><a href='#' class='current'> ".$i."</a></li>";
					} else {
						$pagination	.=	"	<li onClick='filterData(".$i.")'><a href='#'>".$i."</a></li>";
					}
				}

				if($_POST['page'] <> $totpage){
					$nextPage	=	($_POST['page'] *1) + 1;
					$pagination	.=	"	<li onClick='filterData(".$nextPage.")'><a href='#'>&raquo;</a></li>";
				}
				
			}
			
			foreach($result as $key){
				
				$sqlTot	=	sprintf("SELECT COUNT(DISTINCT(IDMURID)) AS JML_MURID, COUNT(IDREPORT) AS JML_LAPORAN
									 FROM t_report_pengajar
									 WHERE IDPENGAJAR= %s"
									 , $key['IDPENGAJAR']
									);
				$rsTot	=	$db->query($sqlTot);
				$rsTot	=	$rsTot[0];

				$sqlSOL	=	sprintf("SELECT NAMA_SOLUSI FROM m_report_solusi WHERE IDSOLUSI IN (%s)", $key['IDSOLUSI']);
				$rsSOL	=	$db->query($sqlSOL);
				$solusi	=	'';
				
				foreach($rsSOL as $keySOL){
					$solusi	.=	"<li>".$keySOL['NAMA_SOLUSI']."</li>";
				}
				
				$data	.=	"	<div style='padding: 8px' id='condata".$enkripsi->encode($key['IDREPORT'])."'>
									<div class='boxSquareWhite'>
										<ul class='profile-con'>
											<li class='li-profile' style='margin-bottom: 10px'>
												<img src='".KURSUSLES_IMG_URL."generate_pic.php?type=pr&q=".$enkripsi->encode($key['FOTO_PENGAJAR'])."&w=48&h=48' class='img-circle'/>
												<a target='_blank' href='".KURSUSLES_URL."pengajar_profil?q=".$enkripsi->encode($key['IDPENGAJAR'])."'>".$key['NAMA_PENGAJAR']."</a>
												<small>Pengajar yang dilaporkan</small>
											</li>
											<li class='li-profile'>
												<img src='".KURSUSLES_IMG_URL."generate_pic.php?type=pr&q=".$enkripsi->encode($key['FOTO_MURID'])."&w=48&h=48' class='img-circle'/>
												<a target='_blank' href='".KURSUSLES_URL."index_murid?q=".$enkripsi->encode($key['IDMURID'])."'>".$key['NAMA_MURID']."</a>
												<small>Murid pelapor</small>
											</li>
											<li class='li-profile'>
												<small>
													<i class='fa fa-share'></i> ".$rsTot['JML_LAPORAN']." Kali Dilaporkan<br/>
													<i class='fa fa-user'></i> ".$rsTot['JML_MURID']." Pelapor
												</small>
											</li>
										</ul>
										<div style='margin: -115px 0px 0px 270px; width: 30%'>
											<h4>Permasalahan yang dilaporkan</h4>
											<p>
												".$key['NAMA_MASALAH']."
											</p><br/>
											<h4>Solusi yang diminta pelapor</h4>
											<ul>
												".$solusi."
											</ul><br/>
											<small>Dilaporkan pada <i class='fa fa-calendar'></i> ".date('d-m-Y', strtotime($key['TGL_REPORT']))." <i class='fa fa-clock-o'></i> ".date('H:i:s', strtotime($key['TGL_REPORT']))."</small>
										</div>
										<div style='margin: -186px 0px 0px; float: right'>
											<h4>Tindakan</h4>";
											
				if($key['TINDAKAN_PESAN'] <> 0){
					switch($key['TINDAKAN_PESAN']){
						case "1" :	$data			.=	"<i class='fa fa-check'></i> Kirim pesan ke pengajar [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"pesan\", 1, \"".$enkripsi->encode($key['IDPENGAJAR'])."\")'><b>Detail</b></a>]<br/>";
									$action_pesan	=	"	<select name='pesan'".$enkripsi->encode($key['IDREPORT'])."' id='pesan".$enkripsi->encode($key['IDREPORT'])."' onchange= 'openMsgComposer(this.id,this.value)'>
																<option value=''>Kirim Pesan?</option>
																<option value='2'>Kirim Pesan ke Murid</option>
															</select><br/><br/>";
									break;
						case "2" :	$data			.=	"<i class='fa fa-check'></i> Kirim pesan ke murid [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"pesan\", 2, \"".$enkripsi->encode($key['IDMURID'])."\")'><b>Detail</b></a>]<br/>";
									$action_pesan	=	"	<select name='pesan'".$enkripsi->encode($key['IDREPORT'])."' id='pesan".$enkripsi->encode($key['IDREPORT'])."' onchange= 'openMsgComposer(this.id,this.value)'>
																<option value=''>Kirim Pesan?</option>
																<option value='1'>Kirim Pesan ke Pengajar</option>
															</select><br/><br/>";
									break;
						case "3" :	$data			.=	"<i class='fa fa-check'></i> Kirim pesan ke pengajar [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"pesan\", 1, \"".$enkripsi->encode($key['IDPENGAJAR'])."\")'><b>Detail</b></a>]<br/>
												 		 <i class='fa fa-check'></i> Kirim pesan ke murid [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"pesan\", 2, \"".$enkripsi->encode($key['IDMURID'])."\")'><b>Detail</b></a>]<br/>";
									$action_pesan	=	"";
									break;
						default	 :	$data			.=	"";
									$action_pesan	=	"	<select name='pesan'".$enkripsi->encode($key['IDREPORT'])."' id='pesan".$enkripsi->encode($key['IDREPORT'])."' onchange= 'openMsgComposer(this.id,this.value)'>
																<option value=''>Kirim Pesan?</option>
																<option value='1'>Kirim Pesan ke Pengajar</option>
																<option value='2'>Kirim Pesan ke Murid</option>
															</select><br/><br/>";
									break;
					}
				} else {
					$action_pesan	=	"	<select name='pesan'".$enkripsi->encode($key['IDREPORT'])."' id='pesan".$enkripsi->encode($key['IDREPORT'])."' onchange= 'openMsgComposer(this.id,this.value)'>
												<option value=''>Kirim Pesan?</option>
												<option value='1'>Kirim Pesan ke Pengajar</option>
												<option value='2'>Kirim Pesan ke Murid</option>
											</select><br/><br/>";
				}
				
				if($key['TINDAKAN_BLOKIR'] <> 0){
					switch($key['TINDAKAN_BLOKIR']){
						case "1" :	$data			.=	"<i class='fa fa-check'></i> Blokir pengajar [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"blokir\", 1, \"".$enkripsi->encode($key['IDPENGAJAR'])."\")'><b>Detail</b></a>]<br/>";
									$block_button	=	"<input name='blokirm".$enkripsi->encode($key['IDREPORT'])."' id='blokirm".$enkripsi->encode($key['IDREPORT'])."' onclick='blockID(this.id)' value='Blokir Murid' type='button'>";
									break;
						case "2" :	$data			.=	"<i class='fa fa-check'></i> Blokir murid [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"blokir\", 2, \"".$enkripsi->encode($key['IDMURID'])."\")'><b>Detail</b></a>]<br/>";
									$block_button	=	"<input name='blokirp".$enkripsi->encode($key['IDREPORT'])."' id='blokirp".$enkripsi->encode($key['IDREPORT'])."' onclick='blockID(this.id)' value='Blokir Pengajar' type='button'>";
									break;
						case "3" :	$data			.=	"<i class='fa fa-check'></i> Blokir pengajar [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"blokir\", 1, \"".$enkripsi->encode($key['IDPENGAJAR'])."\")'><b>Detail</b></a>]<br/>
												 		 <i class='fa fa-check'></i> Blokir murid [<a href='#' onclick='openDetail(\"".$enkripsi->encode($key['IDREPORT'])."\", \"blokir\", 2, \"".$enkripsi->encode($key['IDMURID'])."\")'><b>Detail</b></a>]<br/>";
									$block_button	=	"";
									break;
						default	 :	$data			.=	"";
									$block_button	=	"";
									break;
					}
				} else {
					$block_button	=	"	<input name='blokirp".$enkripsi->encode($key['IDREPORT'])."' id='blokirp".$enkripsi->encode($key['IDREPORT'])."' onclick='blockID(this.id)' value='Blokir Pengajar' type='button'>
											<input name='blokirm".$enkripsi->encode($key['IDREPORT'])."' id='blokirm".$enkripsi->encode($key['IDREPORT'])."' onclick='blockID(this.id)' value='Blokir Murid' type='button'>";	
				}
				
				$data	.=	"				<br/>
											".$action_pesan."
											".$block_button."
										</div>
									</div>
								</div>";
			}
			
		} else {

			$data		=	"	<div style='padding: 8px'>
									<div class='boxSquareWhite'>
										<center><b>Tidak ada data yang ditampilkan</b></center>
									</div>
								</div>";
			$startData	=	0;
			$endData	=	0;

		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data, "startData"=>$startData, "endData"=>$endData, "pagination"=>$pagination));
		die();
	}
	
?>
<style>
.profile-con{list-style:none;}
.li-profile a{
	position: absolute;
	margin: 6px;
	font-weight: bold;
	font-size: 14px;
}
.li-profile small{
	position: absolute;
	margin-top: 23px;
	margin-left: 5px;
}
</style>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-desktop"></i> Monitoring</a></li>
        <li>Laporan Pengajar</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Monitoring Pengajar yang dilaporkan</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Tanggal Laporan</label>
                <span class="field">
                	<input type="text" name="tglfrom" id="tglfrom" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-01')?>" style="width:75px; text-align: center;" />
                     s.d 
                	<input type="text" name="tglto" id="tglto" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-d')?>" style="width:75px; text-align: center;" />
                </span>
            </p>
        </div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="filter" id="filter" class="submit radius2 pull-right" value="Saring" type="button" onclick="filterData(1)">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            <div style="float:right">
                <ul class="pagination" id="pagination" >
                </ul>
            </div><br/><br/>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
            	
            </div>
        </div>
    </div>
</div>
<script>
	$('#tglfrom, #tglto').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		lang:'id',
		closeOnDateSelect:true
	});
	function openMsgComposer(id,value){
		if(value != ''){
			var iddata	=	id.slice(5);
	
			$('#message_response_container').slideDown('fast').html(generateMsg("Sedang Memuat"));
			$.post( "<?=APP_URL?>page/401lappengajar.php?func=<?=$enkripsi->encode('getComposer')?>", {iddata: iddata, type: value})
			.done(function( data ) {
				$('.composeMsg').remove();
				$('#condata'+iddata).after(data);
				$('#message_response_container').slideUp('fast').html("");
				$('#subyek').focus();
			});
		} else {
			$('.composeMsg').remove();
			return true;
		}
	}
	function sendMsg(){
		
		var data	=	$('#composeForm').serialize();
		$('#message_response_container').slideDown('fast').html(generateMsg("Sedang Mengirim"));
		$.post( "<?=APP_URL?>page/401lappengajar.php?func=<?=$enkripsi->encode('sendMsg')?>", data)
		.done(function( data ) {
			
			if(data == '-1'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Harap isi subyek dan isi pesan"));
			} else if(data == '0'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Gagal mengirim pesan"));
			} else {
				$('.composeMsg').remove();
				$('#message_response_container').slideDown('fast').html(generateMsg("Pesan terkirim"));
				filterData(1);
			}
		});
		return true;
	}
	function filterData(page){
		var tglfrom	=	$('#tglfrom').val();
			tglto	=	$('#tglto').val();

		$('#data-con').html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
		$.post( "<?=APP_URL?>page/401lappengajar.php?func=<?=$enkripsi->encode('filterData')?>", {tglfrom: tglfrom, tglto: tglto, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#data-con').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
		return true;
	}
	function blockID(id){
		var type	=	id.substr(6, 1);
			id		=	id.slice(7);
		$("#dialog-confirm").dialog({
			closeOnEscape: false,
			resizable: true,
			modal: true,
			minWidth: 500,
			title: "Konfirmasi",
			open: function() {
			  $(this).html("Yakin blokir ID user?<br/>Sistem akan mengirimkan email kepada user sebagai pemberitahuan pemblokiran");
			},
			position: {
				my: 'top', 
				at: 'top'
			},
			close: function() {
				$(this).dialog( "close" );
			},
			buttons: {
				"Ya": function() {
						$.post("<?=APP_URL?>page/401lappengajar.php?func=<?=$enkripsi->encode('blockID')?>", {iddata: id, type:type})
						.done(function( data ) {
							data			=	JSON.parse(data);
							$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
						});
						$(this).dialog("close");
						filterData(1);
					},
				"Tidak": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	function openDetail(idreport, type, typeuser, iduser){
		
		var msg		=	'';
		$.post( "<?=APP_URL?>page/401lappengajar.php?func=<?=$enkripsi->encode('getDetail')?>", {idreport: idreport, type: type, typeuser: typeuser, iduser:iduser})
		.done(function( data ) {
			$("#dialog-confirm").dialog({
				closeOnEscape: false,
				resizable: true,
				modal: true,
				minWidth: 500,
				title: "Info",
				open: function() {
				  $(this).html(data);
				},
				position: {
					my: 'top', 
					at: 'top'
				},
				close: function() {
					$(this).dialog( "close" );
				},
				buttons: {
					"Tutup": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		});
	}
	$(document).ready(function(){
		filterData(1);
	});

</script>