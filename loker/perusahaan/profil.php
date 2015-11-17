<?php

	include('../php/lib/db_connection.php');
	include('../php/lib/enkripsi.php');
	include('../php/lib/session.php');
	require "../php/lib/defines.php";

	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	if($_GET['q'] == '' && !isset($_GET['q'])){
		if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 1){
			echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
			die();
		} else {
			$idpers	=	$_SESSION['KursusLesLoker']['IDUSER'];
		}
	} else {
		$idpers		=	$enkripsi->decode($_GET['q']);
	}

	//FUNGSI UPDATE PROFIL
	if( $enkripsi->decode($_GET['func']) == "saveProfil" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				$respon_msg	=	'';
				switch($key){
					case "namapers"		:	$respon_msg	=	"Harap isi nama perusahaan"; break;
					case "alamat"		:	$respon_msg	=	"Harap isi alamat kantor"; break;
					case "propinsi"		:	$respon_msg	=	"Harap pilih propinsi"; break;
					case "kota"			:	$respon_msg	=	"Harap pilih kota"; break;
					case "jnsusaha"		:	$respon_msg	=	"Harap pilih jenis usaha perusahaan"; break;
				}

				if($respon_msg <> ''){
					echo json_encode(array("respon_code"=>"00001", "respon_msg"=>$respon_msg));
					die();
				}
		
			} else {
				${$key}		=	str_replace("'","",$value);
			}
		}

		$idpropdec	=	$enkripsi->decode($propinsi);
		$idkotadec	=	$enkripsi->decode($kota);
		$idusahadec	=	$enkripsi->decode($jnsusaha);
		
		$sqlUpd		=	sprintf("UPDATE m_perusahaan SET
								 NAMA_PERUSAHAAN	=	'%s',
								 ALAMAT_KANTOR		=	'%s',
								 IDPROPINSI			=	'%s',
								 IDKOTA				=	'%s',
								 KODEPOS			=	'%s',
								 TELPON				=	'%s',
								 WEBSITE			=	'%s',
								 JENIS_USAHA		=	'%s',
								 TENTANG			=	'%s',
								 TGL_BERDIRI		=	'%s'
								 WHERE IDPERUSAHAAN	=	%s"
								, $db->db_text($_POST['namapers'])
								, $db->db_text($_POST['alamat'])
								, $idpropdec
								, $idkotadec
								, $_POST['kodepos']
								, $_POST['telpon']
								, $_POST['website']
								, $idusahadec
								, $db->db_text($_POST['tentang'])
								, $tglberdiri
								, $idpers
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

	$sql		=	sprintf("SELECT A.NAMA_PERUSAHAAN, A.ALAMAT_KANTOR, B.NAMA_PROPINSI, C.NAMA_KOTA,
									A.KODEPOS, A.TELPON, A.EMAIL, A.WEBSITE, D.NAMA_USAHA, A.IDPROPINSI,
									A.IDKOTA, A.JENIS_USAHA, A.TENTANG, A.TGL_BERDIRI
							 FROM m_perusahaan A
							 LEFT JOIN m_propinsi B ON A.IDPROPINSI = B.IDPROPINSI
							 LEFT JOIN m_kota C ON A.IDKOTA = C.IDKOTA
							 LEFT JOIN m_jenis_usaha D ON A.JENIS_USAHA = D.IDUSAHA
							 WHERE A.IDPERUSAHAAN = %s
							 LIMIT 0,1"
							, $idpers
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	$idpropinsi	=	$enkripsi->encode($result['IDPROPINSI']);
	$idkota		=	$enkripsi->encode($result['IDKOTA']);
	$idusaha	=	$enkripsi->encode($result['JENIS_USAHA']);
	
?>
<div class="boxSquareWhite">
    <h4><i class="fa fa-info-circle"></i> Data Perusahaan</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="formData">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Nama Perusahaan</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="namapers" id="namapers" class="form-control" maxlength="75" value="<?=$result['NAMA_PERUSAHAAN']?>" placeholder="Nama Perusahaan" autocomplete="off"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Alamat</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="alamat" id="alamat" class="form-control" maxlength="150" value="<?=$result['ALAMAT_KANTOR']?>" placeholder="Alamat Perusahaan" autocomplete="off"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Propinsi</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <select id="propinsi" name="propinsi" class="form-control" required onchange="getDataOpt('getDataKota','propinsi='+$('#propinsi').val(),'kota','','- Pilih Kota -')">
                            <option value="">- Pilih Propinsi -</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Kota</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <select id="kota" name="kota" class="form-control" required>
                            <option value="">- Pilih Kota -</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Kode Pos</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="kodepos" id="alamat" class="form-control" maxlength="5" value="<?=$result['KODEPOS']?>" placeholder="Kode Pos Persusahaan" autocomplete="off"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Telpon</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="telpon" id="telpon" class="form-control" maxlength="50" value="<?=$result['TELPON']?>" placeholder="Telpon Persusahaan" autocomplete="off"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Website</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="website" id="website" class="form-control" maxlength="75" value="<?=$result['WEBSITE']?>" placeholder="Website Persusahaan" autocomplete="off"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Jenis Usaha</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <select id="jnsusaha" name="jnsusaha" class="form-control" required>
                            <option value="">- Pilih Jenis Usaha -</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Tanggal Berdiri</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="tglberdiri" id="tglberdiri" class="form-control" maxlength="10" value="<?=$result['TGL_BERDIRI']?>" readonly autocomplete="off"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">Tentang Perusahaan</div>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="form-group">
                    	<textarea id="tentang" name="tentang" maxlength="500" class="form-control" placeholder="Tentang perusahaan anda" required><?=$result['TENTANG']?></textarea><br/>
						<button class="btn btn-custom btn-xs pull-right" onclick="saveProfil()" style="padding: 4px"><i class="fa"></i>Simpan</button>
                    </div>
                </div>
            </div>
    	</div>
	</div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
<script>
	$('#tglberdiri').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		lang:'id',
		closeOnDateSelect:true,
		scrollInput : false
	});
	getDataOpt('getDataPropinsi','noparam','propinsi','','<?=$result['NAMA_PROPINSI'] == "" ? "- Pilih Propinsi -" : $result['NAMA_PROPINSI']?>','<?=$idpropinsi?>');
	getDataOpt('getDataKota','propinsi='+$('#propinsi').val(),'kota','','<?=$result['NAMA_KOTA'] == "" ? "- Pilih Kota -" : $result['NAMA_KOTA']?>','<?=$idkota?>');
	getDataOpt('getDataJenisUsaha','noparam','jnsusaha','','<?=$result['NAMA_USAHA'] == "" ? "- Pilih Jenis Usaha -" : $result['NAMA_USAHA']?>','<?=$idusaha?>');
	function generateMsg(msg){
		return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
				"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
				"<strong><small id='message_response'>"+msg+"</small></strong>"+
			"</div>";
	}
	function saveProfil(){
		
		var data		=	$('#formData input, #formData select, #formData textarea').serialize();
		$('#formData input, #formData select, #formData textarea').prop('disabled', true);
		$('#message_response_container').slideDown('fast').html("");
		
		$.post("<?=APP_URL?>perusahaan/profil.php?func=<?=$enkripsi->encode('saveProfil')?>", data)
		.done(function( data ) {
			$('#formData input, #formData select, #formData textarea').prop('disabled', false);
			if(data == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
			}
		});
		
	}	
</script>