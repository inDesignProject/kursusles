<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	include('../lib/recaptchalib.php');
	require "../lib/defines.php";

	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	session_start();
	$idmurid	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	$sqlMurid	=	sprintf("SELECT DEPOSIT FROM m_murid WHERE IDMURID = %s LIMIT 0,1", $idmurid);
	$resultMurid=	$db->query($sqlMurid);
	$resultMurid=	$resultMurid[0];
	$deposit	=	$resultMurid['DEPOSIT'] * 1;		
	
	//FUNGSI DIGUNAKAN SIMPAN PERMINTAAN
	if( $enkripsi->decode($_GET['func']) == "sendReq" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				switch($key){
					case "nominal"				:	$respon_msg	=	"Masukkan nominal yang ingin dicairkan"; break;
					case "bank"					:	$respon_msg	=	"Pilih bank tujuan pencairan"; break;
					case "norek"				:	$respon_msg	=	"Harap masukkan nomor rekening tujuan"; break;
					case "atasnama"				:	$respon_msg	=	"Harap isi atas nama pemegang rekening"; break;
					case "cabangunit"			:	$respon_msg	=	"Harap isi cabang / unit bank anda"; break;
					case "g-recaptcha-response"	:	$respon_msg	=	"Centang pada kotak Recaptcha untuk melanjutkan"; break;
					default						:	$respon_msg	=	"Lengkapi data isian anda"; break;
				}
				break;
			} else {
				${$key}		=	str_replace("'","",$value);
			}
		}
		
		if($respon_msg <> ''){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>$respon_msg));
			die();
		}
		
		if($_POST['nominal'] == 0 || !is_numeric($_POST['nominal']) || ($_POST['nominal'] * 1) <= 0){
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Nominal yang anda masukkan tidak valid"));
			die();
		}

		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'00003',"respon_msg"=>"Captcha yang anda masukkan tidak valid"));
			die();
		}
		
		if($nominal > $deposit){
			echo json_encode(array("respon_code"=>'00004',"respon_msg"=>"Nominal yang diminta tidak boleh melebihi jumlah deposit"));
			die();
		}
		
		$idbank		=	$enkripsi->decode($_POST['bank']);
		$sqlcek		=	sprintf("SELECT IDREKENING
								 FROM m_rekening
								 WHERE NO_REKENING = %s AND IDBANK = %s
								 LIMIT 0,1"
								, $_POST['norek']
								, $idbank
								);
		$resultcek	=	$db->query($sqlcek);
		
		if($resultcek <> '' && $resultcek <> false){
			$resultcek	=	$resultcek[0];
			$idrekening	=	$resultcek['IDREKENING'];
		} else {
			
			$sqlrek		=	sprintf("INSERT INTO m_rekening
									 (IDUSER_CHILD, JENIS_PEMILIK, IDBANK, NO_REKENING, CABANG_BANK, ATAS_NAMA, STATUS)
									 VALUES
									 (%s, 2, %s, %s, '%s', '%s', 1)"
									, $idmurid
									, $idbank
									, $db->db_text($_POST['norek'])
									, $db->db_text($_POST['cabangunit'])
									, $db->db_text($_POST['atasnama'])
									);
			$idrekening	=	$db->execSQL($sqlrek, 1);
			
		}
		
		$sqlIns		=	sprintf("INSERT INTO t_withdraw
								 (IDUSER, IDREKENING, TYPE, NOMINAL, TANGGAL)
								 VALUES
								 (%s, %s, 2, %s, NOW())"
								, $idmurid
								, $idrekening
								, $_POST['nominal']
								);
		$affected		=	$db->execSQL($sqlIns, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo json_encode(array("respon_code"=>'00000',"respon_msg"=>"Permintaan terkirim"));
			die();
		} else {
			echo json_encode(array("respon_code"=>'00005',"respon_msg"=>"Gagal mengirim. Silakan coba lagi nanti"));
			die();
		}
		
		die();
	}
	
	//DATA TRANSAKSI MURID
	$sql		=	sprintf("SELECT A.IDWITHDRAW, B.ATAS_NAMA, B.CABANG_BANK, C.NAMA_BANK, B.NO_REKENING,
									A.TANGGAL, A.STATUS, A.NOMINAL
							 FROM t_withdraw A
							 LEFT JOIN m_rekening B ON A.IDREKENING= B.IDREKENING
							 LEFT JOIN m_bank C ON B.IDBANK = C.IDBANK
							 WHERE A.TYPE = 2 AND A.IDUSER = %s
							 ORDER BY A.TANGGAL"
							, $idmurid
							);
	$result		=	$db->query($sql);
	
	if($result <> '' && $result <> false){
		
		$saldo	=	0;
		foreach($result as $key){
			
			switch($key['STATUS']){
				case "1"	:	$status	=	"<br/><b style='color: green'>Disetujui</b>"; break;
				case "0"	:	$status	=	"<br/><b style='color: #E8A40C'>Menunggu</b>"; break;
				case "-1"	:	$status	=	"<br/><b style='color: red'>Ditolak</b>"; break;
				default		:	$status	=	"-"; break;
			}

			$data	.=	"<tr id='baris".$enkripsi->encode($key['IDWITHDRAW'])."'>
							<td align='center'>".$key['TANGGAL']."</td>
							<td>
								".$key['NAMA_BANK']."<br/>
								".$key['NO_REKENING']."<br/>
								".$key['ATAS_NAMA']."<br/>
								".$key['CABANG_BANK']."<br/>
							</td>
							<td>".$status."</td>
							<td align='right'>".number_format($key['NOMINAL'], 0, ",", ".")."</td>
						 </tr>";
		}
		
	} else {
		$data	=	"<tr><td colspan = '4'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
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
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <h4>Ajukan Pencairan Deposit</h4>
            <span><i class="fa fa-lg fa-tags"></i> Deposit :  Rp. <?=number_format($deposit, 0, ',', '.')?>,-</span>
            <input type="button" name="ajukan" id="ajukan" value="Ajukan" class="btn btn-sm btn-custom2 pull-right" onclick="openForm(true)">
            <div id="cairContainer">
				<div id="formcair" style="display:none">
                    <p>
                        <label>Nominal </label><br/>
                        <span class="field">
                            <input type="text" id="nominal" name="nominal" maxlength="11" style="width: 30%; text-align:right" />
                        </span>
                    </p>
                    <p>
                        <label>Bank Tujuan </label><br/>
                        <span class="field">
                            <select id="bank" name="bank" class="form-control" style="width: 30%"></select>
                        </span>
                    </p>
                    <p>
                        <label>No. Rekening </label><br/>
                        <span class="field">
                            <input type="text" id="norek" name="norek" maxlength="25" style="width: 60%" />
                        </span>
                    </p>
                    <p>
                        <label>Atas Nama </label><br/>
                        <span class="field">
                            <input type="text" id="atasnama" name="atasnama" maxlength="75" style="width: 50%" />
                        </span>
                    </p>
                    <p>
                        <label>Cabang / Unit </label><br/>
                        <span class="field">
                            <input type="text" id="cabangunit" name="cabangunit" maxlength="100" style="width: 50%" />
                        </span>
                    </p>
                    <p>
                        <label>Centang Pada Kotak Recaptcha</label><br/>
                        <span class="field">
                            <div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
                            <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script>
                        </span>
                    </p>
                </div>
                <input type="button" name="batal" id="batal" value="Batal" class="btn btn-sm btn-custom2 pull-right" onclick="openForm(false)" style="display:none">
                <input type="button" name="kirim" id="kirim" value="Kirim" class="btn btn-sm btn-custom2 pull-right" onclick="sendReq()" style="display:none; margin-right:5px;">
            </div>
		</div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <h4>Historis</h4>
         <table class="table table-bordered" id="data_table">
            <thead>
                <tr>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">Rekening Tujuan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Nominal</th>
                </tr>
            </thead>
            <tbody id="bodyData">
            	<?=$data?>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script>
	function sendReq(){
		var data	=	$('#formcair input, #formcair select, #formcair textarea').serialize();
		$('#formcair input, #formcair select, #formcair textarea').prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg("Sedang menyimpan.."));
		
		$.post( "<?=APP_URL?>php/page/mwithdraw.php?func=<?=$enkripsi->encode('sendReq')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				openForm(false);
				$('#mwithdraw').click();
				$('#formcair input, #formcair select, #formcair textarea').prop('disabled', false).val('');
			}
			grecaptcha.reset();
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			$('#formcair input, #formcair select, #formcair textarea').prop('disabled', false);
			
		});
	}
	function openForm(status){
		if(status == true){
			$('#formcair').slideDown('fast');
			$('#ajukan').hide();
			$('#kirim').show();
			$('#batal').show();
			getDataOpt('getDataBank','noparam','bank','','- Pilih Bank -');
		} else {
			$('#formcair').slideUp('fast');
			$('#kirim').hide();
			$('#batal').hide();
			$('#ajukan').show();
		}
	}
	function countHarga(value, sheet){
		$.post( "<?=APP_URL?>php/page/mbalance.php?func=<?=$enkripsi->encode('getTotNominal')?>", {value: value, sheet: sheet})
		.done(function( data ) {
			$('#totNominal').html(data);
		});
	}
</script>