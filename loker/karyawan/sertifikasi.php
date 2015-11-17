<?php
	
	include('../php/lib/db_connection.php');
	include('../php/lib/enkripsi.php');
	include('../php/lib/session.php');
	require "../php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 2){
		echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
		die();
	} else {
		$idkary	=	$_SESSION['KursusLesLoker']['IDUSER'];
	}
		
	//FUNGSI TAMBAH DATA
	if( $enkripsi->decode($_GET['func']) == "addData" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				$respon_msg	=	'';
				switch($key){
					case "namasert"			:	$respon_msg	=	"Harap isi nama sertifikat"; break;
					case "dikeluarkanoleh"	:	$respon_msg	=	"Harap isi lembaga yang menerbitkan sertifikat"; break;
					case "tahun"			:	$respon_msg	=	"Harap isi tahun perolehan"; break;
				}

				if($respon_msg <> ''){
					echo json_encode(array("respon_code"=>"00001", "respon_msg"=>$respon_msg));
					die();
				}
		
			} else {
				${$key}		=	str_replace("'","",$value);
			}
		}
		
		$sqlIns			=	sprintf("INSERT INTO t_sertifikasi
									 (IDKARYAWAN,NAMA_SERTIFIKAT,DIKELUARKAN_OLEH,TAHUN_PEROLEHAN)
									 VALUES
									 ('%s','%s','%s','%s')"
									, $idkary
									, $db->db_text($_POST['namasert'])
									, $db->db_text($_POST['dikeluarkanoleh'])
									, $_POST['tahun']
									);
		$lastID			=	$db->execSQL($sqlIns, 1);
			
		//JIKA LAST ID > 0
		if($lastID > 0){
			
			$iddata		=	$enkripsi->encode($lastID);
			$respon_msg	=	"<tr id='row".$iddata."' class='rowdata'>
								<td>".$_POST['namasert']."</td>
								<td>".$_POST['dikeluarkanoleh']."</td>
								<td>".$_POST['tahun']."</td>
							 </tr>";
			echo json_encode(array("respon_code"=>"00000","respon_msg"=>$respon_msg));
			die();
			
		} else {
			echo json_encode(array("respon_code"=>"00004","respon_msg"=>"Gagal menyimpan data."));
			die();
		}
		
	}
	
	//GET LIST SERTIFIKAT
	$sql		=	sprintf("SELECT IDSERTIFIKAT, NAMA_SERTIFIKAT, DIKELUARKAN_OLEH, TAHUN_PEROLEHAN
							 FROM t_sertifikasi
							 WHERE IDKARYAWAN = %s"
							, $idkary
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		
		foreach($result as $key){
			$iddata		=	$enkripsi->encode($key['IDSERTIFIKAT']);
			$data	.=	"<tr id='row".$iddata."' class='rowdata'>
							<td>".$key['NAMA_SERTIFIKAT']."</td>
							<td>".$key['DIKELUARKAN_OLEH']."</td>
							<td>".$key['TAHUN_PEROLEHAN']."</td>
						 </tr>";
		}
		
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='3'><center>Tidak ada data</center></td></tr>";
	}
	//HABIS -- GET LIST SERTIFIKAT
	
?>
<div class="boxSquareWhite">
    <h4>Tambahkan Data Pendidikan</h4>
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <form action="#" id="addForm" method="post">
                <div class="form-group">
                    <input type="text" name="namasert" id="namasert" class="form-control" maxlength="75" placeholder="Nama Sertifikat" autocomplete="off" />
                </div>
                <div class="form-group">
                    <input type="text" name="dikeluarkanoleh" id="dikeluarkanoleh" class="form-control" maxlength="75" placeholder="Dikeluarkan oleh" autocomplete="off" />
                </div>
                <div class="form-group">
                    <input type="text" name="tahun" id="tahun" class="form-control" maxlength="4" placeholder="Tahun Perolehan" autocomplete="off" />
                </div>
                <div id="button_container">
                    <input type="button" id="submit" name="submit" value="Simpan" class="btn btn-sm btn-custom2" onclick="addData()" />
                </div>
            </form>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><b>Informasi</b></div>
                <div class="panel-body">
                    <p>Data sertifikat akan menjadi nilai tambah anda</p>
                </div>
            </div>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite">
    <h4>Data Sertifikat</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Nama Sertifikat</th>
                    <th class="text-center">Dikeluarkan Oleh</th>
                    <th class="text-center">Tahun Keluar</th>
                </tr>
            </thead>
            <tbody id="tableData">
            	<?=$data?>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
<script>
	function addData(){
		
		var data		=	$('#addForm input').serialize();
		$('#addForm input').prop('disabled', true);
		
		$.post("<?=APP_URL?>karyawan/sertifikasi.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#addForm input').prop('disabled', false);
			if(data['respon_code'] == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data['respon_code'] == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
				$('#tableData').prepend(data['respon_msg']);
				$('#addForm input').val('');
				$('#submit').val('Simpan');
				if($('#rownodata').length > 0){
					$('#rownodata').remove();
				}
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
			}
		});
		
	}
</script>