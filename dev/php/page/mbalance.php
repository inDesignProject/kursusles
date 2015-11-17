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
	
	//DATA TRANSAKSI MURID
	$sql		=	sprintf("SELECT A.TGL_TRANSAKSI, B.KETERANGAN AS NAMA_TRANSAKSI, 
									A.KETERANGAN, A.DEBET, A.KREDIT, C.STATUS AS STATUS_LOG, A.IDJENISTRANSAKSI
							 FROM t_balance A
							 LEFT JOIN m_jenis_transaksi B ON A.IDJENISTRANSAKSI = B.IDJENISTRANSAKSI
							 LEFT JOIN log_voucher C ON A.IDLOGVOUCHER = C.IDLOGVOUCHER
							 WHERE A.JENIS_PEMILIK = 2 AND A.IDUSER = %s
							 GROUP BY A.IDBALANCE"
							, $_SESSION['KursusLes']['IDUSER']
							);
	$result		=	$db->query($sql);
	
	if($result <> '' && $result <> false){
		
		$saldo	=	0;
		foreach($result as $key){
			
			$add_ket	=	'';
			if($key['IDJENISTRANSAKSI'] == 1){
				switch($key['STATUS_LOG']){
					case "1"	:	$add_ket	=	"<br/><b style='color: green'>(Pembelian Disetujui)</b>"; break;
					case "0"	:	$add_ket	=	"<br/><b style='color: #E8A40C'>(Menunggu Approval)</b>"; break;
					case "-1"	:	$add_ket	=	"<br/><b style='color: red'>(Pembelian Ditolak)</b>"; break;
					case "-2"	:	$add_ket	=	"<br/><b style='color: red'>(Aktifitas transfer tidak ada / melebihi batas waktu )</b>"; break;
					default		:	$add_ket	=	""; break;
				}
			}
			
			$saldo	=	$saldo + ($key['DEBET']*1) - ($key['KREDIT']*1);
			$data	.=	"<tr>
							<td align='center'>".$key['TGL_TRANSAKSI']."</td>
							<td>".$key['NAMA_TRANSAKSI']."</td>
							<td>".$key['KETERANGAN'].$add_ket."</td>
							<td align='right'>".number_format($key['DEBET'], 0, ",", ".")."</td>
							<td align='right'>".number_format($key['KREDIT'], 0, ",", ".")."</td>
							<td align='right'>".number_format($saldo, 0, ",", ".")."</td>
						 </tr>";
		}
		
	} else {
		$data	=	"<tr><td colspan = '6'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
	}
	
	//DAFTAR VOUCHER
	$sqlV		=	sprintf("SELECT IDVOUCHER, NAMA_VOUCHER, KODE_VOUCHER, NOMINAL, HARGA
							 FROM m_voucher
							 WHERE STATUS = 1"
							);
	$resultV	=	$db->query($sqlV);
	
	if($resultV <> '' && $resultV <> false){
		$i		=	1;
		foreach($resultV as $key){
			$idvoucher	=	$enkripsi->encode($key['IDVOUCHER']);
			$checked	=	$i == 1 ? "checked" : "";
			$dataV	.=	"<tr>
							<td>".$key['NAMA_VOUCHER']."</td>
							<td>".$key['KODE_VOUCHER']."</td>
							<td align='right'>".number_format($key['NOMINAL'], 0, ",", ".")."</td>
							<td align='right'>".number_format($key['HARGA'], 0, ",", ".")."</td>
							<td align='center' valign='top'><input type='radio' ".$checked." name='voucher' id='paket".$idvoucher."' value='".$idvoucher."' /></td>
						 </tr>";
			$i++;
		}
	} else {
		$dataV	=	"<tr><td colspan='5'>Tidak ada data yang ditampilkan</td></tr>";
	}

	//FUNGSI DIGUNAKAN GET NOMINAL
	if( $enkripsi->decode($_GET['func']) == "getTotNominal" && isset($_GET['func'])){
		$hasil		=	$_POST['value'] * $_POST['sheet'];
		echo "Rp. ".number_format($hasil, 0, ",", ".");
		die();
	}
	
	//FUNGSI DIGUNAKAN SIMPAN DATA PEMBELIAN
	if( $enkripsi->decode($_GET['func']) == "buyVoucher" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				switch($key){
					case "jmllembar"	:	$respon_msg	=	"Harap pilih jumlah lembar voucher yang akan dibeli"; break;
					case "idbank"		:	$respon_msg	=	"Harap pilih Bank tujuan transfer dana anda"; break;
					case "bank"			:	$respon_msg	=	"Harap pilih bank asal yang digunakan untuk mentransfer dana"; break;
					case "norek"		:	$respon_msg	=	"Harap isi nomor rekening anda"; break;
					case "atasnama"		:	$respon_msg	=	"Harap isi atas nama pemegang rekening asal"; break;
					default				:	$respon_msg	=	"Lengkapi data isian anda"; break;
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
		
		$idrekening	=	$enkripsi->decode($_POST['idbank']);
		$idvoucher	=	$enkripsi->decode($_POST['idvoucher']);
		$bankasal	=	$enkripsi->decode($_POST['bank']);
		
		$sqlvoucher	=	sprintf("SELECT NOMINAL, HARGA, NAMA_VOUCHER FROM m_voucher WHERE IDVOUCHER = %s LIMIT 0,1", $idvoucher);
		$resultVcr	=	$db->query($sqlvoucher);
		$resultVcr	=	$resultVcr[0];
		$namavcr	=	$resultVcr['NAMA_VOUCHER'];
		$hargavcr	=	$resultVcr['HARGA'];
		$Thargavcr	=	$resultVcr['HARGA'] *  $_POST['jmllembar'];
		$nominalvcr	=	$resultVcr['NOMINAL'];
		$Tnominalvcr=	$resultVcr['NOMINAL'] *  $_POST['jmllembar'];
		$totalRP	=	$resultVcr['HARGA'] *  $_POST['jmllembar'];
		$now		=	date('Y-m-d H:i:s');
		$maxtrf		=	date('Y-m-d H:i:s', strtotime($now. ' + 2 days'));
		$txtmaxtrf	=	date('d-m-Y', strtotime($now. ' + 2 days'));
		
		$sqllog		=	sprintf("INSERT INTO log_voucher
								 (IDMURID, IDREKENING, IDVOUCHER, TOTAL_LEMBAR, TOTAL_VOUCHER, TOTAL_RP, IDBANKASAL, NOREK, ATAS_NAMA,
								  TGL_PEMBELIAN, TGL_MAXTRF, STATUS)
								 VALUES
								 ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',0)"
								, $_SESSION['KursusLes']['IDUSER']
								, $idrekening
								, $idvoucher
								, $_POST['jmllembar']
								, $Tnominalvcr
								, $totalRP
								, $bankasal
								, $db->db_text($_POST['norek'])
								, $db->db_text($_POST['atasnama'])
								, $now
								, $maxtrf
								);
		$idlog		=	$db->execSQL($sqllog, 1);
			
		//JIKA LAST ID > 0
		if($idlog > 0){
			
			$nominalvcr	=	number_format($nominalvcr, 0, ',', '.');
			$hargavcr	=	number_format($hargavcr, 0, ',', '.');
			$ket		=	"Pembelian ".$_POST['jmllembar']." lembar Voucher ".$namavcr." nominal ".$nominalvcr." seharga Rp. ".$hargavcr.",-";
			$sqlins		=	sprintf("INSERT INTO t_balance 
									(IDJENISTRANSAKSI, IDLOGVOUCHER, IDJADWALMENGAJAR, JENIS_PEMILIK, IDUSER, TGL_TRANSAKSI, DEBET,
									 KREDIT, KETERANGAN)
									 VALUES
									 ('1','%s',0,'2','%s',NOW(),0,'0','%s')"
									, $idlog
									, $_SESSION['KursusLes']['IDUSER']
									, $ket
									);
			$lastID		=	$db->execSQL($sqlins, 1);
			
			//JIKA LAST ID > 0
			if($lastID > 0){

				$sqlrek		=	sprintf("SELECT B.NAMA_BANK, A.NO_REKENING, A.UNIT_BANK, A.CABANG_BANK, A.ATAS_NAMA
										 FROM admin_rekening A
										 LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
										 WHERE A.IDREKENING = %s
										 LIMIT 0,1"
										, $idrekening
										);
				$resultrek	=	$db->query($sqlrek);
				$resultrek	=	$resultrek[0];
				
				$message	=	"<html>
								<head>
								</head>
								
								<body>
									
									<p>
									Halo ".$_SESSION['KursusLes']['NAMA'].",<br/><br/>
									Anda telah membeli paket voucher yang kami sediakan di KursusLes.com.
									Dengan data pembelian yang kami terima sebagai berikut :<br /><br/>

									<b>Voucher ".$namavcr."</b><br />
									Nominal : ".$nominalvcr."<br />
									Lembar yang dibeli : ".$_POST['jmllembar']."<br/>
									Harga Per Lembar : Rp. ".$hargavcr.",-<br/>
									Total voucher yang diterima : Rp. ".number_format($Tnominalvcr, 0, ',', '.').",-<br/>
									Total yang harus dibayar : Rp. ".number_format($Thargavcr, 0, ',', '.').",-<br /><br/>

									Rekening asal dana : ".$db->db_text($_POST['norek'])."<br />
									Atas nama : ".$db->db_text($_POST['atasnama'])."<br /><br/>
									
									setelah menerima email notifikasi ini, segeralah untuk mentransfer 
									nominal sejumlah yang tertera diatas ke nomor rekening yang sudah 
									dipilih dengan data sebagai berikut :<br /><br />
								
									Bank <b>".$resultrek['NAMA_BANK']."</b> cabang <b>".$resultrek['CABANG_BANK']."</b> unit <b>".$resultrek['UNIT_BANK']."</b>
									dengan nomor rekening <b>".$resultrek['NO_REKENING']."</b> atas nama <b>".$resultrek['ATAS_NAMA']."</b><br /><br />
									
									Admin akan menyetujui pembelian voucher jika nominal yang dikirimkan sesuai dan berasal dari rekening yang sesuai dengan data yang anda kirim<br />
									Harap lakukan transfer dana sebelum tanggal <b>".$txtmaxtrf."</b>, jika melebihi batas waktu tidak ada dana yang masuk, maka pembelian akan ditolak dan dibatalkan<br />
									
									Best regards,<br /><br />
									Admin KursusLes.com
									</p>
								
								</body>
								</html>";
				
				$session->sendEmail($_SESSION['KursusLes']['EMAIL'], $_SESSION['KursusLes']['NAMA'], "Pembelian Voucher KursusLes.com", $message);
				
				echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data tersimpan. Silakan lakukan transfer dana. Saldo akan bertambah saat admin mengapprove pembelian anda"));
				die();
				
			} else {
				echo json_encode(array("respon_code"=>"00003", "respon_msg"=>"Gagal menyimpan data. Silakan coba lagi nanti"));
				die();
			}

		} else {
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Gagal menyimpan data. Silakan coba lagi nanti"));
			die();
		}
		
	}
	
	//FUNGSI DIGUNAKAN GET FORM
	if( $enkripsi->decode($_GET['func']) == "getForm" && isset($_GET['func'])){
		
		$idvou		=	$enkripsi->decode($_POST['iddata']);
		$sqlVou		=	sprintf("SELECT NAMA_VOUCHER, KODE_VOUCHER, NOMINAL, HARGA
								 FROM m_voucher
								 WHERE IDVOUCHER = %s
								 LIMIT 0,1"
								, $idvou
								);
		$resultVou	=	$db->query($sqlVou);
		
		if($resultVou <> '' && $resultVou <> false){
			
			$resultVou	=	$resultVou[0];
			$sqlrek		=	sprintf("SELECT B.NAMA_BANK, A.NO_REKENING, A.ATAS_NAMA, A.IDREKENING
									 FROM admin_rekening A
									 LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
									 WHERE A.STATUS = 1
									 ORDER BY B.NAMA_BANK"
									);
			$resultrek	=	$db->query($sqlrek);
			
			if($resultrek <> '' && $resultrek <> false){
				$j		=	1;
				foreach($resultrek as $key){
					$checked	=	$j == 1 ? "checked" : "";
					$idbank		=	$enkripsi->encode($key['IDREKENING']);
					$listRek	.=	"<input type='radio' ".$checked." name='idbank' id='bank".$idbank."' value='".$idbank."' /> ".$key['NAMA_BANK']." - No Rek. ".$key['NO_REKENING']." [ an. ".$key['ATAS_NAMA']." ] <br/>";
					$j++;
				}
			} else {
				$listRek	=	"Data rekening tujuan tidak tersedia";
			}
			
			echo "<form id='formVoucher' action''>
					<small>* Harap lengkapi form dibawah ini:</small><br/>
					Voucher : <b>".$resultVou['NAMA_VOUCHER']." (".$resultVou['KODE_VOUCHER'].")</b><br/>
					Nominal / Harga : <b>".number_format($resultVou['NOMINAL'],0,',','.')." / ".number_format($resultVou['HARGA'],0,',','.')."</b><br/>
					Jumlah Voucher : <select id='jmllembar' name='jmllembar' onchange='countHarga(this.value, ".$resultVou['HARGA'].")'>
										<option value='1'>1 Lembar</option>
										<option value='2'>2 Lembar</option>
										<option value='3'>3 Lembar</option>
										<option value='4'>4 Lembar</option>
										<option value='5'>5 Lembar</option>
										<option value='6'>6 Lembar</option>
									 </select><br/>
					Jumlah Nominal yang harus di transfer : <b id='totNominal'>Rp. ".number_format($resultVou['HARGA'],0,',','.')."</b><br/><br/>
					Pilih Rekening Tujuan Transfer :<br/>
					".$listRek."<br/><br/>
					Data Rekening Anda :<br/>
					<select id='bank' name='bank' class='form-control'>
                    	<option value=''>- Bank Asal -</option>
					</select><br/>
					<input name='idvoucher' id='idvoucher' type='hidden' value='".$_POST['iddata']."'>
					<input name='norek' id='norek' class='form-control' maxlength='25' autocomplete='off' type='text' placeholder='No Rekening yang digunakan untuk transfer dana'><br/>
					<input name='atasnama' id='atasnama' class='form-control' maxlength='150' autocomplete='off' type='text' placeholder='Nama pemilik rekening'><br/>
				  </form>
				  <script>
				  	getDataOpt(\"getDataBank\",\"noparam\",\"bank\",\"\",\"- Pilih Bank -\");
				  </script>";
		} else {
			echo "<center><b>Form pembelian tidak tersedia</b></center>";
		}
		die();
		
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
            <h4>Beli Voucher</h4>
             <table class="table table-bordered" id="list_voucher">
                <thead>
                    <tr>
                        <th class="text-center"><i class='fa fa-file-o'></i> Voucher</th>
                        <th class="text-center"><i class='fa fa-barcode'></i> Kode</th>
                        <th class="text-center"><i class='fa fa-credit-card'></i> Nominal</th>
                        <th class="text-center"><i class='fa fa-money'></i> Harga</th>
                        <th class="text-center"><i class='fa fa-check'></i> Pilih</th>
                    </tr>
                </thead>
                <tbody>
        			<?=$dataV?>
                </tbody>
            </table>
            <input type="button" name="beli" id="beli" value="Beli Voucher" class="btn btn-sm btn-custom2 pull-right" onclick="openForm(true)">
            <div id="buyContainer">
				<div id="formBuy">
                </div>
                <input type="button" name="batal" id="batal" value="Batal" class="btn btn-sm btn-custom2 pull-right" onclick="openForm(false)" style="display:none">
                <input type="button" name="kirim" id="kirim" value="Kirim" class="btn btn-sm btn-custom2 pull-right" onclick="buyVoucher()" style="display:none; margin-right:5px;">
            </div>
		</div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <h4>Historis</h4>
         <table class="table table-bordered" id="Kjadwal_table">
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
            	<?=$data?>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
<script>
	function openForm(status){

		var iddata	=	$('input[name=voucher]:checked', '#list_voucher').val();

		if(status == true){
			$('#list_voucher').slideUp('fast');
			$('#buyContainer').slideDown('fast');
			$('#formBuy').html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
			$.post( "<?=APP_URL?>php/page/mbalance.php?func=<?=$enkripsi->encode('getForm')?>", {iddata: iddata})
			.done(function( data ) {
				$('#formBuy').html(data);
			});
			$('#batal, #kirim').show();
			$('#beli').hide();
		} else {
			$('#list_voucher').slideDown('fast');
			$('#buyContainer').slideUp('fast');
			$('#formBuy').html('');
			$('#batal, #kirim').hide();
			$('#beli').show();
		}
	}
	function buyVoucher(){
		var data	=	$('#formBuy input, #formBuy select').serialize();
		$('#formBuy input, #formBuy select').prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg("Sedang menyimpan"));
		
		$.post( "<?=APP_URL?>php/page/mbalance.php?func=<?=$enkripsi->encode('buyVoucher')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				openForm(false);
			}
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			$('#formBuy input, #formBuy select').prop('disabled', false);
			
		});
		
	}
	function countHarga(value, sheet){
		$.post( "<?=APP_URL?>php/page/mbalance.php?func=<?=$enkripsi->encode('getTotNominal')?>", {value: value, sheet: sheet})
		.done(function( data ) {
			$('#totNominal').html(data);
		});
	}
</script>