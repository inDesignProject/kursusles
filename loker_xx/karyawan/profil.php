<?php

	include('../php/lib/db_connection.php');
	include('../php/lib/enkripsi.php');
	include('../php/lib/session.php');
	require "../php/lib/defines.php";

	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	$idkary		=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLesLoker']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	
	//FUNGSI UPDATE TENTANG
	if( $enkripsi->decode($_GET['func']) == "saveTentang" && isset($_GET['func'])){
		
		$sqlUpd		=	sprintf("UPDATE m_karyawan SET TENTANG = '%s' WHERE IDKARYAWAN = %s"
								, $db->db_text($_POST['value'])
								, $idkary
								);
		$affected	=	$db->execSQL($sqlUpd, 0);
	
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo "00000";
		} else {
			echo "00001";
		}
		
		die();
		
	}

	//FUNGSI UPDATE STATUS INFO
	if( $enkripsi->decode($_GET['func']) == "saveStatusInfo" && isset($_GET['func'])){
		
		$idbidang	=	$_POST['bidang'] == '' ? "''" : $enkripsi->decode($_POST['bidang']);
		$idposisi	=	$_POST['posisi'] == '' ? "''" : $enkripsi->decode($_POST['posisi']);
		$idpddkan	=	$_POST['optpendidikan'] == '' ? "''" : $enkripsi->decode($_POST['optpendidikan']);
		
		$sqlUpd		=	sprintf("UPDATE m_karyawan
								 SET IDBIDANG		=	%s,
								 	 IDPOSISI		=	%s,
									 TGL_AWALKERJA	=	'%s',
									 TGL_AKHIRKERJA	=	'%s',
									 IDPENDIDIKAN	=	%s,
									 JURUSAN		=	'%s'
								 WHERE IDKARYAWAN	=	%s"
								, $idbidang
								, $idposisi
								, $_POST['tgltfrom']
								, $_POST['tgltto']
								, $idpddkan
								, $_POST['jurusan']
								, $idkary
								);
		$affected	=	$db->execSQL($sqlUpd, 0);
	
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo "00000";
		} else {
			echo "00001";
		}
		die();
		
	}

	//FUNGSI UPDATE TOP STATUS
	if( $enkripsi->decode($_GET['func']) == "saveTopInfo" && isset($_GET['func'])){
		
		$sqlUpd		=	sprintf("UPDATE m_karyawan
								 SET ALAMAT			=	'%s',
								 	 TGL_LAHIR		=	'%s',
									 JK				=	'%s',
									 TELPON			=	'%s'
								 WHERE IDKARYAWAN	=	%s"
								, $db->db_text($_POST['alamat'])
								, $_POST['tgllahir']
								, $_POST['jns_kelamin']
								, $_POST['telpon']
								, $idkary
								);
		$affected	=	$db->execSQL($sqlUpd, 0);
	
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo "00000";
		} else {
			echo "00001";
		}
		die();
		
	}

	//FUNGSI RESET PASSWORD
	if( $enkripsi->decode($_GET['func']) == "resetPassword" && isset($_GET['func'])){
		
		//CEK PANJANG PASSWORD
		if(strlen($_POST['passwordbaru1']) < 8){
			echo "00002";
			die();
		}
		
		//CEK ULANGAN PASSWORD BARU
		if($_POST['passwordbaru1'] <> $_POST['passwordbaru2']){
			echo "00001";
			die();
		}
	
		//CEK PASSWORD LAMA
		$sqlCek		=	sprintf("SELECT NAMA,EMAIL,USERNAME,PASSWORD
								 FROM m_karyawan
								 WHERE IDKARYAWAN = '%s' AND PASSWORD = '%s'"
								, $idkary
								, md5($_POST['passwordlama'])
								);
		$resultCek	=	$db->query($sqlCek);
		
		if($resultCek == false || $resultCek == ''){
			echo "00003";
			die();
		} else {
			$resultCek	=	$resultCek[0];
			$nama		=	$resultCek['NAMA'];
			$username	=	$resultCek['USERNAME'];
			$email		=	$resultCek['EMAIL'];
		}
		
		$sqlUpd		=	sprintf("UPDATE m_karyawan SET PASSWORD = MD5('%s') WHERE IDKARYAWAN = %s"
								, $_POST['passwordbaru1']
								, $idkary
								);
		$affected	=	$db->execSQL($sqlUpd, 0);
	
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			
			$message	=  "<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$nama.",<br/><br/>
								anda telah melakukan pengaturan ulang pada akun anda dengan data:<br /><br/>
								Username : ".$username."<br />
								Password : ".$_POST['passwordbaru1']."<br /><br/>
								anda dapat menggunakan password baru tersebut pada saat login kembali.<br/>
								Jika anda merasa tidak melakukan perubahan pengaturan pada akun anda, harap ubah kembali data password anda setelah login menggunakan data diatas.<br /><br /><br><br>
								
								Best regards,<br /><br />
								Admin Loker KursusLes.com
								</p>
							
							</body>
							</html>";
			$session->sendEmail($email, $nama, "Reset akun di Loker KursusLes.com", $message);
			
			echo "00000";
			die();
			
		} else {
			echo "00004";
			die();
		}
		die();
		
	}

	$sql		=	sprintf("SELECT A.NAMA, A.ALAMAT, A.TELPON, A.EMAIL, A.FOTO, A.TGL_LAHIR, A.JK, B.NAMA_POSISI, C.NAMA_BIDANG,
									D.NAMA_PENDIDIKAN, A.TGL_AWALKERJA, A.TGL_AKHIRKERJA, A.TENTANG, A.IDBIDANG, 
									A.IDPOSISI, A.IDPENDIDIKAN, A.JURUSAN
							 FROM m_karyawan A
							 LEFT JOIN m_posisi B ON A.IDPOSISI = B.IDPOSISI
							 LEFT JOIN m_bidang C ON A.IDBIDANG = C.IDBIDANG
							 LEFT JOIN m_pendidikan D ON A.IDPENDIDIKAN = D.IDPENDIDIKAN
							 WHERE A.IDKARYAWAN = %s
							 LIMIT 0,1"
							, $idkary
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	
	switch($result['JK']){
		case "L"	:	$jeniskelamin	=	"Laki-Laki"; break;
		case "P"	:	$jeniskelamin	=	"Perempuan"; break;
		default		:	$jeniskelamin	=	"Tidak Diketahui"; break;
	}
	$tentang	=	$result['TENTANG'] == "" ? "Tidak ada yang ditampilkan" : $result['TENTANG'];
	$Sidposisi	=	$enkripsi->encode($result['IDPOSISI']);
	$Sidbidang	=	$enkripsi->encode($result['IDBIDANG']);
	$Sidpddkan	=	$enkripsi->encode($result['IDPENDIDIKAN']);
	
	$Tposisi	=	$result['NAMA_POSISI'] == "" ? "- Pilih Posisi Terakhir -" : $result['NAMA_POSISI'];
	$Tbidang	=	$result['NAMA_BIDANG'] == "" ? "- Pilih Bidang Terakhir -" : $result['NAMA_BIDANG'];
	$Tpddkan	=	$result['NAMA_PENDIDIKAN'] == "" ? "- Pilih Pendidikan Terakhir -" : $result['NAMA_PENDIDIKAN'];
	
?>
<style>
	@import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141214'.'cssjqueryui');?>.cssfile");
	.form-control {
		display: inline !important;
		width: 90% !important;
		margin-bottom: 4px !important;
	}
	.editor-list{display:none}
</style>
<div class="boxSquareWhite" id="topInfo">
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" style="text-align: center;">
            <img src="<?=APP_IMG_URL?>generate_pic.php?type=kr&q=<?=$enkripsi->encode($result['FOTO'])?>&w=180&h=180" class="img-responsive img-circle img-profile" style="margin-left:auto; margin-right:auto">
            <div id="change_photo">
                <a href="#" class="btn btn-kursusles btn-sm" onclick="openWindowUploadKary()">
                    Ganti Foto
                </a>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <span class="tutor_name"><?=$result['NAMA']?></span>
            <hr>
            <div class="info">
                <div class="infolist" id="toplist">
                    <i class="fa fa-home"></i> &nbsp; <b id="text-alamat"><?=$result['ALAMAT']?></b><br/>
                    <i class="fa fa-envelope-o"></i> &nbsp; <b><?=$result['EMAIL']?></b><br/>
                    <i class="fa fa-phone"></i> &nbsp; <b id="text-telpon"><?=$result['TELPON']?></b><br/>
                    <i class="fa fa-user"></i> &nbsp; <b id="text-jk"><?=$jeniskelamin?></b><br/>
                    <i class="fa fa-calendar"></i> &nbsp; <b id="text-tgllahir"><?=$result['TGL_LAHIR'] == "" ? "Tidak Diketahui" : $result['TGL_LAHIR']?></b><br/>
                    <button class="btn btn-custom btn-xs pull-right" onclick="showTopInfo()"><i class="fa"></i>Edit Data</button>
                </div>
                <div class="infolist" id="topinfo" style="display:none">
                	<i class="fa fa-home"></i> &nbsp; <input type="text" class="form-control" id="alamat" name="alamat" maxlength="150" placeholder="Alamat lengkap" required value="<?=$result['ALAMAT']?>" style="margin-left:-2px" /><br/>
                	<i class="fa fa-phone"></i> &nbsp; <input type="text" class="form-control" id="telpon" name="telpon" maxlength="50" placeholder="Telpon" required value="<?=$result['TELPON']?>" /><br/>
                	<i class="fa fa-user"></i> &nbsp; 
                    	<input type="radio" name="jns_kelamin" value="L" <?=$result['JK'] == "L" ? "checked" : ""?> /> Laki-Laki
                        <input type="radio" name="jns_kelamin" value="P" <?=$result['JK'] == "P" ? "checked" : ""?> /> Perempuan<br/>
                	<i class="fa fa-calendar"></i> &nbsp; <input type="text" class="form-control" id="tgllahir" name="tgllahir" maxlength="10" placeholder="Tanggal Lahir" required value="<?=$result['TGL_LAHIR']?>" /><br/>
                    <button class="btn btn-custom btn-xs pull-right" onclick="saveTopInfo()" style="margin:4px"><i class="fa"></i>Simpan</button>
                    <button class="btn btn-custom btn-xs pull-right" onclick="showTopInfo()" style="margin:4px"><i class="fa"></i>Batal</button>
                </div>
            </div>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite" id="statusinfo">
    <h4><i class="fa fa-question-circle"></i> Status Terakhir Saya</h4>
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
        	Bidang Terakhir
		</div>
        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
        	<div class="text-list" id="text-bidang"><?=$result['NAMA_BIDANG'] == "" ? "Tidak Ada Data" : $result['NAMA_BIDANG']?></div>
            <div class="editor-list">
                <select id="bidang" name="bidang" class="form-control" required>
                    <option value="">- Pilih Bidang Kerja -</option>
                </select>
            </div>
        </div>
	</div>    
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
        	Posisi Terakhir
		</div>
        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
        	<div class="text-list" id="text-posisi"><?=$result['NAMA_POSISI'] == "" ? "Tidak Ada Data" : $result['NAMA_POSISI']?></div>
            <div class="editor-list">
                <select id="posisi" name="posisi" class="form-control" required>
                    <option value="">- Pilih Posisi -</option>
                </select>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
        	Tanggal Kerja
		</div>
        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
        	<div class="text-list" id="text-tanggal"><?=$result['TGL_AWALKERJA'] == "" ? "Tidak Ada Data" : $result['TGL_AWALKERJA']." s/d ".$result['TGL_AKHIRKERJA']?></div>
            <div class="editor-list">
                <input type="text" name="tgltfrom" value="<?=$result['TGL_AWALKERJA'] == '' ? '-' : $result['TGL_AWALKERJA']?>" id="tgltfrom" class="form-control" maxlength="10" autocomplete="off" readonly style="width:42% !important; display:inline !important" /> s/d 
                <input type="text" name="tgltto" value="<?=$result['TGL_AKHIRKERJA'] == '' ? '-' : $result['TGL_AKHIRKERJA']?>" id="tgltto" class="form-control" maxlength="10" autocomplete="off" readonly style="width:42% !important; display:inline !important" />
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
        	Pendidikan
		</div>
        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
        	<div class="text-list" id="text-pddkn">
				<?=$result['NAMA_PENDIDIKAN'] == "" ? "Tidak Ada Data" : $result['NAMA_PENDIDIKAN']?>
            </div>
            <div class="editor-list">
                <select id="optpendidikan" name="optpendidikan" class="form-control" required>
                    <option value="">- Pilih Minimal Pendidikan -</option>
                </select>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
        	Jurusan
		</div>
        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
        	<div class="text-list" id="text-jurusan">
				<?=$result['JURUSAN'] == "" ? "Tidak Ada Data" : $result['JURUSAN']?><br/>
            	<button id="btnperbarui" class="btn btn-custom btn-xs pull-right" onclick="showStatusInfo()"><i class="fa"></i>Perbarui</button>
            </div>
            <div class="editor-list">
                <input type="text" name="jurusan" value="<?=$result['JURUSAN'] == '' ? '-' : $result['JURUSAN']?>" id="jurusan" class="form-control" autocomplete="off" /><br/><br/>
                <button class="btn btn-custom btn-xs pull-right" onclick="showStatusInfo()"><i class="fa"></i>Batal</button>
                <button class="btn btn-custom btn-xs pull-right" onclick="saveStatusInfo()" style="margin-right:6px;"><i class="fa"></i>Simpan</button> &nbsp; 
            </div>
        </div>
	</div>
</div><hr />
<div class="boxSquareWhite">
    <h4><i class="fa fa-key"></i> Ganti Password</h4>
    <div class="row">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <form action="#" id="resetPwd" method="post">
                <div class="form-group">
                    <input type="password" name="passwordlama" id="passwordlama" class="form-control" maxlength="25" placeholder="Masukkan password lama" autocomplete="off" />
                </div>
                <div class="form-group">
                    <input type="password" name="passwordbaru1" id="passwordbaru1" class="form-control" maxlength="25" placeholder="Masukkan password baru anda" autocomplete="off" />
                </div>
                <div class="form-group">
                    <input type="password" name="passwordbaru2" id="passwordbaru2" class="form-control" maxlength="25" placeholder="Ulangi password baru anda" autocomplete="off" />
                </div>
                    
                <span class="devider"></span>
    
                <div id="button_container">
                    <input type="button" id="submit" name="submit" value="Simpan" class="btn btn-sm btn-custom2" onclick="resetPwd()" />
                </div>
            </form>
        </div>
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><b>Informasi</b></div>
                <div class="panel-body">
                    <p>
                        Kami juga akan mengirim email berisi user dan password anda sebagai pengingat.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite">
    <h4><i class="fa fa-info-circle"></i> Tentang Saya</h4>
    <div id="ptentang">
        <p id="txttentang">
            <?=$tentang?><br/><br/>
        </p>
        <input type="button" id="editTentang" name="editTentang" value="Edit" class="btn btn-sm btn-custom2" onclick="showComposerAbout()" />
    </div>
    <div id="ctentang" style="display:none">
        <div class="form-group">
            <textarea id="ttentang" name="ttentang" class="form-control" placeholder="Ceritakan tentang anda" rows="5"><?=$result['TENTANG']?></textarea>
        </div>
        <div class="form-group">
            <input type="button" name="simpan" id="simpan" value="Simpan" onclick="saveAbout()" class="btn btn-sm btn-custom2" />
            <input type="button" name="batal" id="batal" value="Batal" onclick="showComposerAbout()" class="btn btn-sm btn-custom2" />
        </div>
    </div>
</div>
<div id="dialog-confirm">
  <p id="text_dialog"></p>
</div>
<script>
	$('#tgllahir, #tgltfrom, #tgltto').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		lang:'id',
		closeOnDateSelect:true,
		scrollInput : false
	});
	getDataOpt('getDataBidang','noparam','bidang','','<?=$Tbidang?>','<?=$Sidbidang?>');
	getDataOpt('getDataPosisi','noparam','posisi','','<?=$Tposisi?>','<?=$Sidposisi?>');
	getDataOpt('getDataPendidikan','noparam','optpendidikan','','<?=$Tpddkan?>','<?=$Sidpddkan?>');
	function showTopInfo(){
		if($('#toplist').hasClass('hide')){
			$('#toplist').show().slideDown('fast');
			$('#topinfo').hide().slideUp('fast');
			$('#toplist').removeClass('hide');
		} else {
			$('#toplist').hide().slideUp('fast');
			$('#topinfo').show().slideDown('fast');
			$('#toplist').addClass('hide');
		}
		return false;
	}
	function showStatusInfo(){
		if($('.text-list').hasClass('hide')){
			$('.editor-list').slideUp('fast');
			$('.text-list').slideDown('fast');
			$('.text-list').each(function() {
			  $(this).removeClass('hide');
			});
			$('#btnperbarui').show();
		} else {
			$('.text-list').slideUp('fast');
			$('.editor-list').slideDown('fast');
			$('.text-list').each(function() {
			  $(this).addClass('hide');
			});
			$('#btnperbarui').hide();
		}
		return false;
	}
	function showComposerAbout(){
		if($('#ptentang').hasClass('hide')){
			$('#ptentang').slideDown('fast');
			$('#ctentang').slideUp('fast');
			$('#ptentang').removeClass('hide');
		} else {
			$('#ptentang').slideUp('fast');
			$('#ctentang').slideDown('fast');
			$('#ptentang').addClass('hide');
		}
		return false;
	}
	function saveAbout(){
	
		$('#message_response_container').slideDown('fast').html(generateMsg("Harap tunggu, sedang menyimpan"));
		$("#ttentang").prop('disabled', true);
	
		$.post("<?=APP_URL?>karyawan/profil.php?func=<?=$enkripsi->encode('saveTentang')?>", {value: $('#ttentang').val()})
		.done(function( data ) {
			
			$("#ttentang").prop('disabled', false);
			if(data == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
				showComposerAbout();
				$('#txttentang').html($("#ttentang").val());
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
			}
			
		});
	}
	function saveStatusInfo(){
		var data		=	$('#statusinfo input, #statusinfo textarea, #statusinfo select, #statusinfo radio').serialize();
		$('#statusinfo input, #statusinfo textarea, #statusinfo select, #statusinfo radio').prop('disabled', true);
		$.post("<?=APP_URL?>karyawan/profil.php?func=<?=$enkripsi->encode('saveStatusInfo')?>", data)
		.done(function( data ) {
			$('#statusinfo input, #statusinfo textarea, #statusinfo select, #statusinfo radio').prop('disabled', false);
			if(data == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
				showStatusInfo();
				if($("#bidang").val() != ''){$('#text-bidang').html($("#bidang option:selected").text())}else{$('#text-bidang').html('Tidak ada data')}
				if($("#posisi").val() != ''){$('#text-posisi').html($("#posisi option:selected").text())}else{$('#text-posisi').html('Tidak ada data')}
				if($("#optpendidikan").val() != ''){$('#text-pddkn').html($("#optpendidikan option:selected").text())}else{$('#text-pddkn').html('Tidak ada data')}
				if($("#jurusan").val() != ''){$('#text-jurusan').html($("#jurusan").val())}else{$('#text-jurusan').html('Tidak ada data')}
				$('#text-tanggal').html($("#tgltfrom").val()+" s/d "+$("#tgltto").val());
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
			}
		});
	}
	function saveTopInfo(){
		var data		=	$('#topInfo input, #topInfo textarea, #topInfo select, #topInfo radio').serialize();
		$('#topInfo input, #topInfo textarea, #topInfo select, #topInfo radio').prop('disabled', true);
		$.post("<?=APP_URL?>karyawan/profil.php?func=<?=$enkripsi->encode('saveTopInfo')?>", data)
		.done(function( data ) {
			$('#topInfo input, #topInfo textarea, #topInfo select, #topInfo radio').prop('disabled', false);
			if(data == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
				showTopInfo();
				if($("#alamat").val() != ''){$('#text-alamat').html($("#alamat").val())}else{$('#text-alamat').html('Tidak ada data')}
				if($("#telpon").val() != ''){$('#text-telpon').html($("#telpon").val())}else{$('#text-telpon').html('Tidak ada data')}
				if($("#tgllahir").val() != ''){$('#text-tgllahir').html($("#tgllahir").val())}else{$('#text-tgllahir').html('Tidak ada data')}
				if($('input[name="jns_kelamin"]:checked').val() == 'L'){$('#text-jk').html('Laki-laki')}else if($('input[name="jns_kelamin"]:checked').val() == 'P'){$('#text-jk').html('Perempuan')}else{$('#text-jk').html('Tidak ada data')}
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
			}
		});
	}	
	function generateMsg(msg){
		return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
				"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
				"<strong><small id='message_response'>"+msg+"</small></strong>"+
			"</div>";
	}
	function resetPwd(){

		$('#message_response_container').slideDown('fast').html(generateMsg("Harap tunggu, sedang menyimpan"));
		var sendData = $('#resetPwd input, #resetPwd textarea, #resetPwd select, #resetPwd radio, #resetPwd hidden').serialize();
		$("#resetPwd input, #resetPwd textarea").prop('disabled', true);
	
		$.post( "<?=APP_URL?>karyawan/profil.php?func=<?=$enkripsi->encode('resetPassword')?>", sendData)
		.done(function( data ) {
			
			$("#resetPwd input, #resetPwd textarea").prop('disabled', false);
	
			if(data == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Pengulangan password baru tidak sama"));
			} else if(data == '00002'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Anda harus mengisi password baru dan minimal sepanjang 8 karakter"));
			} else if(data == '00003'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Password lama yang anda masukkan tidak valid"));
			} else if(data == '00004'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data. Silakan masukkan password baru yang berbeda dari sebelumnya"));
			} else if(data == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan. Kami juga sudah mengirimkan email sebagai pengingat"));
				$("#passwordlama, #passwordbaru1, #passwordbaru2").val('');
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
			}
			
		});
	}
	
	function openWindowUploadKary(){
		$("#dialog-confirm").dialog({
			closeOnEscape: false,
			resizable: true,
			modal: true,
			minWidth: 500,
			title: "Upload File",
			position: {
				my: 'top', 
				at: 'top'
			},
			open: function() {
			  $(this).html("<object id='object_uploader' type='type/html' data='<?=APP_URL?>karyawan/upload_foto.php' width='100%' height='400px'></object>");
			},
			close: function() {
				$(this).dialog( "close" );
			},
			buttons: {
				"Simpan": function() {
					
					var objUploader	=	document.getElementById("object_uploader");
					var contentUpl	=	objUploader.contentDocument;
					var resultUpload=	contentUpl.getElementById("resultupload").value;
					var msgElem		=	contentUpl.getElementById("editor_status");
					var msgTxt		=	contentUpl.getElementById("editor_message");
					var data;
					
					if(resultUpload != 1){
						blink(msgElem, 4, 150);
					} else {
						$.post("<?=APP_URL?>karyawan/upload_foto.php?func=<?=$enkripsi->encode('setFoto')?>")
						.done(function( data ) {
							
							data			=	JSON.parse(data);
							console.log(data['respon_code']);
							if(data['respon_code'] != 1){
								msgElem.className	=	"error";
								msgTxt.innerHTML	=	data['respon_message'];
								blink(msgElem, 4, 150);
								return false;
							} else {
								window.location.href	=	'<?=APP_URL?>index_kary';
							}
						});
					}
							
					$(this).dialog("close");
	
				},
				"Batal": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	function blink(elem, times, speed) {
		if (times > 0 || times < 0) {
			if ($(elem).hasClass("blink")) {
				$(elem).removeClass("blink");
			} else {
				$(elem).addClass("blink");
			}
		}
	
		clearTimeout(function () {
			blink(elem, times, speed);
		});
	
		if (times > 0 || times < 0) {
			setTimeout(function () {
				blink(elem, times, speed);
			}, speed);
			times -= .5;
		}
	}
</script>