<?php

include('../lib/db_connection.php');
include('../lib/enkripsi.php');
include('../lib/session.php');
require "../lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

session_start();
$idmurid	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
$sqlMurid	=	sprintf("SELECT A.NAMA, CONCAT(A.ALAMAT, ' Kec. ', C.NAMA_KECAMATAN, ' - ', D.NAMA_KOTA) AS ALAMAT_LENGKAP,
								A.TELPON, A.EMAIL, A.FOTO, A.TENTANG
						 FROM m_murid A
						 LEFT JOIN m_kelurahan B ON A.IDKELURAHAN = B.IDKELURAHAN
						 LEFT JOIN m_kecamatan C ON A.IDKECAMATAN = C.IDKECAMATAN
						 LEFT JOIN m_kota D ON A.IDKOTA_TINGGAL = D.IDKOTA
						 WHERE A.IDMURID = %s"
						, $idmurid
						);
$resultMurid	=	$db->query($sqlMurid);
$resultMurid	=	$resultMurid[0];

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
	$sqlCek		=	sprintf("SELECT A.USERNAME, B.NAMA, B.EMAIL
							 FROM m_user A
							 LEFT JOIN m_murid B ON A.IDUSER_CHILD = B.IDMURID
							 WHERE A.IDUSER = %s AND A.PASSWORD = '%s'"
							, $_SESSION['KursusLes']['IDPRIME']
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
	
	$sqlUpd		=	sprintf("UPDATE m_user SET PASSWORD = MD5('%s') WHERE IDUSER = %s"
							, $_POST['passwordbaru1']
							, $_SESSION['KursusLes']['IDPRIME']
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
							Admin KursusLes.com
							</p>
						
						</body>
						</html>";
		$session->sendEmail($email, $nama, "Reset akun di KursusLes.com", $message);
		
		echo "00000";
		die();
		
	} else {
		echo "00004";
		die();
	}
	die();
	
}

//FUNGSI UPDATE PROFIL
if( $enkripsi->decode($_GET['func']) == "saveProfil" && isset($_GET['func'])){
	
	$sqlUpd		=	sprintf("UPDATE m_murid SET TENTANG = '%s' WHERE IDMURID = %s"
							, $db->db_text($_POST['value'])
							, $_SESSION['KursusLes']['IDUSER']
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

?>
<style>
	@import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141214'.'cssjqueryui');?>.cssfile");
</style>
<div class="boxSquareWhite">
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" style="text-align: center;">
            <img src="<?=APP_IMG_URL?>generate_pic.php?type=mr&q=<?=$enkripsi->encode($resultMurid['FOTO'])?>&w=180&amp;h=180" class="img-responsive img-circle img-profile" style="margin-left:auto; margin-right:auto">
            <div id="change_photo">
                <a href="#" class="btn btn-kursusles btn-sm" onclick="openWindowUploadMurid()">
                    Ganti Foto
                </a>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <span class="tutor_name"><?=$resultMurid['NAMA']?></span>
            <hr>
            <div class="info">
                <div class="infolist">
                    <ul class="list-inline">
                        <li class="address">
                            <small><?=$resultMurid['ALAMAT_LENGKAP']?></small>
                        </li>
                    </ul>
                    <ul class="list-inline">
                        <li class="materi">
                            <small><?=$resultMurid['EMAIL']?></small>
                        </li>
                    </ul>
                    <ul class="list-inline">
                        <li class="level">
                            <small><?=$resultMurid['TELPON']?></small>
                        </li>
                    </ul>
                </div>
            </div>
            <button class="btn btn-custom btn-xs pull-right" onclick="window.location.href='editdata?q=<?=$_GET['q']?>&r=index_murid'"><i class="fa"></i>Edit Data Saya</button>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite">
    <h4>Ganti Password</h4>
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
    <h4>Tentang Saya</h4>
    <div id="ptentang">
        <p id="txttentang">
            <?=$resultMurid['TENTANG'] == "" ? "<center>Tidak ada yang ditampilkan</center>" : $resultMurid['TENTANG']?><br/><br/>
        </p>
        <input type="button" id="editTentang" name="editTentang" value="Edit" class="btn btn-sm btn-custom2" onclick="showComposer()" />
    </div>
    <div id="ctentang" style="display:none">
        <div class="form-group">
            <textarea id="ttentang" name="ttentang" class="form-control" placeholder="Ceritakan tentang anda" rows="5"><?=$resultMurid['TENTANG']?></textarea>
        </div>
        <div class="form-group">
            <input type="button" name="simpan" id="simpan" value="Simpan" onclick="editAbout()" class="btn btn-sm btn-custom2" />
            <input type="button" name="batal" id="batal" value="Batal" onclick="showComposer()" class="btn btn-sm btn-custom2" />
        </div>
    </div>
</div>
<div id="dialog-confirm">
  <p id="text_dialog"></p>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141217'.'jssmprofil');?>.jsfile"></script>