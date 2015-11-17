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
	
	//FUNGSI UPDATE RESUME
	if( $enkripsi->decode($_GET['func']) == "saveResume" && isset($_GET['func'])){
		
		$idrasdec	=	$enkripsi->decode($_POST['ras']);
		$idagamadec	=	$enkripsi->decode($_POST['agama']);
		$negogaji	=	isset($_POST['ceknego']) && $_POST['ceknego'] == "1" ? "1" : "0";
		
		$sqlUpd		=	sprintf("INSERT INTO t_resume SET
								 IDKARYAWAN		=	%s,
								 STATUS_NIKAH	=	%s,
								 IDRAS			=	%s,
								 IDAGAMA		=	%s,
								 KENDARAAN		=	%s,
								 KARTU_KREDIT	=	%s,
								 STATUS_TINGGAL	=	%s,
								 JML_ANAK		=	'%s',
								 KODEPOS		=	%s,
								 TINGKAT_KERJA	=	%s,
								 GAJI_TERAKHIR	=	'%s',
								 GAJI_DIHARAPKAN=	'%s',
								 BISA_NEGO		=	%s
								 
								 ON DUPLICATE KEY UPDATE
								 
								 STATUS_NIKAH	=	%s,
								 IDRAS			=	%s,
								 IDAGAMA		=	%s,
								 KENDARAAN		=	%s,
								 KARTU_KREDIT	=	%s,
								 STATUS_TINGGAL	=	%s,
								 JML_ANAK		=	'%s',
								 KODEPOS		=	%s,
								 TINGKAT_KERJA	=	%s,
								 GAJI_TERAKHIR	=	'%s',
								 GAJI_DIHARAPKAN=	'%s',
								 BISA_NEGO		=	%s"
								, $idkary
								, $_POST['statpern']
								, $idrasdec
								, $idagamadec
								, $_POST['kendaraan']
								, $_POST['kartukredit']
								, $_POST['tempattinggal']
								, $_POST['jmlanak']
								, $_POST['kodepos']
								, $_POST['tingkatkarir']
								, $_POST['gajiterakhir']
								, $_POST['gajidiharapkan']
								, $negogaji

								, $_POST['statpern']
								, $idrasdec
								, $idagamadec
								, $_POST['kendaraan']
								, $_POST['kartukredit']
								, $_POST['tempattinggal']
								, $_POST['jmlanak']
								, $_POST['kodepos']
								, $_POST['tingkatkarir']
								, $_POST['gajiterakhir']
								, $_POST['gajidiharapkan']
								, $negogaji
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

	$sql		=	sprintf("SELECT NAMA, ALAMAT, TELPON, EMAIL, TGL_LAHIR, JK, TENTANG
							 FROM m_karyawan
							 WHERE IDKARYAWAN = %s
							 LIMIT 0,1"
							, $idkary
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	$tentang	=	$result['TENTANG'] == "" ? "Tidak ada yang ditampilkan" : $result['TENTANG'];
	
	switch($result['JK']){
		case "L"	:	$jeniskelamin	=	"Laki-Laki"; break;
		case "P"	:	$jeniskelamin	=	"Perempuan"; break;
		default		:	$jeniskelamin	=	"Tidak Diketahui"; break;
	}
	
	$sqlresume	=	sprintf("SELECT A.STATUS_NIKAH, A.IDRAS, B.NAMA_RAS, A.IDAGAMA, C.NAMA_AGAMA,
									A.KENDARAAN, A.KARTU_KREDIT, A.STATUS_TINGGAL, A.JML_ANAK, 
									A.KODEPOS, A.TINGKAT_KERJA, A.GAJI_TERAKHIR, A.GAJI_DIHARAPKAN, A.BISA_NEGO
							 FROM t_resume A
							 LEFT JOIN m_ras B ON A.IDRAS = B.IDRAS
							 LEFT JOIN m_agama C ON A.IDAGAMA = C.IDAGAMA
							 WHERE A.IDKARYAWAN = %s
							 LIMIT 0,1"
							, $idkary
							);
	$resultres	=	$db->query($sqlresume);
	$resultres	=	$resultres[0];
	
	$idras		=	$enkripsi->encode($resultres['IDRAS']);
	$idagama	=	$enkripsi->encode($resultres['IDAGAMA']);
	
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
<div class="boxSquareWhite">
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <span class="tutor_name"><?=$result['NAMA']?></span>
            <hr>
            <div class="info">
                <div class="infolist">
                    <i class="fa fa-home"></i> &nbsp; <b><?=$result['ALAMAT']?></b><br/>
                    <i class="fa fa-envelope-o"></i> &nbsp; <b><?=$result['EMAIL']?></b><br/>
                    <i class="fa fa-phone"></i> &nbsp; <b><?=$result['TELPON']?></b><br/>
                    <i class="fa fa-user"></i> &nbsp; <b><?=$jeniskelamin?></b><br/>
                    <i class="fa fa-calendar"></i> &nbsp; <b><?=$result['TGL_LAHIR'] == "" ? "Tidak Diketahui" : $result['TGL_LAHIR']?></b><br/>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><b>Informasi</b></div>
                <div class="panel-body">
                    <p>
                        Anda dapat mengubah data profil dengan masuk ke tab <b>Profil</b> dan melengkapi data isiannya.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite" id="resumeList">
    <h4><i class="fa fa-user"></i> Keterangan Pribadi</h4>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Status Pernikahan</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <select id="statpern" name="statpern" class="form-control" required>
                    <option value="">- Pilih Status Pernikahan -</option>
                    <option value="0" <?=$resultres['STATUS_NIKAH'] == "0" ? "selected" : ""?>>Lajang</option>
                    <option value="1" <?=$resultres['STATUS_NIKAH'] == "1" ? "selected" : ""?>>Menikah</option>
                    <option value="2" <?=$resultres['STATUS_NIKAH'] == "2" ? "selected" : ""?>>Berpisah</option>
                    <option value="3" <?=$resultres['STATUS_NIKAH'] == "3" ? "selected" : ""?>>Cerai</option>
                    <option value="4" <?=$resultres['STATUS_NIKAH'] == "4" ? "selected" : ""?>>Janda / Duda</option>
                    <option value="5" <?=$resultres['STATUS_NIKAH'] == "5" ? "selected" : ""?>>Tidak dijelaskan</option>
                </select><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Ras</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <select id="ras" name="ras" class="form-control" required>
                    <option value="">- Pilih Ras -</option>
                </select><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Agama</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <select id="agama" name="agama" class="form-control" required>
                    <option value="">- Pilih Agama -</option>
                </select><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Kendaraan</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <select id="kendaraan" name="kendaraan" class="form-control" required>
                    <option value="">- Pilih Kepemilikan Kendaraan -</option>
                    <option value="0" <?=$resultres['KENDARAAN'] == "0" ? "selected" : ""?>>Tidak Ada</option>
                    <option value="1" <?=$resultres['KENDARAAN'] == "1" ? "selected" : ""?>>Speda Motor</option>
                    <option value="2" <?=$resultres['KENDARAAN'] == "2" ? "selected" : ""?>>Mobil</option>
                </select><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Kartu Kredit</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <select id="kartukredit" name="kartukredit" class="form-control" required>
                    <option value="">- Pilih Kartu Kredit -</option>
                    <option value="0" <?=$resultres['KARTU_KREDIT'] == "0" ? "selected" : ""?>>Tidak Ada</option>
                    <option value="1" <?=$resultres['KARTU_KREDIT'] == "1" ? "selected" : ""?>>Classic</option>
                    <option value="2" <?=$resultres['KARTU_KREDIT'] == "2" ? "selected" : ""?>>Gold</option>
                    <option value="3" <?=$resultres['KARTU_KREDIT'] == "3" ? "selected" : ""?>>Platinum</option>
                </select><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Tempat Tinggal</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <select id="tempattinggal" name="tempattinggal" class="form-control" required>
                    <option value="">- Pilih Tempat Tinggal -</option>
                    <option value="1" <?=$resultres['STATUS_TINGGAL'] == "1" ? "selected" : ""?>>Sewa</option>
                    <option value="2" <?=$resultres['STATUS_TINGGAL'] == "2" ? "selected" : ""?>>Mortgaged</option>
                    <option value="3" <?=$resultres['STATUS_TINGGAL'] == "3" ? "selected" : ""?>>Properti Tanpa Hipotik</option>
                    <option value="4" <?=$resultres['STATUS_TINGGAL'] == "4" ? "selected" : ""?>>Quarters</option>
                    <option value="5" <?=$resultres['STATUS_TINGGAL'] == "5" ? "selected" : ""?>>Bersama Orang Tua</option>
                    <option value="6" <?=$resultres['STATUS_TINGGAL'] == "6" ? "selected" : ""?>>Lainnya</option>
                </select><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Jumlah Anak</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <input type="text" name="jmlanak" value="<?=$resultres['JML_ANAK']?>" id="jmlanak" class="form-control" maxlength="2" autocomplete="off" style="text-align:right" /><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Kode Pos</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <input type="text" name="kodepos" value="<?=$resultres['KODEPOS']?>" id="kodepos" class="form-control" maxlength="5" autocomplete="off" style="text-align:right" /><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Tingkat Karir</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <select id="tingkatkarir" name="tingkatkarir" class="form-control" required>
                    <option value="">- Pilih Tingkat Karir -</option>
                    <option value="1" <?=$resultres['TINGKAT_KERJA'] == "1" ? "selected" : ""?>>Awal</option>
                    <option value="2" <?=$resultres['TINGKAT_KERJA'] == "2" ? "selected" : ""?>>Pertengahan</option>
                    <option value="3" <?=$resultres['TINGKAT_KERJA'] == "3" ? "selected" : ""?>>Senior</option>
                    <option value="4" <?=$resultres['TINGKAT_KERJA'] == "4" ? "selected" : ""?>>Top</option>
                </select><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Gaji Terakhir</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <input type="text" name="gajiterakhir" value="<?=$resultres['GAJI_TERAKHIR']?>" id="gajiterakhir" class="form-control" maxlength="11" autocomplete="off" style="text-align:right" /><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">Gaji Diharapkan</div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <input type="text" name="gajidiharapkan" value="<?=$resultres['GAJI_DIHARAPKAN']?>" id="gajidiharapkan" class="form-control" maxlength="11" autocomplete="off" style="text-align:right" /><br/>
            </div>
        </div>
	</div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12"></div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div>
                <input name="ceknego" id="ceknego" value="1" <?=$resultres['BISA_NEGO'] == "1" ? "checked" : ""?> type="checkbox"> Bisa Nego<br/>
                <button class="btn btn-custom btn-xs pull-right" onclick="saveResume()" style="padding: 4px"><i class="fa"></i>Simpan</button>
            </div>
        </div>
	</div>
</div><hr>
<div class="boxSquareWhite">
    <h4><i class="fa fa-info-circle"></i> Tentang Saya</h4>
    <div>
        <p>
            <?=$tentang?><br/><br/>
        </p>
    </div>
</div>
<script>
	getDataOpt('getDataRas','noparam','ras','','<?=$resultres['NAMA_RAS'] == "" ? "- Pilih Ras -" : $resultres['NAMA_RAS']?>','<?=$idras?>');
	getDataOpt('getDataAgama','noparam','agama','','<?=$resultres['NAMA_AGAMA'] == "" ? "- Pilih Agama -" : $resultres['NAMA_AGAMA']?>','<?=$idagama?>');
	function saveResume(){
		
		var data		=	$('#resumeList input, #resumeList select, #resumeList checkbox').serialize();
		$('#resumeList input, #resumeList select, #resumeList checkbox').prop('disabled', true);
		
		$.post("<?=APP_URL?>karyawan/resume.php?func=<?=$enkripsi->encode('saveResume')?>", data)
		.done(function( data ) {
			$('#resumeList input, #resumeList select, #resumeList checkbox').prop('disabled', false);
			if(data == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
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
</script>