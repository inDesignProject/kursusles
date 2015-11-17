<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();

	if($session->cekSession() <> 2){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."&rdr=send_report;q=".$_GET['q']."'</script>";
		 die();
	}
	
	require_once "php/lib/recaptchalib.php";
	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);

	//DATA NAMA DAN FOTO PENGAJAR
	$idpengajar	=	$enkripsi->decode($_GET['q']);
	$sql		=	sprintf("SELECT NAMA, FOTO FROM m_pengajar
							 WHERE IDPENGAJAR = %s LIMIT 0,1"
							, $idpengajar
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	//HABIS -- DATA NAMA DAN FOTO PENGAJAR
	
	//FUNGSI DIGUNAKAN UNTUK LIST MASALAH
	if( $enkripsi->decode($_GET['func']) == "listMasalah" && isset($_GET['func'])){
		
		$limit		=	isset($_POST['limit']) && $_POST['limit'] <> '' ? $_POST['limit'] : "0";
		$sqlM		=	sprintf("SELECT IDMASALAH, NAMA_MASALAH FROM m_report_masalah
								 WHERE STATUS = 1"
								, $limit
								);
		$resultM	=	$db->query($sqlM);
		
		if($resultM == false || $resultM == ''){
			$listM	=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
		} else {
			
			foreach($resultM as $key){
				
				$idM	=	$enkripsi->encode($key['IDMASALAH']);
				$listM	.=	"<input type='radio' name='cekMasalah' id='masalah".$idM."' value='".$idM."' onClick='getSolusi(this.value)'> ".$key['NAMA_MASALAH']."<br/>";	
				
			}
			
		}
		
		echo $listM;
		die();

	}
	//HABIS -- DATA MASALAH
	
	//FUNGSI DIGUNAKAN UNTUK LIST SOLUSI
	if( $enkripsi->decode($_GET['func']) == "listSolusi" && isset($_GET['func'])){
		
		$idmasalah	=	$enkripsi->decode($_POST['idmasalah']);
		$sqlS		=	sprintf("SELECT IDSOLUSI,NAMA_SOLUSI,KETERANGAN FROM m_report_solusi
								 WHERE IDMASALAH = %s AND STATUS = 1"
								, $idmasalah
								);
		$resultS	=	$db->query($sqlS);
		
		if($resultS == false || $resultS == ''){
			$listS	=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
			$detailM=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
		} else {

			$sqlKetM	=	sprintf("SELECT KETERANGAN FROM m_report_masalah
									 WHERE IDMASALAH = %s
									 LIMIT 0,1"
									, $idmasalah
									);
			$resultKetM	=	$db->query($sqlKetM);
			if($resultKetM == false || $resultKetM == ''){
				$detailM=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
			} else {
				$detailM=	$resultKetM[0]['KETERANGAN'];
			}

			foreach($resultS as $key){
				
				$idS	=	$enkripsi->encode($key['IDSOLUSI']);
				$listS	.=	"<input type='checkbox' name='checkSol[".$idS."]' id='checkSol[".$idS."]' value='".$idS."'> ".$key['NAMA_SOLUSI']."<br/>";	
				
			}
			
		}
		
		echo json_encode(array(
								"listSolusi"=>$listS,
								"keteranganMas"=>$detailM
								)
						);
		die();

	}
	//HABIS -- DATA SOLUSI
	
	//FUNGSI DIGUNAKAN UNTUK LAPORAN
	if( $enkripsi->decode($_GET['func']) == "saveReport" && isset($_GET['func'])){
		
		//JIKA TIDAK TERDAFTAR SEBAGAI MURID
		if(!isset($_SESSION['KursusLes']['TYPEUSER']) || $_SESSION['KursusLes']['TYPEUSER'] <> 1){
			echo json_encode(array("respon_code"=>'00004',
								   "urlRdr"=>APP_URL."login?authResult=".$enkripsi->encode('5')."&rdr=send_report;q=".$_POST['idp']
							));
			die();
		}
		
		//JIKA TIDAK MEMILIH SOLUSI
		if(!isset($_POST['checkSol']) || count($_POST['checkSol']) == 0){
			echo json_encode(array("respon_code"=>"00002"));
			die();
		}
		
		//JIKA CAPTCHA TIDAK DIISI
		if(!isset($_POST['g-recaptcha-response']) || $_POST['g-recaptcha-response'] == ''){
			echo json_encode(array("respon_code"=>"00001"));
			die();
		}
		
		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'00003'));
			die();
		}
		
		foreach($_POST['checkSol'] as $key){
			$idsol	.=	$enkripsi->decode($key).",";
		}
		
		$idsol		=	substr($idsol,0,strlen($idsol)-1);
		$idpengajar	=	$enkripsi->decode($_POST['idp']);
		$idmasalah	=	$enkripsi->decode($_POST['cekMasalah']);
		$sqlIns		=	sprintf("INSERT INTO t_report_pengajar
								 (IDMURID,IDPENGAJAR,IDMASALAH,IDSOLUSI,ALASAN)
								  VALUES
								 (%s,%s,%s,'%s','%s')"
								, $_SESSION['KursusLes']['IDUSER']
								, $idpengajar
								, $idmasalah
								, $idsol
								, $db->db_text($_POST['alasan'])
								);
		$affected	=	$db->execSQL($sqlIns, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo json_encode(array("respon_code"=>'00000'));
			die();
		} else {
			echo json_encode(array("respon_code"=>'00005'));
			die();
		}
		
		die();
		
	}
	//HABIS -- FUNGSI DIGUNAKAN LIST SOLUSI
	
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
		.boxSquare{padding:1% !important}
    </style>
        
        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login)?>
        
		<div class="container">
        	<h3 class="text-left text_kursusles page-header">LAPORKAN PENGAJAR</h3>
            <div class="row" id="form-con">
            	<?php
				if(!isset($_GET['q']) || $_GET['q'] == '' || $result == false || $result == ''){
				?>
                <center style="height: 300px;">
                	<b>
                    	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    		<div class="boxSquare">
                      			<div class="disclaimer">
                                	<h4>Halaman tidak tersedia</h4>
                                </div>
                            </div>
                        </div>
                    </b>
                </center>
				<?php
				} else {
                ?>
                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                    <div class="boxSquareWhite">
                    	<img src="<?=APP_IMG_URL?>generate_pic.php?type=pr&q=<?=$enkripsi->encode($result['FOTO'])?>&w=40&h=40" class="img-circle" style="margin: 4px;">
                        <?=$result['NAMA']?>
                    </div>
                    <div class="boxSquare">
                        <div class="disclaimer">
                            Kami akan menampung semua jenis laporan yang kami terima dan akan meneruskannya pada pihak pengajar.
                            Informasikanlah keluhan anda apa adanya agar kami dapat membantu memecahkannya. Jika ternyata apa
                            yang anda laporkan tidak sesuai, maka kami akan melakukan tindakan kepada anda mulai dari peringatan
                            hingga menon-aktifkan akun anda secara permanen.
                        </div>
                        <hr></hr>
                    </div>
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Keluhan Anda</b></div>
                            <div class="panel-body" id="keluhan-con">
                            </div>
                        </div>
                    </div>
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Solusi Yang Anda Inginkan</b></div>
                            <div class="panel-body" id="solusi-con">
								<center>Pilih masalah untuk menampilkan pilihan solusi</center>
                            </div>
                        </div>
                    </div>
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-body">
					            <textarea id="alasan" name="alasan" placeholder="Berikan keterangan tambahan" rows="5" style="width:100%"></textarea><br/>
                                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
                                <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script>
                                <input type="text" name="idp" id="idp" value="<?=$_GET['q']?>" style="display:none" />
                                <input type="button" name="kirim" id="kirim" value="Kirim Laporan" class="btn btn-sm btn-custom2 pull-right" onClick="sendRpt()" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Keterangan Masalah</b></div>
                            <div class="panel-body" id="keterangan-con">
								<center>Klik pada pilihan masalah untuk melihat detail keterangan</center>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				}
                ?>
			</div>
        </div><br/>
        
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
        <script>
		
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
						"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
						"<strong><small id='message_response'>"+msg+"</small></strong>"+
					"</div>";
			}
			
			function getListMasalah(limit){
				
				$('#keluhan-con').html("<center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
				
				$.post( "send_report?func=<?=$enkripsi->encode('listMasalah')?>", {limit: limit})
				.done(function( data ) {
					
					$('#keluhan-con').html(data);
					
				});
				
			}
			
			function getSolusi(value){
				
				$('#solusi-con').html("<center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
				$('#keterangan-con').html("<center><img src='<?=APP_IMG_URL?>loading.gif'/></center>");
				
				$.post( "send_report?func=<?=$enkripsi->encode('listSolusi')?>", {idmasalah: value})
				.done(function( data ) {
					
					data			=	JSON.parse(data);
					$('#solusi-con').html(data['listSolusi']);
					$('#keterangan-con').html(data['keteranganMas']);
					
				});
				
			}
			
			function sendRpt(){
				
				var sendData = $('#form-con input, #form-con textarea, #form-con select, #form-con radio, #form-con hidden').serialize();
				$("#form-con input, #form-con textarea").prop('disabled', true);
				
				$.post( "send_report?func=<?=$enkripsi->encode('saveReport')?>", sendData)
				.done(function( data ) {
					
					data			=	JSON.parse(data);
					if(data['respon_code'] == '00001'){
						$('#message_response_container').slideDown('fast').html(generateMsg('Anda belum mengisi captcha. Harap centang pada kotak "Saya Bukan Robot"'));
						$("#form-con input, #form-con textarea").prop('disabled', false);
					} else if(data['respon_code'] == '00002'){
						$('#message_response_container').slideDown('fast').html(generateMsg('Anda belum memilih solusi yang anda inginkan'));
						$("#form-con input, #form-con textarea").prop('disabled', false);
					} else if(data['respon_code'] == '00003'){
						$('#message_response_container').slideDown('fast').html(generateMsg('Kode <b>Captcha</b> yang anda masukkan tidak valid'));
						$("#form-con input, #form-con textarea").prop('disabled', false);
						grecaptcha.reset();
					} else if(data['respon_code'] == '00004'){
						$('#message_response_container').slideDown('fast').html(generateMsg('Anda tidak berhak melakukan aktivitas ini'));
						$("#form-con input, #form-con textarea").prop('disabled', false);
						window.location.href = data['urlRdr'];
					} else if(data['respon_code'] == '00005'){
						$('#message_response_container').slideDown('fast').html(generateMsg('Gagal di server. Silakan coba lagi'));
						$("#form-con input, #form-con textarea").prop('disabled', false);
						grecaptcha.reset();
					} else if(data['respon_code'] == '00000'){
						$('#message_response_container').slideDown('fast').html(generateMsg('Data tersimpan'));
						$('#form-con').html("<center style='height: 300px;'><b><div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'><div class='boxSquare'><div class='disclaimer'>"+
											"<h4>Terima kasih sudah mengirimkan laporan tentang pengajar kami.<br/><br/>Laporan anda akan segerap kami respon</h4></div></div></div></b></center>");
					}
					
				});
				
			}
			
			$(document).ready(function(){
				getListMasalah('0');
			});
		</script>
        
    	<?=$session->getTemplate('footer')?>

    </body>
</html>