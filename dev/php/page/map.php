<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();

	//FUNGSI DIGUNAKAN UNTUK MENYIMPAN DATA PERUBAHAN LOKASI
	if( $enkripsi->decode($_GET['func']) == "setLokasi" && isset($_GET['func']) && $_SESSION['KursusLes']['TYPEUSER'] == 2){

		sleep(2);
		
		//AMBIL DATA POST
		$Pgps_l		=	$_POST['gps_l'];
		$Pgps_b		=	$_POST['gps_b'];
		$Pradius	=	$_POST['radius'];
		
		//UPDATE DATA LOKASI
		$sqlUpdate	=	sprintf("UPDATE m_pengajar
								 SET GPS_L	=	'%s',
								 	 GPS_B	=	'%s',
									 RADIUS	=	'%s'
								 WHERE IDPENGAJAR = %s"
								, $Pgps_l
								, $Pgps_b
								, $Pradius
								, $_SESSION['KursusLes']['IDUSER']
								);
		$affected	=	$db->execSQL($sqlUpdate, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo json_encode(array("respon_code"=>"success"));
		//JIKA GAGAL
		} else if($affected == 0){
			echo json_encode(array("respon_code"=>"null"));
		} else {
			echo json_encode(array("respon_code"=>"error"));
		}
		
		die();
		
	}
	
	$idpengajar	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;

	//QUERY SELECT DETAIL PROFILE
	$sqlDetail	=	sprintf("SELECT A.GPS_L, A.GPS_B, IFNULL(A.RADIUS, '0') * 1 AS RADIUS, B.GPS_L AS GPS_L_KOTA, B.GPS_B AS GPS_B_KOTA,
									B.NAMA_KOTA
							 FROM m_pengajar A
							 LEFT JOIN m_kota B ON A.IDKOTA_TINGGAL = B.IDKOTA
							 WHERE A.IDPENGAJAR = %s"
							, $idpengajar
							);
	$resultDetail	=	$db->query($sqlDetail);
	$resultDetail	=	$resultDetail[0];
	//HABIS -- QUERY SELECT DETAIL PROFILE
	
	$gps_l		=	$resultDetail['GPS_L'] == "" || $resultDetail['GPS_L'] == 'NULL' ? $resultDetail['GPS_L_KOTA'] : $resultDetail['GPS_L'];
	$gps_b		=	$resultDetail['GPS_B'] == "" || $resultDetail['GPS_B'] == 'NULL' ? $resultDetail['GPS_B_KOTA'] : $resultDetail['GPS_B'];
	$radius		=	$resultDetail['RADIUS'];
	$nama_kota	=	$resultDetail['NAMA_KOTA'];

	$status		=	$resultDetail['GPS_L'] == "" || $resultDetail['GPS_L'] == 'NULL' ? 0 : 1;

?>

<style>
@import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141215'.'cssmap');?>.cssfile");
</style>
<div id="map_editor">
	<div id="editor_button" style="text-align:right; margin-bottom:10px">
        <?php
        if($_SESSION['KursusLes']['TYPEUSER'] == 2 && isset($_SESSION['KursusLes']['IDUSER']) && $_SESSION['KursusLes']['IDUSER'] == $idpengajar){
		?>
        	<input type="button" name="ubahlokasi" id="ubahlokasi" value="Ubah Lokasi" onclick="showEditor('true');makeDraggable()" class="btn-xs btn btn-custom" />
		<?php
		}
		?>
    </div>
    <div id="editor_container" style="display:none">
        <div>
            Jangkauan mengajar anda
            <span> : </span>
            <select id="jangkauan" name="jangkauan" onchange="updateRadius(this.value)">
                <option value="0" <?=$radius == "0" ? "selected" : ""?>>- Belum Ditentukan -</option>
                <option value="5000" <?=$radius == "5000" ? "selected" : ""?>>5 Km</option>
                <option value="10000" <?=$radius == "10000" ? "selected" : ""?>>10 Km</option>
                <option value="15000" <?=$radius == "15000" ? "selected" : ""?>>15 Km</option>
                <option value="20000" <?=$radius == "20000" ? "selected" : ""?>>20 Km</option>
            </select>
        </div>
        <div>
            <input type="button" name="submit" id="submit" value="Batal" onclick="showEditor('false')" class="btn-xs btn btn-custom"/>
            <input type="button" name="batal" id="batal" value="Simpan" onclick="saveLocation();" class="btn-xs btn btn-custom"/>
            <input type="hidden" name="gps_l" id="gps_l" value="<?=$gps_l?>" />
            <input type="hidden" name="gps_b" id="gps_b" value="<?=$gps_b?>" />
        </div><br/>
	</div>
</div>
<div id="map_content" style="height: 650px;width: 98%;"></div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141223'.'jssmap');?>.jsfile&GPS=<?=$gps_l.",".$gps_b?>&RAD=<?=$radius?>"></script>
<script>
<?php
if($status == 0 && $_SESSION['KursusLes']['TYPEUSER'] == 2 && isset($_SESSION['KursusLes']['IDUSER']) && $_SESSION['KursusLes']['IDUSER'] == $idpengajar){
	echo "$('#message_response_container').slideDown('fast').html(generateMsg('Lokasi belum ditentukan (Kota tinggal anda sekarang : ".$nama_kota."). Silakan klik tombol ubah lokasi untuk menentukannya'));";
}

echo "setTimeout(function(){google.maps.event.addDomListener(window, 'load', initialize)}, 5000);";
?>
</script>