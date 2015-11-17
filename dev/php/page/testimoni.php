<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	
	$idpengajar	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	$sql		=	sprintf("SELECT A.RATE, A.DATE_RATE, A.COMMENT, B.NAMA AS NAMA_MURID, B.FOTO, B.EMAIL
							 FROM t_rating A
							 LEFT JOIN m_murid B ON A.IDMURID = B.IDMURID
							 WHERE A.IDPENGAJAR = %s
							 ORDER BY A.DATE_RATE DESC"
							, $idpengajar
							);
	$result		=	$db->query($sql);

	//FUNGSI DIGUNAKAN UNTUK MENNGIRIM TESTI DARI PENGUNJUNG
	if( $enkripsi->decode($_GET['func']) == "sendTesti" && isset($_GET['func'])){

		//AMBIL DATA POST
		$testi		=	$db->db_text($_POST['testi']);
		$rate		=	$db->db_text($_POST['rate']);
		$idpengajar	=	$db->db_text($enkripsi->decode($_POST['idpengajar']));

		$arrField	=	!isset($_POST['rate']) || ($_POST['rate'] * 1) < 0 ? array('rate') : array();
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
				} else {
					$respon_code	=	0;
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
		
		//INSERT PESAN KE DATABASE
		$sqlInsert	=	sprintf("INSERT INTO t_rating
								 SET IDPENGAJAR	=	'%s',
								 	 IDMURID	=	'%s',
									 RATE		=	'%s',
									 DATE_RATE	=	'%s',
									 COMMENT	=	'%s'
								 ON DUPLICATE KEY UPDATE
								 	 RATE		=	'%s',
									 DATE_RATE	=	'%s',
									 COMMENT	=	'%s'"
								, $idpengajar
								, $_SESSION['KursusLes']['IDUSER']
								, $rate
								, $now
								, $testi

								, $rate
								, $now
								, $testi
								);
		$affected	=	$db->execSQL($sqlInsert, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			
			$fotoEncode	=	$enkripsi->encode($_SESSION['KursusLes']['IDPRIME']).".jpg";
			switch($rate){
				case 1	:	$rating_desc	=	"Sangat Buruk"; break;
				case 2	:	$rating_desc	=	"Buruk"; break;
				case 3	:	$rating_desc	=	"Cukup"; break;
				case 4	:	$rating_desc	=	"Baik"; break;
				case 5	:	$rating_desc	=	"Sangat Baik"; break;
				default	:	$rating_desc	=	"Tidak diketahui"; break;
			}
			
			echo json_encode(array("respon_code"=>$respon_code,
								   "respon_msg"=>"Pesan terkirim",
								   "rate"=>$rate,
								   "rate_desc"=>$rating_desc,
								   "testi"=>$testi,
								   "waktu"=>$tgl_kirim,
								   "foto_user"=>$enkripsi->encode($fotoEncode),
								   "nama_murid"=>$_SESSION['KursusLes']['NAMA']
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
@import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141215'.'csstesti');?>.cssfile");
</style>
<br clear="all">
<?php
	if(isset($_SESSION['KursusLes']['IDPRIME'])){
?>
    <form id="composeTesti_container" method="POST" action="#" style="display:none">
        <div class="form-group">
            <textarea required class="form-control" placeholder="Tulis testimonial disini" rows="5" name="testi" id="testi" ></textarea>
            <span id="testimsg_testi" class="testimsg_input"></span>
        </div>
        <div class="inputTesti_container">
            <span class="inputTesti_label">Rating</span><br/>
            <div id="rating">
                <input type="radio" name="star" class="star-1" id="star-1" value="1" <? if($resultDetail['RATING'] <= 1 && $resultDetail['RATING'] <> 0 && $resultDetail['RATING'] <> '') echo "checked" ?>/>
                <label class="star-1" for="star-1">1</label>
                <input type="radio" name="star" class="star-2" id="star-2" value="2" <? if($resultDetail['RATING'] > 1 && $resultDetail['RATING'] <= 2 && $resultDetail['RATING'] <> 0 && $resultDetail['RATING'] <> '') echo "checked" ?>/>
                <label class="star-2" for="star-2">2</label>
                <input type="radio" name="star" class="star-3" id="star-3" value="3" <? if($resultDetail['RATING'] > 2 && $resultDetail['RATING'] <= 3 && $resultDetail['RATING'] <> 0 && $resultDetail['RATING'] <> '') echo "checked" ?>/>
                <label class="star-3" for="star-3">3</label>
                <input type="radio" name="star" class="star-4" id="star-4" value="4" <? if($resultDetail['RATING'] > 3 && $resultDetail['RATING'] <= 4 && $resultDetail['RATING'] <> 0 && $resultDetail['RATING'] <> '') echo "checked" ?>/>
                <label class="star-4" for="star-4">4</label>
                <input type="radio" name="star" class="star-5" id="star-5" value="5" <? if($resultDetail['RATING'] > 4 && $resultDetail['RATING'] <> 0 && $resultDetail['RATING'] <> '') echo "checked" ?>/>
                <label class="star-5" for="star-5">5</label>
                <span></span>
            </div>
            <span id="testimsg_rate" class="testimsg_input"></span>
        </div>
        <div class="inputTesti_container" style="text-align:right">
            <div id="testimsg_process" class="hide">Harap tunggu, sedang menyimpan...</div>
            <input type="button" name="kirim" id="kirim" value="Kirim Testimonial / Review" onclick="saveTesti()" class="btn btn-sm btn-custom2" />
        </div>
    </form>
    <div id="showButton_Testicontainer" class="hide_Testicontainer" onclick="showComposerTesti(this.id)" style="text-align:left">
        <input type="button" name="kirimtesti" id="kirimtesti" value="Tulis Testimonial" class="btn btn-sm btn-custom2" />
        <input type="button" name="tutuptesti" id="tutuptesti" value="Tutup Form Testimonial" style="display:none" class="btn btn-sm btn-custom2" />
        <input type="hidden" name="idpengajar" id="idpengajar" value="<?=$_GET['q']?>" />
    </div><br/>
<?php
} else {
?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong><small>Anda harus login sebagai murid untuk memberi testimonial/review kepada pengajar ini.</small></strong>
    </div>
<?php
}
?>
<div id="testi_container">
    <?php
    if($result <> false){
        foreach($result as $key){
    ?>
    <hr></hr>
    <div class="listPesan">
        <div class="media">
            <div class="media-body">
                <h5 class="media-heading">
                	<strong><?=$key['NAMA_MURID']?> <small>- <i class="fa fa-envelope"></i> <?=$key['EMAIL']?></small></strong>
                    <small>
                    	| User rating: 
                        <span class="text-gold">
                        	<?php
								for($i = 1; $i<=$key['RATE']; $i++){
									echo "<i class='fa fa-star'></i>";
								}
							?>
                        </span>
                    </small>
                </h5>
                <p>
                    <small><?=$key['COMMENT']?></small>
                </p>
                <small><i class="fa fa-calendar"></i> <?=date("Y-m-d",strtotime($key['DATE_RATE']))?> &nbsp;&nbsp;<i class="fa fa-clock-o"></i> <?=date("H:i:s",strtotime($key['DATE_RATE']))?></small>
            </div>
        </div>
    </div>
    <?php
        }
    } else {
        echo "<div class='testi_child no-msg'>
                <div id='message_list'>
					<div class='message no-msg'>
						<center><b>Tidak ada testimonial yang ditampilkan.</b></center>
					</div>
				</div>
              </div>";			
    }
    ?>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141216'.'jsstestimoni');?>.jsfile"></script>