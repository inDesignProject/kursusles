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
	$sql		=	sprintf("SELECT PROFIL, VISI, MISI
							 FROM m_pengajar
							 WHERE IDPENGAJAR = %s"
							, $idpengajar
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	
	//FUNGSI DIGUNAKAN UNTUK MENNGIRIM PESAN DARI PENGUNJUNG
	if( $enkripsi->decode($_GET['func']) == "saveData" && isset($_GET['func'])){

		//INSERT PESAN KE DATABASE
		$sqlUpdate	=	sprintf("UPDATE m_pengajar SET %s = '%s' WHERE IDPENGAJAR = %s"
								, mysql_real_escape_string($_POST['type'])
								, mysql_real_escape_string($_POST['value'])
								, $idpengajar
								);
		$affected	=	$db->execSQL($sqlUpdate, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo json_encode(array("respon_code"=>'1'));
		//JIKA GAGAL INSERT
		} else if($affected == 0){
			echo json_encode(array("respon_code"=>'-1'));
		//JIKA GAGAL QUERY
		} else {
			echo json_encode(array("respon_code"=>'-2'));
		}
		die();
		
	}
	
?>

<style>
@import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'csspesan');?>.cssfile");
</style>
<div class="boxSquareWhite">
	<ul class="list-inline">
        <li class="icon"><img src="<?=APP_IMG_URL?>icon/profil.png" alt="profil" class="img-responsive"/></li>
        <li style="margin-left:-10px"><small><h4>Profil</h4></small></li>
		<?php
        if($_SESSION['KursusLes']['IDUSER'] == $idpengajar){
        ?>
        <li class="pull-right"><img src="<?=APP_IMG_URL?>icon/edit.png" alt="Edit profil" class="img-responsive" onclick="showComposer('profil')"/></li>
        <?php
		}
		?>
    </ul>
    <p id="pprofil">
        <?php
		if($result['PROFIL'] <> ''){
			echo $result['PROFIL'];
		} else {
			echo "<center><b>Tidak ada data yang ditampilkan</b></center>";
		}
		?>
    </p>
	<?php
    if($_SESSION['KursusLes']['IDUSER'] == $idpengajar){
		echo "<div id='cprofil' style='display:none'>
				<div class='form-group'>
					<textarea id='tprofil' name='tprofil' class='form-control' placeholder='Tulis profil anda disini' rows='5'>".$result['PROFIL']."</textarea>
				</div>
				<div class='form-group'>
					<input type='button' name='simpan' id='simpan' value='Simpan' onclick='saveData(\"profil\")' class='btn btn-sm btn-custom2' />
				</div>
			  </div>";
	}
    ?>
</div><br/>
<div class="boxSquareWhite">
	<ul class="list-inline">
        <li class="icon"><img src="<?=APP_IMG_URL?>icon/visi.png" alt="visi" class="img-responsive"/></li>
        <li style="margin-left:-10px"><small><h4>Visi</h4></small></li>
		<?php
        if($_SESSION['KursusLes']['IDUSER'] == $idpengajar){
        ?>
        <li class="pull-right"><img src="<?=APP_IMG_URL?>icon/edit.png" alt="Edit visi" class="img-responsive" onclick="showComposer('visi')"/></li>
        <?php
		}
		?>
    </ul>
    <p id="pvisi">
        <?php
		if($result['VISI'] <> ''){
			echo $result['VISI'];
		} else {
			echo "<center><b>Tidak ada data yang ditampilkan</b></center>";
		}
		?>
    </p>
	<?php
    if($_SESSION['KursusLes']['TYPEUSER'] == 2 && $_SESSION['KursusLes']['IDUSER'] == $idpengajar){
		echo "<div id='cvisi' style='display:none'>
				<div class='form-group'>
					<textarea id='tvisi' name='tvisi' class='form-control' placeholder='Tulis visi anda disini' rows='5'>".$result['VISI']."</textarea>
				</div>
				<div class='form-group'>
					<input type='button' name='simpan' id='simpan' value='Simpan' onclick='saveData(\"visi\")' class='btn btn-sm btn-custom2' />
				</div>
			  </div>";
	}
    ?>
</div><br/>
<div class="boxSquareWhite">
	<ul class="list-inline">
        <li class="icon"><img src="<?=APP_IMG_URL?>icon/misi.png" alt="misi" class="img-responsive"/></li>
        <li style="margin-left:-10px"><small><h4>Misi</h4></small></li>
		<?php
        if($_SESSION['KursusLes']['TYPEUSER'] == 2 && $_SESSION['KursusLes']['IDUSER'] == $idpengajar){
        ?>
        <li class="pull-right"><img src="<?=APP_IMG_URL?>icon/edit.png" alt="Edit misi" class="img-responsive" onclick="showComposer('misi')"/></li>
        <?php
		}
		?>
    </ul>
    <p id="pmisi">
        <?php
		if($result['MISI'] <> ''){
			echo $result['MISI'];
		} else {
			echo "<center><b>Tidak ada data yang ditampilkan</b></center>";
		}
		?>
    </p>
	<?php
    if($_SESSION['KursusLes']['TYPEUSER'] == 2 && $_SESSION['KursusLes']['IDUSER'] == $idpengajar){
		echo "<div id='cmisi' style='display:none'>
				<div class='form-group'>
					<textarea id='tmisi' name='tmisi' class='form-control' placeholder='Tulis misi anda disini' rows='5'>".$result['MISI']."</textarea>
				</div>
				<div class='form-group'>
					<input type='button' name='simpan' id='simpan' value='Simpan' onclick='saveData(\"misi\")' class='btn btn-sm btn-custom2' />
				</div>
			  </div>";
	}
    ?>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141216'.'jssprofil');?>.jsfile"></script>