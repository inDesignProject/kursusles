<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	require_once "php/lib/recaptchalib.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	//AMBIL DATA PENGAJAR TERAKHIR
	$sql			=	sprintf("SELECT A.NAMA, A.ALAMAT, B.NAMA_KOTA, A.FOTO, A.IDPENGAJAR
								 FROM m_pengajar A
								 LEFT JOIN m_kota B ON A.IDKOTA_TINGGAL = B.IDKOTA
								 ORDER BY IDPENGAJAR DESC
								 LIMIT 0,5");
	$listNewTeacher	=	$db->query($sql);
	
	session_start();
	unset($_SESSION['KursusLes']);
	session_destroy();

	$show_login		=	"true";
	
	//FUNGSI DIGUNAKAN UNTUK CEK DATA EMAIL
	if( $enkripsi->decode($_GET['func']) == "cekMail" && isset($_GET['func'])){
		$sql	=	sprintf("SELECT COUNT(EMAIL) AS TOTAL FROM (
								   SELECT EMAIL FROM m_pengajar WHERE EMAIL = '%s'
								   UNION ALL
								   SELECT EMAIL FROM m_murid WHERE EMAIL = '%s'
							 ) AS A"
					, $_POST['email']
					, $_POST['email']
					);

		$result		=	$db->query($sql);
		
		//JIKA TOTAL EMAIL DITEMUKAN
		if(isset($result) && $result <> false) {
			echo $result[0]['TOTAL'];
		} else {
			echo "0";
		}
		die();
	}
	//HABIS -- FUNGSI DIGUNAKAN UNTUK CEK DATA EMAIL
	
	//FUNGSI DIGUNAKAN UNTUK CEK DATA USERNAME
	if( $enkripsi->decode($_GET['func']) == "cekUsername" && isset($_GET['func'])){
		
		$sql	=	sprintf("SELECT COUNT(IDUSER) AS TOTAL FROM m_user WHERE USERNAME = '%s'"
					, $_POST['username']
					);
		$result		=	$db->query($sql);
		
		//JIKA TOTAL EMAIL DITEMUKAN
		if(isset($result) && $result <> false) {
			echo $result[0]['TOTAL'];
		} else {
			echo "0";
		}
		die();
	}
	//HABIS -- FUNGSI DIGUNAKAN UNTUK CEK DATA USERNAME

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

    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>

		<div class="container">
			<h3 class="text-left text_kursusles page-header">HALAMAN PENDAFTARAN</h3>
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
					<div class="boxSquare segmenDaftar" id="signupContainer">
                        <form action="signup_submit" id="signup" autocomplete="off" class="form-horizontal">
							<div class="form-group">
								<label for="nama_lengkap" class="col-sm-3 control-label">
                                	Nama Lengkap 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                	<input type="text" class="form-control" id="nama" name="nama" maxlength="75" placeholder="Nama lengkap sesuai dengan KTP ditambah gelar" required />
                                </div>
                            </div>
							<div class="form-group">
								<label for="tempat_lahir" class="col-sm-3 control-label">
                                	Tempat Lahir
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                	<input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" maxlength="75" onkeyup="getDataKotaByInput(this.value, this.id)" placeholder="Tempat lahir sesuai KTP" required autocomplete="off"/>
                                </div>
                            </div>
							<div class="form-group">
								<label for="tanggal_lahir" class="col-sm-3 control-label">
                                	Tanggal Lahir
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                	<input type="text" class="form-control" id="tgl_lahir" name="tgl_lahir" maxlength="10" placeholder="Tanggal lahir sesuai KTP" required readonly/>
                                </div>
                            </div>
							<div class="form-group">
								<label for="jenis_lahir" class="col-sm-3 control-label">
                                	Jenis Kelamin
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
									<label class="radio-inline">
                                    	<input type="radio" id="jns_kelamin1" name="jns_kelamin" value="1" checked /> Laki-Laki
									</label>
                                    <label class="radio-inline">
                                    	<input type="radio" id="jns_kelamin2" name="jns_kelamin" value="2" /> Perempuan
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
                                    <textarea id="alamat" name="alamat" maxlength="200" class="form-control" placeholder="Alamat tempat Anda tinggal sekarang" required></textarea>
                                </div>
                            </div>
							<div class="form-group">
								<label for="provinsi" class="col-sm-3 control-label">
                                	Provinsi
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <select id="propinsi" name="propinsi" class="form-control" required onchange="getDataOpt('getDataKota','propinsi','kota',this.value,'- Pilih Kota / Kab -'); emptyOpt('kecamatan', '- Pilih Kecamatan -'); emptyOpt('kelurahan', '- Pilih Desa / Kelurahan -');">
                                        <option value="">- Pilih Propinsi -</option>
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
                                        <option value="">- Pilih Kota / Kab -</option>
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
                                        <option value="">- Pilih Kecamatan -</option>
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
                                        <option value="">- Pilih Kelurahan / Desa -</option>
                                    </select>
                                </div>
                            </div>
							<div class="form-group">
								<label for="kode_pos" class="col-sm-3 control-label">
                                	Kode Pos 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="kodepos" name="kodepos" maxlength="5" placeholder="Kode pos" required  />
                                </div>
                            </div>
							<div class="form-group">
								<label for="telp_hp" class="col-sm-3 control-label">
                                	No. Telp / No. HP 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="telpon" name="telpon" maxlength="75" placeholder="No. telp atau no. hp yang aktif" required />
                                </div>
                            </div>
							<div class="form-group">
								<label for="email" class="col-sm-3 control-label">
                                	Email 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="email" name="email" maxlength="50" placeholder="Email" required onkeyup="cekMail(this.value)" />
                                	<div id="mail-msg" style="margin-top: 4px;"></div>
                                </div>
                            </div>
							<h4 class="page-header">Data Akun</h4>
							<!--<div class="form-group">
								<label for="jenis_daftar" class="col-sm-3 control-label">
                                	Daftar Sebagai 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
									<label class="radio-inline">
										<input type="radio" name="jenis_akun" id="jenis_akun_murid" value="Murid" /> Murid
									</label>
									<label class="radio-inline">
										<input type="radio" name="jenis_akun" id="jenis_akun_pengajar" value="Pengajar" /> Pengajar
									</label>
								</div>
							</div>-->
							<div class="form-group">
								<label for="username" class="col-sm-3 control-label">
                                	Username 
                                  	<small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="username" name="username" maxlength="25" placeholder="Username" required onkeyup="cekUsername(this.value)" />
                                	<div id="uname-msg" style="margin-top: 4px;"></div>
                                </div>
                            </div>
							<div class="form-group">
								<label for="password" class="col-sm-3 control-label">
                                	Password
                                </label>
								<div class="col-sm-9">
                                    <input type="password" id="password1" name="password1" maxlength="25" class="form-control" placeholder="Password" required />
                                </div>
                            </div>
							<div class="form-group">
								<label for="ulangi_password" class="col-sm-3 control-label">
                                	Ulangi Password
                                </label>
								<div class="col-sm-9">
                                    <input type="password" id="password2" name="password2" maxlength="25" class="form-control" placeholder="Ulangi Password" required />
                                </div>
                            </div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
                                	Klik pada kotak untuk verifikasi
									<div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
									<script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script>
								</div>
							</div>
                    
                            <input type="submit" name="submit" id="submit" value="DAFTAR" onclick="submitData()" class="btn btn-custom" />
                            <input type="reset" name="reset" id="reset" value="RESET" class="btn btn-custom" />
                            <input type="button" id="kembali" name="kembali" value="KEMBALI" onclick="javascript:history.back()" class="btn btn-custom" />
                        </form>
                        <div id="loading" style="display:none"></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <div class="segmenPaket">
                            <div class="panel panel-default">
                                <div class="panel-heading">Pengajar Terbaru</div>
                                <div class="panel-body">
                                	<?php
									foreach($listNewTeacher as $key){
									?>
                                    <div class="media">
                                        <div class="media-left">
                                            <a href="<?=APP_URL?>pengajar_profil.php?q=<?=$enkripsi->encode($key['IDPENGAJAR'])?>">
                                                <img src="<?=APP_IMG_URL?>generate_pic.php?type=pr&w=70&h=70&q=<?=$enkripsi->encode($key['FOTO'])?>" alt="<?=$key['NAMA']?>"/>
                                            </a>
                                        </div>
                                        <div class="media-body">
                                            <h4 class="media-heading"><a href="<?=APP_URL?>pengajar_profil.php?q=<?=$enkripsi->encode($key['IDPENGAJAR'])?>"><?=$key['NAMA']?></a></h4>
                                            <?=$key['ALAMAT']?>, <?=$key['NAMA_KOTA']?>
                                        </div>
                                    </div>
                                    <?php
									}
									?>
                                    <a href="search_result.php?w=<?=$enkripsi->encode('all')?>" class="btn btn-custom btn-xs">Lihat semua pengajar</a>
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
			
			var	mailValid, usernameValid;
			
			jQuery.validator.setDefaults({
			  debug: true,
			  success: "valid"
			});
	
			getDataOpt('getDataPropinsi','noparam','propinsi','','- Pilih Propinsi -');
			$('#tgl_lahir').datetimepicker({
							timepicker:false,
							format:'Y-m-d',
							minDate:'1956/01/01',
							maxDate:'2001/01/01',
							lang:'id',
							closeOnDateSelect:true,
							startDate:'1998/01/01'
						});
			
			function cekMail(value){
				var valid	=	$('#email').valid();
				if(valid == true){
					$.post("<?=APP_URL?>signup.php?func=<?=$enkripsi->encode('cekMail')?>",{'email':value})
					.done(function( data ) {
						if(data >= 1){
							$('#mail-msg').html("Masukkan email yang lain");
							$('#email').focus();
							mailValid = false;
						} else {
							$('#mail-msg').html("Email valid");
							mailValid = true;
						}
					});
				} else {
					$('#mail-msg').html("");
					mailValid = false;
				}
				return true;
			}
			
			function cekUsername(value){
				var valid	=	$('#username').valid();
				if(valid == true){
					$.post("<?=APP_URL?>signup.php?func=<?=$enkripsi->encode('cekUsername')?>",{'username':value})
					.done(function( data ) {
						if(data >= 1){
							$('#uname-msg').html("Masukkan username yang lain");
							$('#username').focus();
							usernameValid = false;
						} else {
							$('#uname-msg').html("username valid");
							usernameValid = true;
						}
					});
				} else {
					$('#uname-msg').html("");
					usernameValid = false;
				}
				return true;
			}
			
			$('#signup').validate({
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
					telpon: {minlength: 9, maxlength: 35, digits: true, required: true},
					email: {minlength: 5, maxlength: 35, required: true, email:true},
					username: {minlength: 8, maxlength: 35, required: true},
					password1: {minlength: 8, maxlength: 35, required: true},
					password2: {required: true,  equalTo: "#password1"}
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
				var valid	=	$('#signup').valid();
				mailValid = true;
				usernameValid = true;
				valid = true;
				if(mailValid == false) {
					$('#message_response_container').slideDown('fast').html(generateMsg('Email sudah digunakan. Masukkan Email yang lain'));
				} else if(usernameValid == false){
					$('#message_response_container').slideDown('fast').html(generateMsg('Username sudah digunakan. Masukkan Username yang lain'));
				} else {
					if(valid == true){
						
						//if(document.getElementsByName('jenis_akun').value == ''){
						//	$('#message_response_container').slideDown('fast').html(generateMsg('Anda belum memilih jenis pendaftaran sebagai //<b>Pengajar / Murid</b>'));
	//						return false;
	//					}
						
						var data		=	$('#signup input, #signup textarea, #signup select, #signup radio').serialize();

						$.ajax({
							beforeSend	: function(){
								$('#message_response_container').slideUp('fast').html("");
								$('#signup input, #signup textarea, #signup select, #signup radio').prop('disabled', true);
							},
							complete	: function(){
							},
							type	: "POST",
							url		: "<?=APP_URL?>signup_submit.php?func=<?=$enkripsi->encode('submitForm')?>",
							data	: data,
							success : function(result) {
								alert(result)
								data	=	JSON.parse(result);
								if(data['respon_code'] == "000000"){
									$('#signupContainer').html(data['respon_msg']);
								} else if(data['respon_code'] == "000001") {
									$('#message_response_container').slideDown('fast').html(generateMsg('Error di server. Silakan coba lagi nanti'));
								} else if(data['respon_code'] == "000002") {
									$('#message_response_container').slideDown('fast').html(generateMsg('Data tidak valid. Harap pilih username lainnya'));
								} else if(data['respon_code'] == "000003") {
									$('#message_response_container').slideDown('fast').html(generateMsg('Kota lahir tidak valid. Harap isi dengan benar, pilih kota sesuai dengan pilihan yang muncul'));
								} else if(data['respon_code'] == "000004"){
									$('#message_response_container').slideDown('fast').html(generateMsg('Klik pada kotak <b>Saya bukan robot</b> untuk melanjutkan'));
								} else if(data['respon_code'] == "000005"){
									$('#message_response_container').slideDown('fast').html(generateMsg('Akses ditolak. Kami menganggap anda adalah robot'));
								}
								
								$('#signup input, #signup textarea, #signup select').prop('disabled', false);
							},
							error: function(){
								$('#message_response_container').slideDown('fast').html(generateMsg('Error di server. Silakan coba lagi nanti'));
							}
						});
												
					} else {
						$('#message_response_container').slideDown('fast').html(generateMsg('Cek kembali isian Form anda'));
					}
				}
				return true;
			}
		</script>
		<?=$session->getTemplate('footer')?>

	</body>
</html>