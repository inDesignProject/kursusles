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
	
	//FUNGSI DIGUNAKAN UNTUK CEK DATA EMAIL
	if( $enkripsi->decode($_GET['func']) == "cekMail" && isset($_GET['func'])){
		$sql	=	sprintf("SELECT COUNT(EMAIL) AS TOTAL FROM m_perusahaan WHERE EMAIL = '%s'", $db->db_text($_POST['email']));
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
		
		$sql	=	sprintf("SELECT COUNT(IDPERUSAHAAN) AS TOTAL FROM m_perusahaan WHERE USERNAME = '%s'"
					, $db->db_text($_POST['username'])
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
	
	//AMBIL DATA PERUSAHAAN TERAKHIR
	$sql			=	sprintf("SELECT A.NAMA_PERUSAHAAN, A.ALAMAT_KANTOR, B.NAMA_KOTA, A.LOGO, A.IDPERUSAHAAN
								 FROM m_perusahaan A
								 LEFT JOIN m_kota B ON A.IDKOTA = B.IDKOTA
								 ORDER BY IDPERUSAHAAN DESC
								 LIMIT 0,5");
	$listNewPerush	=	$db->query($sql);
	
	session_start();
	unset($_SESSION['KursusLesLoker']);
	session_destroy();

	$show_login		=	"true";
	
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
								<label for="nama_perusahaan" class="col-sm-3 control-label">
                                	Nama Perusahaan 
                                  <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                	<input type="text" class="form-control" id="nama" name="nama" maxlength="75" placeholder="Nama perusahaan anda" required />
                                </div>
                            </div>
							<div class="form-group">
								<label for="alamat" class="col-sm-3 control-label">
                                	Alamat
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <textarea id="alamat" name="alamat" maxlength="200" class="form-control" placeholder="Alamat lengkap kantor" required></textarea>
                                </div>
                            </div>
							<div class="form-group">
								<label for="provinsi" class="col-sm-3 control-label">
                                	Propinsi
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <select id="propinsi" name="propinsi" class="form-control" required onchange="getDataOpt('getDataKota','propinsi','kota',this.value,'- Pilih Kota / Kab -');">
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
                                    <select id="kota" name="kota" class="form-control" required >
                                        <option value="">- Pilih Kota / Kab -</option>
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
                                	No. Telp
                                  <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="telpon" name="telpon" maxlength="75" placeholder="No. telp kantor yang aktif" required />
                                </div>
                            </div>
							<div class="form-group">
								<label for="email" class="col-sm-3 control-label">
                                	Email 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="email" name="email" maxlength="50" placeholder="Email Perusahaan" required onkeyup="cekMail(this.value)" />
                                	<div id="mail-msg" style="margin-top: 4px;"></div>
                                </div>
                            </div>
							<div class="form-group">
								<label for="web" class="col-sm-3 control-label">
                                	Alamat Web 
                                    <small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <input type="text" class="form-control" id="website" name="website" maxlength="50" placeholder="Alamat website perusahaan" required />
                                	<div id="web-msg" style="margin-top: 4px;"></div>
                                </div>
                            </div>
							<div class="form-group">
								<label for="jenis_usaha" class="col-sm-3 control-label">
                                	Bidang Usaha
                                 	<small class="text-danger">*</small>
                                </label>
								<div class="col-sm-9">
                                    <select id="jnsusaha" name="jnsusaha" class="form-control" required >
                                        <option value="">- Pilih Bidang Usaha -</option>
                                    </select>
                                </div>
                            </div>
                            
							<h4 class="page-header">Data Akun</h4>
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
                                <div class="panel-heading">Perusahaan Terbaru</div>
                                <div class="panel-body">
                                	<?php
									if($listNewPerush <> '' && $listNewPerush <> false){
										foreach($listNewPerush as $key){
										?>
										<div class="media">
											<div class="media-left">
												<a href="<?=APP_URL?>perusahaan_profil?q=<?=$enkripsi->encode($key['IDPERUSAHAAN'])?>">
													<img src="<?=APP_IMG_URL?>generate_pic.php?type=lkr&w=70&h=70&q=<?=$enkripsi->encode($key['LOGO'])?>" alt="<?=$key['NAMA_PERUSAHAAN']?>"/>
												</a>
											</div>
											<div class="media-body">
												<h4 class="media-heading"><a href="<?=APP_URL?>perusahaan_profil.php?q=<?=$enkripsi->encode($key['IDPERUSAHAAN'])?>"><?=$key['NAMA_PERUSAHAAN']?></a></h4>
												<?=$key['ALAMAT_KANTOR']?>, <?=$key['NAMA_KOTA']?>
											</div>
										</div>
										<?php
										}
										?>
	                                    <a href="search_result.php?w=<?=$enkripsi->encode('all')?>" class="btn btn-custom btn-xs">Lihat semua perusahaan</a>
									<?php
									} else {
										echo "<center><b>Tidak ada data yang ditampilkan</b></center>";
									}
									?>
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
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssvalidate');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
        <script>
			
			var	mailValid, usernameValid;
			
			jQuery.validator.setDefaults({
			  debug: true,
			  success: "valid"
			});
	
			getDataOpt('getDataPropinsi','noparam','propinsi','','- Pilih Propinsi -');
			getDataOpt('getDataJenisUsaha','noparam','jnsusaha','','- Pilih Jenis Usaha -');

			function cekMail(value){
				var valid	=	$('#email').valid();
				if(valid == true){
					$.post("<?=APP_URL?>signup_pemberi.php?func=<?=$enkripsi->encode('cekMail')?>",{'email':value})
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
					$.post("<?=APP_URL?>signup_pemberi.php?func=<?=$enkripsi->encode('cekUsername')?>",{'username':value})
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
					nama: {minlength: 3, maxlength: 35, required: true},
					alamat: {minlength: 3, maxlength: 35, required: true},
					propinsi: {required: true},
					kota: {required: true},
					kode_pos: {minlength: 5, maxlength: 5, digits: true, required: true},
					telpon: {minlength: 9, maxlength: 35, digits: true, required: true},
					website: {minlength: 9, maxlength: 75,required: true},
					email: {minlength: 5, maxlength: 35, required: true, email:true},
					jnsusaha: {required: true},
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
				$('#message_response_container').slideDown('fast').html(generateMsg("Sedang Mengirim..."));
				var valid	=	$('#signup').valid();

				if(mailValid == false) {
					$('#message_response_container').slideDown('fast').html(generateMsg('Email sudah digunakan. Masukkan Email yang lain'));
				} else if(usernameValid == false){
					$('#message_response_container').slideDown('fast').html(generateMsg('Username sudah digunakan. Masukkan Username yang lain'));
				} else {
					if(valid == true){
						
						var data		=	$('#signup input, #signup textarea, #signup select, #signup radio').serialize();
					
						$.ajax({
							beforeSend	: function(){
								$('#message_response_container').slideUp('fast').html("");
								$('#signup input, #signup textarea, #signup select, #signup radio').prop('disabled', true);
							},
							complete	: function(){
							},
							type	: "POST",
							url		: "<?=APP_URL?>signup_submit.php?func=<?=$enkripsi->encode('submitFormPemberi')?>",
							data	: data,
							success : function(result) {
								
								data	=	JSON.parse(result);
								if(data['respon_code'] == "000000"){
									$('#signupContainer').html(data['respon_msg']);
									$('#message_response_container').slideUp('fast').html("");
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
								
								$('#signup input, #signup textarea, #signup select, #signup radio').prop('disabled', false);
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