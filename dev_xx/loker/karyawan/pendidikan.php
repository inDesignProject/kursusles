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
					case "idjenjang"		:	$respon_msg	=	"Pilih jenjang pendidikan"; break;
					case "institusi"		:	$respon_msg	=	"Harap isi nama institusi pendidikan"; break;
					case "tahunawal"		:	$respon_msg	=	"Harap isi tahun awal"; break;
					case "tahunakhir"		:	$respon_msg	=	"Harap isi tahun akhir"; break;
				}

				if($respon_msg <> ''){
					echo json_encode(array("respon_code"=>"00001", "respon_msg"=>$respon_msg));
					die();
				}
		
			} else {
				${$key}		=	str_replace("'","",$value);
			}
		}
		
		$idjenjang		=	$enkripsi->decode($_POST['idjenjang']);
		$tampil			=	isset($_POST['cektampil']) && $_POST['cektampil'] == '1' ? "1" : "0";
		
		$sqlIns			=	sprintf("INSERT INTO t_riwayat_pendidikan
									 (IDKARYAWAN,IDJENJANG,BIDANG_STUDI,NAMA_INSTITUSI,TAHUN_AWAL,TAHUN_AKHIR,TAMPIL)
									 VALUES
									 ('%s','%s','%s','%s','%s','%s','%s')"
									, $idkary
									, $idjenjang
									, $db->db_text($_POST['bidangstudi'])
									, $db->db_text($_POST['institusi'])
									, $_POST['tahunawal']
									, $_POST['tahunakhir']
									, $tampil
									);
		$lastID			=	$db->execSQL($sqlIns, 1);
			
		//JIKA LAST ID > 0
		if($lastID > 0){
			
			$iddata		=	$enkripsi->encode($lastID);
			$checked	=	isset($_POST['cektampil']) && $_POST['cektampil'] == "1" ? "checked" : "";
			$sqlj		=	sprintf("SELECT NAMA_JENJANG FROM m_jenjang WHERE IDJENJANG = %s LIMIT 0,1", $idjenjang);
			$resultj	=	$db->query($sqlj);
			$namajenjang=	$resultj[0]['NAMA_JENJANG'];
			
			$respon_msg	=	"<tr id='row".$iddata."' class='rowdata'>
								<td>".$namajenjang."</td>
								<td>".$_POST['bidangstudi']."</td>
								<td>".$_POST['institusi']."</td>
								<td align='center'>".$_POST['tahunawal']."</td>
								<td align='center'>".$_POST['tahunakhir']."</td>
								<td align='center'><input name='cek".$iddata."' id='cek".$iddata."' value='1' ".$checked." type='checkbox' onclick='updTampil(this.id)'> Tampilkan</td>
							 </tr>";
			echo json_encode(array("respon_code"=>"00000","respon_msg"=>$respon_msg));
			die();
			
		} else {
			echo json_encode(array("respon_code"=>"00004","respon_msg"=>"Gagal menyimpan data."));
			die();
		}
		
	}
	
	//FUNGSI UPDATE TAMPIL
	if( $enkripsi->decode($_GET['func']) == "updTampil" && isset($_GET['func'])){
		
		$iddata		=	$enkripsi->decode(substr($_POST['value'],3,strlen($_POST['value'])-3));
		$sqlUpd		=	sprintf("UPDATE t_riwayat_pendidikan SET TAMPIL = IF(TAMPIL = 0, 1, 0) WHERE IDRIWAYATDIDIK = %s", $iddata);
		$affected	=	$db->execSQL($sqlUpd, 0);
			
		//JIKA LAST ID > 0
		if($affected > 0){
			echo json_encode(array("respon_code"=>"00000",
								   "respon_msg"=>"Data diperbaharui"));
		} else {
			echo json_encode(array("respon_code"=>"00001",
								   "respon_msg"=>"Gagal mengubah data"));
		}
		die();
		
	}

	//GET LIST PENDIDIKAN
	$sql		=	sprintf("SELECT A.IDRIWAYATDIDIK, B.NAMA_JENJANG, A.BIDANG_STUDI, A.NAMA_INSTITUSI,
									A.TAHUN_AWAL, A.TAHUN_AKHIR, A.TAMPIL
							 FROM t_riwayat_pendidikan A
							 LEFT JOIN m_jenjang B ON A.IDJENJANG = B.IDJENJANG
							 WHERE A.IDKARYAWAN = %s
							 ORDER BY A.IDJENJANG DESC"
							, $idkary
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		
		foreach($result as $key){
			$iddata		=	$enkripsi->encode($key['IDRIWAYATDIDIK']);
			$checked	=	$key['TAMPIL'] == "1" ? "checked" : "";
		
			$data	.=	"<tr id='row".$iddata."' class='rowdata'>
							<td>".$key['NAMA_JENJANG']."</td>
							<td>".$key['BIDANG_STUDI']."</td>
							<td>".$key['NAMA_INSTITUSI']."</td>
							<td align='center'>".$key['TAHUN_AWAL']."</td>
							<td align='center'>".$key['TAHUN_AKHIR']."</td>
							<td align='center'><input name='cek".$iddata."' id='cek".$iddata."' value='1' ".$checked." type='checkbox' onclick='updTampil(this.id)'> Tampilkan</td>
						 </tr>";
		}
		
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='6'><center>Tidak ada data</center></td></tr>";
	}
	//HABIS -- GET LIST PENDIDIKAN
	
?>
<div class="boxSquareWhite">
    <h4>Tambahkan Data Pendidikan</h4>
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <form action="#" id="addForm" method="post">
                <div class="form-group">
                    <select id="idjenjang" name="idjenjang" class="form-control" required >
                        <option value="">- Pilih Jenjang -</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="bidangstudi" id="bidangstudi" class="form-control" maxlength="75" placeholder="Bidang Studi" autocomplete="off" />
                </div>
                <div class="form-group">
                    <input type="text" name="institusi" id="institusi" class="form-control" maxlength="75" placeholder="Nama Institusi Pendidikan" autocomplete="off" />
                </div>
                <div class="row">
                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="tahunawal" id="tahunawal" class="form-control" maxlength="4" placeholder="Tahun Awal" autocomplete="off" />
                        </div>
                    </div>
                     <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12"> s/d </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="tahunakhir" id="tahunakhir" class="form-control" maxlength="4" placeholder="Tahun Akhir" autocomplete="off" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input name="cektampil" id="cektampil" value="1" checked type="checkbox" > Tampilkan
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
                    <p>Isilah data secara lengkap dan jelas agar perusahaan yang membutuhkan anda bisa melihat data anda secara rinci</p>
                </div>
            </div>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite">
    <h4>Data Pendidikan Anda</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Jenjang</th>
                    <th class="text-center">Bidang Studi</th>
                    <th class="text-center">Nama Institusi</th>
                    <th class="text-center">Tahun<br/>Awal</th>
                    <th class="text-center">Tahun<br/>Berakhir</th>
                    <th class="text-center"></th>
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
	getDataOpt('getDataJenjang','noparam','idjenjang','','- Pilih Jenjang Pendidikan -');
	function updTampil(id){
		$('#message_response_container').slideUp('fast').html('');
		$.post("<?=APP_URL?>karyawan/pendidikan.php?func=<?=$enkripsi->encode('updTampil')?>", {value:id})
		.done(function( data ) {
			data	=	JSON.parse(data);
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
		});
	}
	function addData(){
		
		var data		=	$('#addForm input, #addForm select, #addForm checkbox').serialize();
		$('#addForm input, #addForm select, #addForm checkbox').prop('disabled', true);
		
		$.post("<?=APP_URL?>karyawan/pendidikan.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#addForm input, #addForm select, #addForm checkbox').prop('disabled', false);
			if(data['respon_code'] == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data['respon_code'] == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
				$('#tableData').prepend(data['respon_msg']);
				$('#addForm input, #addForm select').val('');
				getDataOpt('getDataJenjang','noparam','idjenjang','','- Pilih Jenjang Pendidikan -');
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