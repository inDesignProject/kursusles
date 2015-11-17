<?php
	include('../php/include/enkripsi.php');
	include('../php/include/session.php');
	include('../php/lib/db_connection.php');
	include('../php/lib/recaptchalib.php');
	require "../php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);
	
	if($session->cekSession() <> 2 && !isset($_GET['func'])){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode($session->cekSession())."'</script>";
		 die();
	}
	
	//FUNGSI DIGUNAKAN SIMPAN SETTING AKUN
	if( $enkripsi->decode($_GET['func']) == "saveData" && isset($_GET['func'])){
		
		//CEK G-RECAPTCHA
		if($_POST['g-recaptcha-response'] == '' || !isset($_POST['g-recaptcha-response'])){
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Silakan cek box reCaptcha untuk melanjutkan"));
			die();
		}
		
		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'00003',"respon_msg"=>"Captcha yang anda masukkan tidak valid"));
			die();
		}
		
		//CEK KIRIMAN DATA
		if($_POST['pwlama'] <> '' || $_POST['pwbaru'] <> '' || $_POST['pwulang'] <> ''){

			if($_POST['pwlama'] == '' || !isset($_POST['pwlama'])){
				echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Harap masukkan password lama anda"));
				die();
			}

			if($_POST['pwbaru'] == '' || $_POST['pwulang'] == ''){
				echo json_encode(array("respon_code"=>"00011", "respon_msg"=>"Password baru dan pengulangan password tidak boleh kosong"));
				die();
			}
			
			if($_POST['pwbaru'] <> $_POST['pwulang']){
				echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Pengulangan password baru tidak valid. Silakan ulangi lagi"));
				die();
			}
			
			$sqlcek		=	sprintf("SELECT IDUSER FROM admin_user
									 WHERE IDUSER = %s AND PASSWORD = MD5('%s') LIMIT 0,1"
									, $_SESSION['KursusLesAdmin']['IDUSER']
									, $_POST['pwlama']
									);
			$resultcek	=	$db->query($sqlcek);
			
			if($resultcek <> '' && $resultcek <> false){
				
				$sqlupd	=	sprintf("UPDATE admin_user
									 SET PASSWORD = MD5('%s'), USERNAME = '%s'
									 WHERE IDUSER = %s"
									, $_POST['pwbaru']
									, $_POST['username']
									, $_SESSION['KursusLesAdmin']['IDUSER']
									);
				$affected	=	$db->execSQL($sqlupd, 0);
		
				//JIKA DATA SUDAH MASUK, KIRIM RESPON
				if($affected > 0){
					echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data perubahan tersimpan"));
					die();
				} else {
					echo json_encode(array("respon_code"=>"00003", "respon_msg"=>"Gagal menyimpan data, silakan coba lagi nanti".$sqlupd));
					die();
				}
				
			} else {
				echo json_encode(array("respon_code"=>"00004", "respon_msg"=>"Password lama yang anda masukkan tidak valid"));
				die();
			}
			
		} else {
			
			if($_POST['uname'] <> $_POST['username']){
				
				$sqlupd	=	sprintf("UPDATE admin_user
									 SET USERNAME = '%s'
									 WHERE IDUSER = %s"
									, $_POST['username']
									, $_SESSION['KursusLesAdmin']['IDUSER']
									);
				$affected	=	$db->execSQL($sqlupd, 0);
		
				//JIKA DATA SUDAH MASUK, KIRIM RESPON
				if($affected > 0){
					echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data perubahan tersimpan"));
					die();
				} else {
					echo json_encode(array("respon_code"=>"00005", "respon_msg"=>"Gagal menyimpan data, silakan coba lagi nanti"));
					die();
				}
				
			} else {
				echo json_encode(array("respon_code"=>"00006", "respon_msg"=>"Tidak ada perubahan data"));
				die();
			}
			
		}
		
	}

	$sqlsel		=	sprintf("SELECT USERNAME FROM admin_user WHERE IDUSER = %s LIMIT 0,1", $_SESSION['KursusLesAdmin']['IDUSER']);
	$result		=	$db->query($sqlsel);
	$result		=	$result[0];
	$username	=	$result['USERNAME'];
	
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-wrench"></i> Pengaturan</a></li>
        <li>Akun</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Atur Akun Anda</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divdata">
            <p>
                <span class="field">
                    * silakan ganti username tanpa memasukkan isian yang lain untuk mengganti username anda<br/>
                   ** Jika ingin mengubah Password harap lengkapi isian sesuai dengan instruksi 
                </span>
            </p>
            <p>
                <label>Username</label>
                <span class="field">
                	<input type="text" name="username" id="username" autocomplete="off" value="<?=$username?>" style="width:200px;" />
                    <input type="hidden" name="uname" id="uname" value="<?=$username?>" />
                </span>
            </p>
            <p>
                <label>Password Lama</label>
                <span class="field">
                	<input type="password" name="pwlama" id="pwlama" autocomplete="off" value="" style="width:300px;" />
                </span>
            </p>
            <p>
                <label>Password Baru</label>
                <span class="field">
                	<input type="password" name="pwbaru" id="pwbaru" autocomplete="off" value="" style="width:300px;" />
                </span>
            </p>
            <p>
                <label>Ulangi Password</label>
                <span class="field">
                	<input type="password" name="pwulang" id="pwulang" autocomplete="off" value="" style="width:300px;" />
                </span>
            </p>
            <p>
                <label>Centang Kotak Dibawah</label>
                <span class="field">
                    <div class="g-recaptcha" data-sitekey="<?=$siteKey?>" style="margin-left:16px"></div>
                    <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?=$lang?>"></script>
                </span>
            </p>
        </div><br/><br/>
        <div class="actionBar">
            <input name="simpan" id="simpan" class="submit radius2 pull-right" value="Simpan" type="button" onclick="saveData()">
        </div>
	</form><br/>
</div>
<script>
	function saveData(page){
		var data	=	$('#divdata input, #divdata password, #divdata textarea').serialize();

		$('#message_response_container').slideDown('fast').html(generateMsg("Sedang menyimpan"));
		$('#divdata input, #divdata password, #divdata textarea').prop('disabled', true);
		$.post( "<?=APP_URL?>page/601settingakun.php?func=<?=$enkripsi->encode('saveData')?>", data)
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				window.location.href = '<?=APP_URL?>logout?authResult=<?=$enkripsi->encode('8')?>'
			}
			$('#divdata input, #divdata password, #divdata textarea').prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			grecaptcha.reset();
			
		});
	}
</script>