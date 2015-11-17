<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
		
	//FUNGSI DIGUNAKAN UNTUK VALIDASI PENAMBAHAN / PENGURANGAN LEVEL / MAPEL
	if( $enkripsi->decode($_GET['func']) == "cekDataMapel" && isset($_GET['func'])){

		//AMBIL DATA NAMA MATA PELAJARAN
		$idmapel	=	$enkripsi->decode($_POST['listMapel']);
		$sql		=	sprintf("SELECT A.NAMA_MAPEL, B.IDMAPELPENGAJAR, B.STATUS, C.NAMA_JENJANG FROM m_mapel A
								 LEFT JOIN (SELECT * FROM t_mapel_pengajar WHERE IDPENGAJAR = %s) B ON A.IDMAPEL = B.IDMAPEL
								 LEFT JOIN m_jenjang C ON A.IDJENJANG = C.IDJENJANG
								 WHERE A.IDMAPEL = %s
								 LIMIT 0,1"
								, $_SESSION['KursusLes']['IDUSER']
								, $idmapel
								);
		$result		=	$db->query($sql);
		$result		=	$result[0];
		$respon		=	array();
		
		//JIKA DATA MAPEL DITEMUKAN
		if(isset($result) && $result <> false) {
			
			//JIKA MAPEL SUDAH DIMILIKI PENGAJAR, CEK STATUSNYA
			if($result['IDMAPELPENGAJAR'] <> '' && $result['IDMAPELPENGAJAR'] <> 'NULL'){
				
				//JIKA STATUS DATA MAPEL MASIH AKTIF, MAKA KETERANGAN KURANGI
				if($result['STATUS'] == 1){
					$respon	=	array("respon_code" => "2",
									  "nama_mapel" => $result['NAMA_MAPEL'],
									  "level" => $result['NAMA_JENJANG']
									  );
				//JIKA STATUS DATA MAPEL TIDAK AKTIF, MAKA KETERANGAN TAMBAHKAN
				} else if($result['STATUS'] == 0) {
					$respon	=	array("respon_code" => "1",
									  "nama_mapel" => $result['NAMA_MAPEL'],
									  "level" => $result['NAMA_JENJANG']
									  );
				}
			
			//JIKA BELUM PERNAH MENAMBAHKAN DATA, MAKA KETERANGAN TAMBAHKAN
			} else {
				$respon	=	array("respon_code" => "1",
								  "nama_mapel" => $result['NAMA_MAPEL'],
								  "level" => $result['NAMA_JENJANG']
								  );
			}
			
		//JIKA DATA MAPEL TIDAK	DITEMUKAN
		} else {
			$respon	=	array("respon_code" => "0",
							  "nama_mapel" => "",
							  "level" => ""
							  );
		}
		
		echo json_encode($respon);
		die();
		
	}
	
	//FUNGSI DIGUNAKAN UNTUK MENAMBAHKAN DATA MAPEL PADA PENGAJAR
	if( $enkripsi->decode($_GET['func']) == "tambahDataMapel" && isset($_GET['func'])){
		
		//AMBIL DATA NAMA MATA PELAJARAN
		$idmapel	=	$enkripsi->decode($_POST['listMapel']);
		
		//SQL INSERT/UPDATE DATA MAPEL
		$sqlUpdate	=	sprintf("INSERT INTO t_mapel_pengajar
								 SET IDPENGAJAR	=	%s,
								 	 IDMAPEL	=	%s,
									 STATUS		=	1
								 ON DUPLICATE KEY UPDATE
								 	 STATUS		=	1"
								, $_SESSION['KursusLes']['IDUSER']
								, $idmapel
								);
		$affected	=	$db->execSQL($sqlUpdate, 0);
		
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			
			//UPDATE STATUS PAKET PENGAJAR
			$sqlUpdateP	=	sprintf("UPDATE t_paket A
									 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
									 SET A.STATUS = 1
									 WHERE B.IDPENGAJAR = %s AND B.IDMAPEL = %s"
									, $_SESSION['KursusLes']['IDUSER']
									, $idmapel
									);
			$db->execSQL($sqlUpdateP, 0);
			//HABIS -- UPDATE STATUS PAKET PENGAJAR

			echo json_encode(array("respon_code"=>"success"));
		//JIKA GAGAL
		} else {
			echo json_encode(array("respon_code"=>"error"));
		}
		
		die();
		
	}
	
	//FUNGSI DIGUNAKAN UNTUK MENGURANGI DATA MAPEL PADA PENGAJAR
	if( $enkripsi->decode($_GET['func']) == "kurangDataMapel" && isset($_GET['func'])){
		
		//AMBIL DATA NAMA MATA PELAJARAN
		$idmapel	=	$enkripsi->decode($_POST['listMapel']);
		
		//SQL UPDATE STATUS NON AKTIF DATA MAPEL
		$sqlUpdate	=	sprintf("UPDATE t_mapel_pengajar
								 SET STATUS	= 0
								 WHERE IDPENGAJAR = %s AND IDMAPEL = %s"
								, $_SESSION['KursusLes']['IDUSER']
								, $idmapel
								);
		$affected	=	$db->execSQL($sqlUpdate, 0);
		
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			//UPDATE STATUS PAKET PENGAJAR
			$sqlUpdateP	=	sprintf("UPDATE t_paket A
									 LEFT JOIN t_mapel_pengajar B ON A.IDMAPELPENGAJAR = B.IDMAPELPENGAJAR
									 SET A.STATUS = 0
									 WHERE B.IDPENGAJAR = %s AND B.IDMAPEL = %s"
									, $_SESSION['KursusLes']['IDUSER']
									, $idmapel
									);
			$db->execSQL($sqlUpdateP, 0);
			//HABIS -- UPDATE STATUS PAKET PENGAJAR
			echo json_encode(array("respon_code"=>"success"));
		//JIKA GAGAL
		} else {
			echo json_encode(array("respon_code"=>"error"));
		}
		
		die();
		
	}
	
	//FUNGSI DIGUNAKAN UNTUK MENAMBAH DATA PAKET
	if( $enkripsi->decode($_GET['func']) == "simpanDataPaket" && isset($_GET['func'])){
		
		$valid		=	true;
		$jns_paket	=	"";
		foreach($_POST as $key => $value){
			if($value == ""){
				$respon_code	=	$key;
				switch(substr($key,0,9)){
					case "namapaket"	:	$respon_msg	=	"Harap isi <b>Nama Paket</b>"; break;
					case "jumlah"		:	$respon_msg	=	"Harap isi <b>Jumlah Peserta</b>"; break;
					case "tempat"		:	$respon_msg	=	"Harap tentukan <b>Tempat Kegiatan Kursus</b>"; break;
					case "materi"		:	$respon_msg	=	"Harap isi <b>Materi apa saja yang akan disampaikan</b>"; break;
					case "jns_paket"	:	$respon_msg	=	"Harap pilih <b>Jenis Paket</b>"; break;
					default				:	$respon_msg	=	"Harap Lengkapi Data Isian Anda"; break;
				}
				$valid	=	false;
				break;
			}
			
			$jns_paket	=	substr($key,0,9) == "jns_paket" ? $key : $jns_paket;
		}
		
		if(!isset($_POST[$jns_paket]) || $_POST[$jns_paket] == ""){
			$respon_code=	"gagal";
			$respon_msg	=	"Harap pilih <b>Jenis Paket</b>";
			$valid		=	false;
		}
		
		if(!is_numeric($_POST['jumlah'] * 1) || ($_POST['jumlah'] * 1) <= 0){
			$respon_code=	"gagal";
			$respon_msg	=	"<b>Jumlah Peserta</b> tidak valid";
			$valid		=	false;
		}
		
		if(!is_numeric($_POST['harga'] * 1) || ($_POST['harga'] * 1) <= 0){
			$respon_code=	"gagal";
			$respon_msg	=	"<b>Harga Paket</b> tidak valid";
			$valid		=	false;
		}
		
		if($valid == true){
			$sqlIns		=	sprintf("INSERT INTO t_paket
									 SET IDMAPELPENGAJAR=	'%s',
									 	 NAMA_PAKET		=	'%s',
									 	 JENIS			=	'%s',
										 JUMLAH_MURID	=	'%s',
										 TEMPAT			=	'%s',
										 MATERI			=	'%s',
										 WAKTU			=	'%s',
										 HARGA			=	'%s',
										 TOTAL_PERTEMUAN=	'%s'
									 ON DUPLICATE KEY UPDATE
									 	 NAMA_PAKET		=	'%s',
									 	 JENIS			=	'%s',
										 JUMLAH_MURID	=	'%s',
										 TEMPAT			=	'%s',
										 MATERI			=	'%s',
										 WAKTU			=	'%s',
										 HARGA			=	'%s',
										 TOTAL_PERTEMUAN=	'%s'"
									, $enkripsi->decode($_POST['iddata'])
									, $_POST['namapaket']
									, $_POST[$jns_paket]
									, $_POST['jumlah']
									, $_POST['tempat']
									, $_POST['materi']
									, str_pad($_POST['waktu_jam'],2,'0',STR_PAD_LEFT).":".$_POST['waktu_menit']
									, $_POST['harga']
									, $_POST['tot_pertemuan']
									, $_POST['namapaket']
									, $_POST[$jns_paket]
									, $_POST['jumlah']
									, $_POST['tempat']
									, $_POST['materi']
									, str_pad($_POST['waktu_jam'],2,'0',STR_PAD_LEFT).":".$_POST['waktu_menit']
									, $_POST['harga']
									, $_POST['tot_pertemuan']
									);
			$affected	=	$db->execSQL($sqlIns, 0);
	
			//JIKA DATA SUDAH MASUK, KIRIM RESPON
			if($affected > 0){
				$respon_code	=	"success";
			} else if($affected == 0){
				$respon_code	=	"null";
				$respon_msg		=	"Tidak ada perubahan data";
			} else {
				$respon_code	=	"error on server";
				$respon_msg		=	"Error Di Server. Silakan coba lagi nanti";
			}
			
		}

		echo json_encode(array(
							"respon_code"=>$respon_code,
							"respon_msg"=>$respon_msg
						));
		die();
	}
	
	//DAFTAR KEAHLIAN PENGAJAR
	$idpengajar	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	$sql		=	sprintf("SELECT A.IDMAPEL, A.NAMA_MAPEL, B.KODE_JENJANG, B.NAMA_JENJANG, C.IDPENGAJAR,
									C.IDMAPELPENGAJAR, D.NAMA_PAKET, D.JENIS AS JENIS_PAKET, D.JUMLAH_MURID, D.TEMPAT, D.MATERI,
									D.WAKTU, D.HARGA, D.TOTAL_PERTEMUAN, D.STATUS AS STATUS_PAKET
							 FROM m_mapel A
							 LEFT JOIN m_jenjang B ON A.IDJENJANG = B.IDJENJANG
							 LEFT JOIN (SELECT * FROM t_mapel_pengajar
							 			WHERE STATUS = 1 AND IDPENGAJAR = %s
										) C ON A.IDMAPEL= C.IDMAPEL
							 LEFT JOIN t_paket D ON C.IDMAPELPENGAJAR = D.IDMAPELPENGAJAR
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
		
		$checked	=	$key['IDPENGAJAR'] <> 'NULL' && $key['IDPENGAJAR'] <> '' && $key['IDPENGAJAR'] > 0 ? "checked" : "";
		
		if(!in_array($key['KODE_JENJANG'], $jenjang)){
			
			if($i == 0){
				$header		.=	"<li role='presentation' class='active'>
									<a href='#".$key['KODE_JENJANG']."' aria-controls='".$key['KODE_JENJANG']."' role='tab' data-toggle='tab'>
										<span class='icon-".$key['KODE_JENJANG']."'>
											<img src='".APP_IMG_URL."icon/".$key['KODE_JENJANG'].".png' alt='".$key['KODE_JENJANG']."' class='img-responsive'/>
										</span>
									</a>
								 </li>";
				$tabcontent	.=	"<div role='tabpanel' class='tab-pane active' id='".$key['KODE_JENJANG']."'>";
				
			} else {
				$header		.=	"<li role='presentation'>
									<a href='#".$key['KODE_JENJANG']."' aria-controls='".$key['KODE_JENJANG']."' role='tab' data-toggle='tab'>
										<span class='icon-".$key['KODE_JENJANG']."'>
											<img src='".APP_IMG_URL."icon/".$key['KODE_JENJANG'].".png' alt='".$key['KODE_JENJANG']."' class='img-responsive'/>
										</span>
									</a>
								 </li>";
				$tabcontent	.=	"</div><div role='tabpanel' class='tab-pane' id='".$key['KODE_JENJANG']."'>";
			}
		
			array_push($jenjang, $key['KODE_JENJANG']);
			
		}
		
		$tabcontent	.=	"<div class='tab-paket tab-paket2'>";
		$tabcontent	.=	"	<input name='listMapel_".$enkripsi->encode($key['IDMAPEL'])."' id='listMapel_".$enkripsi->encode($key['IDMAPEL'])."' onclick='addRemoveList(this.value,\"listMapel_".$enkripsi->encode($key['IDMAPEL'])."\")' type='checkbox' value='".$enkripsi->encode($key['IDMAPEL'])."' ".$checked."/>";
		$tabcontent	.=	"	<span> <b>".$key['NAMA_MAPEL']."</b></span>";

		if($key['IDPENGAJAR'] <> 'NULL' && $key['IDPENGAJAR'] <> '' && $key['IDPENGAJAR'] > 0){
			
			for($i=1; $i<=20; $i++){
				${"checkTemu".$i}	=	$i == $key['TOTAL_PERTEMUAN'] ? "selected" : "";
			}
			
			$jam		=	substr($key['WAKTU'],0,2) * 1;
			$menit		=	substr($key['WAKTU'],3,2) * 1;
			$checkMenit1=	$menit == "30" ? "selected" : "";
			$checkMenit2=	$menit == "0" ? "selected" : "";
			$checkJenis1=	$key['JENIS_PAKET'] == "1" ? "checked" : "";
			$checkJenis2=	$key['JENIS_PAKET'] == "2" ? "checked" : "";

			for($j=1; $j<=5; $j++){
				${"checkJam".$j}	=	$j == $jam ? "selected" : "";
			}
			$button_val	=	$key['NAMA_PAKET'] <> "" && $key['STATUS_PAKET'] ? "Lihat Paket" : "Tambah Paket";
			$tabcontent	.=	"	<span style='float: right'>
								  <input class='btn btn-kursusles btn-sm open-paket-editor' onClick='openPaketEditor(this.id)' type='button' value='".$button_val."' id='btn-paket-".$enkripsi->encode($key['IDMAPELPENGAJAR'])."'>
								</span>";
			$tabcontent	.=	"</div><br/>";
			$tabcontent	.=	"<div class='tab-paket2-editor' style='diplay:none' id='paket-".$enkripsi->encode($key['IDMAPELPENGAJAR'])."'>
								<div class='row'>
									<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
										<input type='text' name='namapaket' id='namapaket".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' class='form-control' placeholder='Nama Paket' value='".$key['NAMA_PAKET']."' /><br/>
										<input type='text' name='jumlah' id='jumlah".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' class='form-control' placeholder='Jumlah Peserta' value='".$key['JUMLAH_MURID']."' /><br/>
										<input type='text' name='tempat' id='tempat".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' class='form-control' placeholder='Tempat Kursus' value='".$key['TEMPAT']."' /><br/>
										<input type='text' name='harga' id='harga".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' class='form-control' placeholder='Harga Paket' value='".$key['HARGA']."' />
									</div>
									<div class='col-lg-8 col-md-8 col-sm-8 col-xs-12'>
										<div class='row'>
											<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>
												Jenis Paket &nbsp; &nbsp;
												<input name='jns_paket".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' value='1' id='jns_paket".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' type='radio' ".$checkJenis1." onClick='$(\"#jumlah".$enkripsi->encode($key['IDMAPELPENGAJAR'])."\").val(\"1\").prop(\"readonly\", true);'/> <small>Privat</small> &nbsp; 
												<input name='jns_paket".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' value='2' id='jns_paket".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' type='radio' ".$checkJenis2." onClick='$(\"#jumlah".$enkripsi->encode($key['IDMAPELPENGAJAR'])."\").prop(\"readonly\", false);'/> <small>Grup</small>
											</div>
										</div><br/>
										<div class='row'>
											<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
												Lama Per Pertemuan
											</div>
											<div class='col-lg-3 col-md-3 col-sm-3 col-xs-12'>
												<select name='waktu_jam' id='waktu_jam".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' class='form-control'>
													<option value='1' ".$checkJam1.">1 Jam</option>
													<option value='2' ".$checkJam2.">2 Jam</option>
													<option value='3' ".$checkJam3.">3 Jam</option>
													<option value='4' ".$checkJam4.">4 Jam</option>
													<option value='5' ".$checkJam5.">5 Jam</option>
												</select>
											</div>
											<div class='col-lg-3 col-md-3 col-sm-3 col-xs-12'>
												<select name='waktu_menit' id='waktu_menit".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' class='form-control'>
													<option value='00' ".$checkMenit1.">00 Menit</option>
													<option value='30' ".$checkMenit1.">30 Menit</option>
												</select>
											</div>
											<div class='col-lg-2 col-md-2 col-sm-2 col-xs-12'></div>
										</div><br/>
										<div class='row'>
											<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
												Total Pertemuan
											</div>
											<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
												<select name='tot_pertemuan' id='tot_pertemuan".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' class='form-control'>
													<option value='1' ".$checkTemu1." >1 Kali</option>
													<option value='2' ".$checkTemu2.">2 Kali</option>
													<option value='3' ".$checkTemu3.">3 Kali</option>
													<option value='4' ".$checkTemu4.">4 Kali</option>
													<option value='5' ".$checkTemu5.">5 Kali</option>
													<option value='6' ".$checkTemu6.">6 Kali</option>
													<option value='7' ".$checkTemu7.">7 Kali</option>
													<option value='8' ".$checkTemu8.">8 Kali</option>
													<option value='9' ".$checkTemu9.">9 Kali</option>
													<option value='10' ".$checkTemu10.">10 Kali</option>
													<option value='11' ".$checkTemu11.">11 Kali</option>
													<option value='12' ".$checkTemu12.">12 Kali</option>
													<option value='13' ".$checkTemu13.">13 Kali</option>
													<option value='14' ".$checkTemu14.">14 Kali</option>
													<option value='15' ".$checkTemu15.">15 Kali</option>
													<option value='16' ".$checkTemu16.">16 Kali</option>
													<option value='17' ".$checkTemu17.">17 Kali</option>
													<option value='18' ".$checkTemu18.">18 Kali</option>
													<option value='19' ".$checkTemu19.">19 Kali</option>
													<option value='20' ".$checkTemu20.">20 Kali</option>
												</select>
											</div>
											<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'></div>
										</div>
									</div>
								</div><br/>
								<div class='row'>
									<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
										<textarea name='materi' required class='form-control' id='materi".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' placeholder='Materi yang disampaikan' rows='5'>".$key['MATERI']."</textarea><br/>
										<input id='iddata-".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' value='".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' name='iddata' type='hidden'>
										<input id='simpanpaket-".$enkripsi->encode($key['IDMAPELPENGAJAR'])."' value='Simpan' class='btn btn-sm btn-custom2' type='button' onClick='simpanPaket(this.id)'>
									</div>
								</div>
							 </div>";
		} else {
			$tabcontent	.=	"</div><br/>";
		}
		
		$i++;

	}
	
	echo "</div>";

	if($session->cekSession() <> 2){ echo "<script>window.location.href = '".APP_URL."login'</script>"; die();}
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
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'csslevelpengajar');?>.cssfile">
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
    </head>
    <body>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

		<?=$session->getTemplate('header')?>
        <div class='container'>
            <h3 class="text-left text_kursusles page-header">LEVEL DAN KEAHLIAN ANDA</h3>
            <div id="data_content">
                <div id="dialog-confirm">
                  <p id="text_dialog"></p>
                </div><br/>
				<div class="boxSquare">
                    <div class="segmenKeahlian">
                        <div class="panel panel-default">
                            <div class="panel-heading">Keahlian Pengajar</div>
                            <div class="panel-body">
                                <div role="tabpanel">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <?=$header?>
                                    </ul>
                                    <div class="tab-content">
                                        <?=$tabcontent?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            	</div>
            </div>
            <div id="loading"></div>
        </div><br/>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jsslevel');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssvalidate');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jsslevel');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

        <?=$session->getTemplate('footer');?>

	</body>
</html>