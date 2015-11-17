<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	session_start();
	
	$type		=	$_GET['r'] == "pengajar_profil" ? "1" : "2";
	$iddata		=	$enkripsi->decode($_GET['q']);

	if($type == 1){

		//AMBIL DATA PENGAJAR
		$sql		=	sprintf("SELECT A.NAMA, B.NAMA_KOTA AS KOTA_LAHIR, A.TGL_LAHIR, A.JNS_KELAMIN, A.ALAMAT, 
										C.IDPROPINSI, C.NAMA_PROPINSI, D.IDKOTA, D.NAMA_KOTA AS KOTA_TINGGAL,
										E.IDKECAMATAN, E.NAMA_KECAMATAN, F.IDKELURAHAN, F.NAMA_KELURAHAN, A.KODEPOS, A.TELPON
								 FROM m_pengajar A
								 LEFT JOIN m_kota B ON A.IDKOTA_LAHIR = B.IDKOTA
								 LEFT JOIN m_kota D ON A.IDKOTA_TINGGAL = D.IDKOTA
								 LEFT JOIN m_propinsi C ON D.IDPROPINSI = C.IDPROPINSI
								 LEFT JOIN m_kecamatan E ON A.IDKECAMATAN = E.IDKECAMATAN
								 LEFT JOIN m_kelurahan F ON A.IDKELURAHAN = F.IDKELURAHAN
								 WHERE A.IDUSER = %s
								 LIMIT 0,1"
								, $iddata
								);
		//HABIS -- AMBIL DATA PENGAJAR

	} else {

		//AMBIL DATA MURID
		$sql		=	sprintf("SELECT A.NAMA, B.NAMA_KOTA AS KOTA_LAHIR, A.TGL_LAHIR, A.JNS_KELAMIN, A.ALAMAT, 
										C.IDPROPINSI, C.NAMA_PROPINSI, D.IDKOTA, D.NAMA_KOTA AS KOTA_TINGGAL,
										E.IDKECAMATAN, E.NAMA_KECAMATAN, F.IDKELURAHAN, F.NAMA_KELURAHAN, A.KODEPOS, A.TELPON
								 FROM m_murid A
								 LEFT JOIN m_kota B ON A.IDKOTA_LAHIR = B.IDKOTA
								 LEFT JOIN m_kota D ON A.IDKOTA_TINGGAL = D.IDKOTA
								 LEFT JOIN m_propinsi C ON D.IDPROPINSI = C.IDPROPINSI
								 LEFT JOIN m_kecamatan E ON A.IDKECAMATAN = E.IDKECAMATAN
								 LEFT JOIN m_kelurahan F ON A.IDKELURAHAN = F.IDKELURAHAN
								 WHERE A.IDUSER = %s
								 LIMIT 0,1"
								, $iddata
								);
		//HABIS -- AMBIL DATA MURID

	}

	$result		=	$db->query($sql);
	$show_login	=	"false";
	
	//FUNGSI DIGUNAKAN SIMPAN DATA PERUBAHAN
	if( $enkripsi->decode($_GET['func']) == "submitForm" && isset($_GET['func'])){

		$table		=	$enkripsi->decode($_POST['typedata']) == "1" ? "m_pengajar" : "m_murid";
		//$field		=	$enkripsi->decode($_POST['typedata']) == "1" ? "IDPENGAJAR" : "IDMURID";
		$field	= 'iduser';
		$iddata		=	$enkripsi->decode($_POST['iddata']);
		
		$sqlKotaL	=	sprintf("SELECT IDKOTA FROM m_kota WHERE NAMA_KOTA = '%s' LIMIT 0,1", $_POST['tempat_lahir']);
		$resultL	=	$db->query($sqlKotaL);
		$idkotaL	=	$resultL <> false && $resultL <> '' ? $resultL[0]['IDKOTA'] : false;
		
		if(!$idkotaL){
			echo json_encode(array("respon_code"=>"00001"));
			die();
		} else {
			$sql		=	sprintf("UPDATE m_pengajar
									 SET NAMA			=	'%s',
									 	 IDKOTA_LAHIR	=	'%s',
									 	 TGL_LAHIR		=	'%s',
									 	 JNS_KELAMIN	=	'%s',
									 	 ALAMAT			=	'%s',
									 	 IDKOTA_TINGGAL	=	'%s',
										 IDKECAMATAN	=	'%s',
										 IDKELURAHAN	=	'%s',
									 	 KODEPOS		=	'%s',
									 	 TELPON			=	'%s'
									 WHERE %s			=	'%s'"
									, $_POST['nama']
									, $idkotaL
									, $_POST['tgl_lahir']
									, $_POST['jns_kelamin']
									, $_POST['alamat']
									, $enkripsi->decode($_POST['kota'])
									, $enkripsi->decode($_POST['kecamatan'])
									, $enkripsi->decode($_POST['kelurahan'])
									, $_POST['kodepos']
									, $_POST['telpon']
									, $field
									, $iddata
									);
			$affected	=	$db->execSQL($sql, 0);
			
			$sql		=	sprintf("UPDATE m_murid
									 SET NAMA			=	'%s',
									 	 IDKOTA_LAHIR	=	'%s',
									 	 TGL_LAHIR		=	'%s',
									 	 JNS_KELAMIN	=	'%s',
									 	 ALAMAT			=	'%s',
									 	 IDKOTA_TINGGAL	=	'%s',
										 IDKECAMATAN	=	'%s',
										 IDKELURAHAN	=	'%s',
									 	 KODEPOS		=	'%s',
									 	 TELPON			=	'%s'
									 WHERE %s			=	'%s'"
									, $_POST['nama']
									, $idkotaL
									, $_POST['tgl_lahir']
									, $_POST['jns_kelamin']
									, $_POST['alamat']
									, $enkripsi->decode($_POST['kota'])
									, $enkripsi->decode($_POST['kecamatan'])
									, $enkripsi->decode($_POST['kelurahan'])
									, $_POST['kodepos']
									, $_POST['telpon']
									, $field
									, $iddata
									);
			$affected	=	$db->execSQL($sql, 0);
			
			$sql = "";
			$sql .= "UPDATE m_karyawan SET ";
			$sql .= "NAMA ='".$_POST['nama']."', ";
			$sql .= "ALAMAT ='".$_POST['alamat']."', ";
			$sql .= "JK='".$_POST['jns_kelamin']."', ";
			$sql .= "TELPON='".$_POST['telpon']."', ";
			$sql .= "TGL_LAHIR='".$_POST['tgl_lahir']."', ";
			$sql .= "IDKOTA=".$enkripsi->decode($_POST['kota'])." WHERE "; 
			$sql .= "iduser=".$iddata." ";
			
			$db->execSQL($sql, 0);
			
			if($affected > 0){
				echo json_encode(array("respon_code"=>"00000"));
			} else {
				echo json_encode(array("respon_code"=>"00002"));
			}
			
			die();
			
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
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile" />
    	<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssindex');?>.cssfile" />
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssdatepicker');?>.cssfile" />
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>

    	<?=$session->getTemplate('header', $show_login)?>

		<div class="container">
			<h3 class="text-left text_kursusles page-header">EDIT DATA</h3>
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                	<?php
					if($result <> false && $result <> ''){
						$result	=	$result[0];
					?>
					<div class="boxSquare segmenDaftar" id="editDataContainer">
                        <form action="signup_submit?func=<?=$enkripsi->encode('editdata')?>" id="editdata" autocomplete="off" class="form-horizontal">
							<div class="form-group">
								<label for="nama_lengkap" class="col-sm-3 control-label">
                                	Nama Lengkap 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                	<input type="text" class="form-control" id="nama" name="nama" maxlength="75" placeholder="Nama lengkap sesuai dengan KTP ditambah gelar" required value="<?=$result['NAMA']?>" />
                                </div>
                            </div>
							<div class="form-group">
								<label for="tempat_lahir" class="col-sm-3 control-label">
                                	Tempat Lahir
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                	<input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" maxlength="75" onkeyup="getDataKotaByInput(this.value, this.id)" placeholder="Tempat lahir sesuai KTP" required autocomplete="off"  value="<?=$result['KOTA_LAHIR']?>"/>
                                </div>
                            </div>
							<div class="form-group">
								<label for="tanggal_lahir" class="col-sm-3 control-label">
                                	Tanggal Lahir
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                	<input type="text" class="form-control" id="tgl_lahir" name="tgl_lahir" maxlength="10" placeholder="Tanggal lahir sesuai KTP" required readonly value="<?=$result['TGL_LAHIR']?>"/>
                                </div>
                            </div>
							<div class="form-group">
								<label for="jenis_lahir" class="col-sm-3 control-label">
                                	Jenis Kelamin
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
									<label class="radio-inline">
                                    	<input type="radio" id="jns_kelamin1" name="jns_kelamin" value="1" <?=$result['JNS_KELAMIN'] == "1" ? "checked" : "checked"?> /> Laki-Laki
									</label>
                                    <label class="radio-inline">
                                    	<input type="radio" id="jns_kelamin2" name="jns_kelamin" value="2" <?=$result['JNS_KELAMIN'] == "2" ? "checked" : ""?> /> Perempuan
                                    </label>
                                    <div id="jns_kelamin-validation"></div>
                                </div>
                            </div>
							<div class="form-group">
								<label for="alamat" class="col-sm-3 control-label">
                                	Alamat
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <textarea id="alamat" name="alamat" maxlength="200" class="form-control" placeholder="Alamat tempat Anda tinggal sekarang" required><?=$result['ALAMAT']?></textarea>
                                </div>
                            </div>
							<div class="form-group">
								<label for="provinsi" class="col-sm-3 control-label">
                                	Provinsi
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <select id="propinsi" name="propinsi" class="form-control" required onFocus="getDataOpt('getDataPropinsi','noparam','propinsi','','- Pilih Propinsi -');" onchange="getDataOpt('getDataKota','propinsi','kota',this.value,'- Pilih Kota / Kab -'); emptyOpt('kecamatan', '- Pilih Kecamatan -'); emptyOpt('kelurahan', '- Pilih Desa / Kelurahan -');">
                                        <option value="<?=$enkripsi->encode($result['IDPROPINSI'])?>"><?=$result['NAMA_PROPINSI']?></option>
                                    </select>
                                </div>
                            </div>
							<div class="form-group">
								<label for="kota_kabupaten" class="col-sm-3 control-label">
                                	Kota / Kabupaten
                                 	<small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <select id="kota" name="kota" class="form-control" required onchange="getDataOpt('getDataKecamatan','kota','kecamatan',this.value,'- Pilih Kecamatan -'); emptyOpt('kelurahan', '- Pilih Desa / Kelurahan -');">
                                        <option value="<?=$enkripsi->encode($result['IDKOTA'])?>"><?=$result['KOTA_TINGGAL']?></option>
                                    </select>
                                </div>
                            </div>
							<div class="form-group">
								<label for="kecamatan" class="col-sm-3 control-label">
                                	Kecamatan 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <select id="kecamatan" name="kecamatan" class="form-control" required onchange="getDataOpt('getDataKelurahan','kecamatan','kelurahan',this.value,'- Pilih Desa / Kelurahan -');">
                                        <option value="<?=$enkripsi->encode($result['IDKECAMATAN'])?>"><?=$result['NAMA_KECAMATAN']?></option>
                                    </select>
                               </div>
                            </div>
							<div class="form-group">
								<label for="kelurahan" class="col-sm-3 control-label">
                                	Kelurahan 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <select id="kelurahan" name="kelurahan" class="form-control" required>
                                        <option value="<?=$enkripsi->encode($result['IDKELURAHAN'])?>"><?=$result['NAMA_KELURAHAN']?></option>
                                    </select>
                                </div>
                            </div>
							<div class="form-group">
								<label for="kode_pos" class="col-sm-3 control-label">
                                	Kode Pos 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="kodepos" name="kodepos" maxlength="5" placeholder="Kode pos" required value="<?=$result['KODEPOS']?>" />
                                </div>
                            </div>
							<div class="form-group">
								<label for="telp_hp" class="col-sm-3 control-label">
                                	No. Telp / No. HP 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="telpon" name="telpon" maxlength="75" placeholder="No. telp atau no. hp yang aktif" required value="<?=$result['TELPON']?>" />
                                </div>
                            </div>

                            <input type="hidden" name="iddata" id="iddata" value="<?=$_GET['q']?>" />
                            <input type="hidden" name="typedata" id="typedata" value="<?=$enkripsi->encode($type)?>" />
                            <input type="submit" name="submit" id="submit" value="SIMPAN" onclick="submitData()" class="btn btn-custom" />
                            <input type="button" id="kembali" name="kembali" value="KEMBALI" onclick="javascript:history.back()" class="btn btn-custom" />
                        </form>
                        <div id="loading" style="display:none"></div>
                    </div>
					<?php
					} else {
						echo "<center><b>Tidak ada data yang ditampilkan</b></center>";
					}
					?>

                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <div class="segmenPaket">
                            <div class="panel panel-default">
                                <div class="panel-heading">Informasi</div>
                                <div class="panel-body">
                                	<p>
                                    	Berikan informasi sebenarnya dan seakurat mungkin agar murid dapat menemukan anda dengan mudah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><br/><br/>
        
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssindex');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssvalidate');?>.jsfile"></script>
        <script>
			
			jQuery.validator.setDefaults({
			  debug: true,
			  success: "valid"
			});
	
			$('#tgl_lahir').datetimepicker({
							timepicker:false,
							format:'Y-m-d',
							minDate:'1956/01/01',
							maxDate:'2001/01/01',
							lang:'id',
							closeOnDateSelect:true,
							startDate:'1998/01/01'
						});
			
			$('#editdata').validate({
				rules: {
					nama_lengkap: {minlength: 3, maxlength: 35, required: true},
					tempat_lahir: {required: true},
					tgl_lahir: {required: true},
					alamat: {minlength: 3, maxlength: 35, required: true},
					propinsi: {required: true},
					kota: {required: true},
					kecamatan: {required: true},
					kelurahan: {required: true},
					kode_pos: {minlength: 5, maxlength: 5, digits: true, required: true},
					telpon: {minlength: 9, maxlength: 35, digits: true, required: true}
				},
				highlight: function(element) {
					$(element).closest('.form-row').addClass('has-error');
				},
				unhighlight: function(element) {
					$(element).closest('.form-row').removeClass('has-error');
				},
				errorElement: 'span',
				errorClass: 'help-block',
				errorPlacement: function(error, element) {
					if(element.parent('.form-row').length) {
						error.insertAfter(element.parent());
					} else {
						error.insertAfter(element);
					}
				}
			});
			
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
						"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
						"<strong><small id='message_response'>"+msg+"</small></strong>"+
					"</div>";
			}
			
			function submitData(){
				$('#message_response_container').slideUp('fast').html("");
				var valid	=	$('#editdata').valid();

				if(valid == true){
					
					var data		=	$('#editdata input, #editdata textarea, #editdata select, #editdata radio').serialize();
				
					$.ajax({
						beforeSend	: function(){
							$('#message_response_container').slideUp('fast').html("");
							$('#editdata input, #editdata textarea, #editdata select, #editdata radio').prop('disabled', true);
						},
						complete	: function(){
						},
						type	: "POST",
						url		: "<?=APP_URL?>editdata?func=<?=$enkripsi->encode('submitForm')?>",
						data	: data,
						success : function(result) {
							
							data	=	JSON.parse(result);
							if(data['respon_code'] == "00000"){
								//window.location.href='<?=$type == "1" ? "pengajar_profil" : "index_murid"?>';
								window.location.href='index_global'
							} else if(data['respon_code'] == "00001") {
								$('#message_response_container').slideDown('fast').html(generateMsg('Data kota lahir yang anda masukkan tidak ditemukan, silakan pilih kota sesuai dengan pilihan yang muncul'));
							} else if(data['respon_code'] == "00002") {
								$('#message_response_container').slideDown('fast').html(generateMsg('Tidak ada perubahan data'));
							} else {
								$('#message_response_container').slideDown('fast').html(generateMsg('Ada kesalahan di server'));
							}
							
							$('#editdata input, #editdata textarea, #editdata select, #editdata radio').prop('disabled', false);
						},
						error: function(){
							$('#message_response_container').slideDown('fast').html(generateMsg('Error di server. Silakan coba lagi nanti'));
						}
					});
											
				} else {
					$('#message_response_container').slideDown('fast').html(generateMsg('Cek kembali isian Form anda'));
				}
				return true;
			}
		</script>
		<?=$session->getTemplate('footer')?>

	</body>
</html>