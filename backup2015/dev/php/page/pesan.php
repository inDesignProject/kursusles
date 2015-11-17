<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	require_once "../lib/recaptchalib.php";
	$siteKey	=	GOOGLE_RECAPTCHA_SITEKEY;
	$secret		=	GOOGLE_RECAPTCHA_SECRET;
	$lang		=	GOOGLE_RECAPTCHA_LANG;
	$resp		=	null;
	$error		=	null;
	$reCaptcha	=	new ReCaptcha($secret);

	$idpengajar	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	$cekData	=	$idpengajar <> "" || $idpengajar <> "null" || isset($idpengajar) ? true : false;
	$sql		=	sprintf("SELECT NAMA_PENGIRIM,EMAIL,PESAN,TGL_PESAN,ISREAD
							 FROM t_pesan
							 WHERE IDUSER = %s AND TYPE = 1
							 ORDER BY TGL_PESAN DESC
							 LIMIT 0,5"
							, $idpengajar
							);
	$result		=	$db->query($sql);
	
	//FUNGSI DIGUNAKAN UNTUK MENNGIRIM PESAN DARI PENGUNJUNG
	if( $enkripsi->decode($_GET['func']) == "sendMessage" && isset($_GET['func'])){

		//AMBIL DATA POST
		$nama		=	$db->db_text($_POST['nama']);
		$email		=	$db->db_text($_POST['email']);
		$pesan		=	$db->db_text($_POST['pesan']);
		$idpengajar	=	$db->db_text($enkripsi->decode($_POST['idpengajar']));
		$arrField	=	array();
		$respon_code=	'';
		$now		=	date("Y-m-d H:i:s");
		$tgl_kirim	=	date("d-m-Y H:i:s",strtotime($now));
		
		//CEK JIKA ADA DATA KIRIMAN YANG KOSONG
		foreach($_POST as $key => $value){
			if($value == ''){
				$respon_code	=	0;
				array_push($arrField,$key);
			} else {
				if(count($arrField) == 0){
					$respon_code	=	1;
				}
			}
		}
		
		//JIKA ADA ERROR, KIRIM RESPON JSON
		if($respon_code <> 1){
			echo json_encode(array("respon_code"=>$respon_code,
								   "respon_msg"=>"Harap isi data dengan lengkap",
								   "arrField"=>$arrField)
						    );
			die();
		}
		
		//JIKA CAPTCHA TIDAK VALID
		if($session->cekGCaptcha($_POST['g-recaptcha-response']) <> 2){
			echo json_encode(array("respon_code"=>'-3',
								   "respon_msg"=>"",
								   "arrField"=>"")
						    );
			die();
		}
		
		//INSERT PESAN KE DATABASE
		$sqlInsert	=	sprintf("INSERT INTO t_pesan (IDUSER,NAMA_PENGIRIM,EMAIL,PESAN,TGL_PESAN,TYPE)
								 VALUES (%s,'%s','%s','%s',NOW(),1)"
								, $idpengajar
								, $nama
								, $email
								, $pesan
								);
		$affected	=	$db->execSQL($sqlInsert, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo json_encode(array("respon_code"=>$respon_code,
								   "respon_msg"=>"Pesan terkirim",
								   "nama"=>$nama,
								   "email"=>$email,
								   "pesan"=>$pesan,
								   "waktu"=>$tgl_kirim,
								   )
							  );
		//JIKA GAGAL INSERT
		} else if($affected == 0){
			echo json_encode(array("respon_code"=>'-1',
								   "respon_msg"=>"Gagal mengirim pesan, silakan coba lagi nanti",
								   "arrField"=>"")
							  );
		//JIKA GAGAL QUERY
		} else {
			echo json_encode(array("respon_code"=>'-2',
								   "respon_msg"=>"Error lainnya. Silakan coba lagi nanti",
								   "arrField"=>"")
							  );
		}
		die();
		
	}
	
?>

<style>
@import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'csspesan');?>.cssfile");
</style>
<div id="pesan_container">
	<?php
    if($_SESSION['KursusLes']['TYPEUSER'] != 2 && $_SESSION['KursusLes']['IDUSER'] != $idpengajar){
    ?>
	<div id="compose_container" style="display:none">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="form-group">
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama lengkap" required />
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="form-group">
                    <input type="text" id="email" name="email" class="form-control" placeholder="Alamat email" required />
                </div>
            </div>
        </div>
        <div class="form-group">
            <textarea id="pesan" name="pesan" required class="form-control" placeholder="Tulis pesan disini" rows="5"></textarea>
        </div>
        <div class="form-group">
            <div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
            <script type="text/javascript" src="<?=GOOGLE_RECAPTCHA_URL?>api.js?hl=<?php echo $lang;?>"></script>
        </div>
        <div class="input_container" style="text-align:right">
        	<div id="msg_process" class="hide">Harap tunggu, sedang mengirim...</div>
            <input type="text" name="idpengajar" id="idpengajar" value="<?=$_GET['q']?>" style="display:none" />
            <input type="button" name="kirim" id="kirim" value="Kirim" onclick="savePesan()" class="btn btn-sm btn-custom2" />
        </div>
    </div>
    <div id="showButton_container" class="hide_container" onclick="showComposer(this.id)" >
    	<input type="button" name="kirimpesan" id="kirimpesan" value="Kirim Pesan" class="btn btn-sm btn-custom2"/>
    	<input type="button" name="tutuppesan" id="tutuppesan" value="Tutup Pesan" style="display:none" class="btn btn-sm btn-custom2" />
        <div class="disclaimer">
            Penerima pesan akan menerima notifikasi secara instan lewat email dan sms mengenai pesan anda. Admin juga akan secara manual memonitor pesan anda.
            Lindungi privasi anda. Tidak diperkenankan untuk saling memberikan nomor kontak, email maupun jenis data koneksi lainnya diluar sepengetahuan kursusles.com. Melanggar ketentuan ini akan menyebabkan <strong>ban permanen</strong> dari kami.
            Yuk lindungi privasi dan reputasi kita dengan lebih baik :)
        </div>
        <hr>
    </div>
	<?php
	}
    ?>
    <div id="message_list">
		<?php
        if($cekData == true && $result <> false){
            foreach($result as $key){
            ?>
                <div class="listPesan">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="media-heading"><strong><?=$key['NAMA_PENGIRIM']?> <small>- <i class="fa fa-envelope"></i> <?=$key['EMAIL']?></small></strong></h5>
                            <p>
                                <small><?=$key['PESAN']?></small>
                            </p>
                            <small><i class="fa fa-calendar"></i> <?=date("d-m-Y",strtotime($key['TGL_PESAN']))?> &nbsp;&nbsp;<i class="fa fa-clock-o"></i> <?=date("H:i:s",strtotime($key['TGL_PESAN']))?></small>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
        ?>
        	<div class="message no-msg">
                <center><b>Tidak ada pesan yang ditampilkan.</b></center>
            </div>
        <?php
		}
		?>
        <div class="text-center">
            <?php
			if($_SESSION['KursusLes']['TYPEUSER'] == 2 && $_SESSION['KursusLes']['IDUSER'] == $idpengajar){
			?>
            <a href="<?=APP_URL?>msg_center?r=pengajar_profil" class="btn btn-custom btn-xs">Lihat Semua</a>
            <?php
			}
			?>
        </div>
	</div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141221'.'jsspesan');?>.jsfile"></script>