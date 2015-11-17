<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	include('php/lib/recaptchalib.php');
	
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	session_start();
		
	//FUNGSI DIGUNAKAN UNTUK KIRIM PERMINTAAN WITHDRAW
	if( $enkripsi->decode($_GET['func']) == "sendWithdraw" && isset($_GET['func'])){
		
		if(!isset($_POST['bank']) || !isset($_POST['nominal']) || $_POST['bank'] == '' || $_POST['nominal'] == ''){
			echo json_encode(array("respon_code"=>"00004", "respon_msg"=>"Gagal menyimpan. Lengkapi data isian terlebih dahulu"));
			die();
		}

		if($_POST['nominal'] * 1 == 0 || !is_numeric($_POST['nominal'])){
			echo json_encode(array("respon_code"=>"00005", "respon_msg"=>"Gagal menyimpan. Nominal withdraw tidak boleh nol dan hanya boleh angka"));
			die();
		}
		
		if($_POST['g-recaptcha-response'] * 1 == 0 || !isset($_POST['g-recaptcha-response'])){
			echo json_encode(array("respon_code"=>"00006", "respon_msg"=>"Harap klik pada kotak Saya bukan robot untuk melanjutkan"));
			die();
		}
		
		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'00007',
								   "respon_msg"=>"Harap klik pada kotak Saya bukan robot untuk melanjutkan"));
			die();
		}
		
		$iddata			=	$_SESSION['KursusLes']['IDUSER'];
		$idrekening		=	$enkripsi->decode($_POST['bank']);
		$sqlcekS		=	sprintf("SELECT IDWITHDRAW
									 FROM t_withdraw
									 WHERE TYPE = 1 AND IDUSER = %s AND STATUS = 0
									 LIMIT 0,1"
									 , $iddata
									);
		$resultcekS		=	$db->query($sqlcekS);

		if($resultcekS <> '' || $resultcekS <> false){
			echo json_encode(array("respon_code"=>"00006", "respon_msg"=>"Gagal menyimpan. Permintaan Withdraw sebelumnya belum mendapat respon dari admin"));
			die();
		}
		
		$sqlcek		=	sprintf("SELECT SUM(DEBET-KREDIT) AS SALDO
							 	 FROM t_balance WHERE JENIS_PEMILIK = 1 AND IDUSER = %s
								 LIMIT 0,1"
								 , $iddata
								);
		$resultcek		=	$db->query($sqlcek);
		
		if($resultcek <> '' && $resultcek <> false){
			
			$resultcek	=	$resultcek[0];
			$saldo		=	$resultcek['SALDO'] * 1;
			
			if($saldo >= $_POST['nominal']){
				
				$sqlIns		=	sprintf("INSERT INTO t_withdraw
										 (IDUSER, IDREKENING, TYPE,NOMINAL,TANGGAL)
										 VALUES
										 (%s, %s, 1, %s, NOW())"
										, $iddata
										, $idrekening
										, $_POST['nominal']
										);
				$affected	=	$db->execSQL($sqlIns, 0);
				
				if($affected > 0){
					echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data permintaan dikirim"));
				} else {
					echo json_encode(array("respon_code"=>"00003", "respon_msg"=>"Gagal menyimpan, silakan coba lagi nanti"));
				}
			} else {
				echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Gagal menyimpan. Saldo tidak mencukupi. Kurangi jumlah nominal"));
			}
			
		} else {
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Gagal menyimpan. Anda tidak memiliki saldo"));
		}
		die();
	}

	$idpengajar	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	$sql		=	sprintf("SELECT A.IDBALANCE, A.TGL_TRANSAKSI, B.KETERANGAN AS JENIS_TRANSAKSI, A.KETERANGAN, A.DEBET, A.KREDIT, 
									SUM(A.DEBET-A.KREDIT) AS SALDO
							 FROM t_balance A
							 LEFT JOIN m_jenis_transaksi B ON A.IDJENISTRANSAKSI = B.IDJENISTRANSAKSI
							 WHERE A.JENIS_PEMILIK = 1 AND IDUSER = %s
							 GROUP BY A.IDBALANCE
							 ORDER BY A.TGL_TRANSAKSI"
							, $idpengajar
							);
	$result		=	$db->query($sql);
	
	if($result <> '' && $result <> false){

		$saldo			=	0;
		foreach($result as $key){
			
			$saldo		=	$saldo + $key['SALDO'];
			$listdata	.=	"<tr id='row".$enkripsi->encode($key['IDBALANCE'])."'>
								<td align='center'>".$key['TGL_TRANSAKSI']."</td>
								<td>".$key['JENIS_TRANSAKSI']."</td>
								<td>".$key['KETERANGAN']."</td>
								<td align='right'>".number_format($key['DEBET'],0,',','.')."</td>
								<td align='right'>".number_format($key['KREDIT'],0,',','.')."</td>
								<td align='right'>".number_format($saldo,0,',','.')."</td>
							 </tr>";
		}
	} else {
		$listdata	=	"<tr><td colspan='6'><center><b>Tidak ada data yang ditemukan</b></center></td></tr>";
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
        	<h3 class="text-left text_kursusles page-header">BALANCE DAN WITHDRAW</h3>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
                        <div id="data_container">
                            <div class="panel panel-default">
                                <div class="panel-heading"><b>Pengajuan Withdraw</b></div>
                                <div class="panel-body" id="post-con">
                                    <div class="form-group">
                                        <i class="fa fa-tags"></i> <b>Saldo saat ini : Rp. <?=number_format($saldo,0,',','.')?>,-</b><br>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                <div class="inputDesc">
                                                    Bank Tujuan
                                                    <select id="bank" name="bank" class="form-control"></select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                <div class="inputDesc">
                                                    Nominal
                                                    <input type="text" id="nominal" name="nominal" maxlength="9" value="" class="form-control" style="text-align:right"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                <div class="inputDesc">
                                                    Centang pada kotak dibawah
                                                    <div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
                                                    <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="button" name="submit" id="submit" value="Kirim Pengajuan" onclick="sendWithdraw()" style="float:right" class="btn btn-sm btn-custom2"/>
                                    </div>
                                </div>
                           </div><br/><br/>
                           <h4>Daftar Transaksi</h4>
                           <table class="table table-bordered" id="data_table">
                                <thead>
                                    <tr>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Jenis Transaksi</th>
                                        <th class="text-center">Keterangan</th>
                                        <th class="text-center">Debet</th>
                                        <th class="text-center">Kredit</th>
                                        <th class="text-center">Saldo</th>
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
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
		<script>
		 	$(document).ready(function() {
                getDataOpt('getDataBankPengajar','idpengajar=<?=$enkripsi->encode($_SESSION['KursusLes']['IDUSER'])?>','bank','','- Pilih Bank -');
            });
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
						"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
						"<strong><small id='message_response'>"+msg+"</small></strong>"+
					"</div>";
			}
			function sendWithdraw(){
				
				var	data	=	$('#post-con input, #post-con select,  #post-con textarea').serialize();
				$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang menyimpan..'));
				$('#post-con input, #post-con select,  #post-con textarea').prop('disabled', true);
				$.post( "<?=APP_URL?>withdraw?func=<?=$enkripsi->encode('sendWithdraw')?>", data)
				.done(function( data ) {
					
					data			=	JSON.parse(data);
					if(data['respon_code'] == "00000"){
						getDataOpt('getDataBankPengajar','idpengajar=<?=$enkripsi->encode($_SESSION['KursusLes']['IDUSER'])?>','bank','','- Pilih Bank -');
						$('#nominal').val('');
					}
					grecaptcha.reset();
					$('#post-con input, #post-con select,  #post-con textarea').prop('disabled', false);
					$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
				});
				
			}
			
		</script>
        <br><br><br>
    	<?=$session->getTemplate('footer')?>
	</body>
</html>