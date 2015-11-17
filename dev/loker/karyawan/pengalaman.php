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
		if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 2){
			echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
			die();
		} else {
			$idkary	=	$_SESSION['KursusLesLoker']['IDUSER'];
		}
	} else {
		$idkary		=	$enkripsi->decode($_GET['q']);
	}
		
	//FUNGSI TAMBAH DATA
	if( $enkripsi->decode($_GET['func']) == "addData" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				$respon_msg	=	'';
				switch($key){
					case "namapers"		:	$respon_msg	=	"Harap isi nama perusahaan"; break;
					case "bidang"		:	$respon_msg	=	"Harap pilih bidang perkerjaan"; break;
					case "posisi"		:	$respon_msg	=	"Harap pilih posisi pekerjaan anda"; break;
				}

				if($respon_msg <> ''){
					echo json_encode(array("respon_code"=>"00001", "respon_msg"=>$respon_msg));
					die();
				}
		
			} else {
				${$key}		=	str_replace("'","",$value);
			}
		}
		
		$idbidang		=	$enkripsi->decode($bidang);
		$idposisi		=	$enkripsi->decode($posisi);
		
		$sqlIns			=	sprintf("INSERT INTO t_pengalaman_kerja
									 (IDKARYAWAN,IDBIDANG,IDPOSISI,MULAI_KERJA,SELESAI_KERJA,NAMA_PERUSAHAAN,GAJI_BULANAN,DESKRIPSI_KERJA)
									 VALUES
									 ('%s','%s','%s','%s','%s','%s','%s','%s')"
									, $idkary
									, $idbidang
									, $idposisi
									, $tanggalawal
									, $tanggalakhir
									, $db->db_text($_POST['namapers'])
									, $_POST['gaji']
									, $db->db_text($_POST['deskripsi'])
									);
		$lastID			=	$db->execSQL($sqlIns, 1);
			
		//JIKA LAST ID > 0
		if($lastID > 0){
			
			$iddata		=	$enkripsi->encode($lastID);
			$sqldetail	=	sprintf("SELECT B.NAMA_BIDANG, C.NAMA_POSISI
							 FROM t_pengalaman_kerja A
							 LEFT JOIN m_bidang B ON A.IDBIDANG = B.IDBIDANG
							 LEFT JOIN m_posisi C ON A.IDPOSISI = C.IDPOSISI
							 WHERE A.IDPENGALAMAN = %s"
							, $lastID
							);
			$resultdet	=	$db->query($sqldetail);
			$namabidang	=	$resultdet[0]['NAMA_BIDANG'];
			$namaposisi	=	$resultdet[0]['NAMA_POSISI'];
							
			$respon_msg	=	"<tr id='row".$iddata."' class='rowdata'>
								<td>".$namapers."</td>
								<td>".$namabidang."</td>
								<td>".$namaposisi."</td>
								<td align='center'>".$tanggalawal."</td>
								<td align='center'>".$tanggalakhir."</td>
								<td align='right'>Rp. ".number_format($gaji, 0, ',', '.').",-</td>
							 </tr>";
			echo json_encode(array("respon_code"=>"00000","respon_msg"=>$respon_msg));
			die();
			
		} else {
			echo json_encode(array("respon_code"=>"00004","respon_msg"=>"Gagal menyimpan data."));
			die();
		}
		
	}
	
	//GET LIST PENGALAMAN
	$sql		=	sprintf("SELECT B.NAMA_BIDANG, C.NAMA_POSISI, A.MULAI_KERJA, A.SELESAI_KERJA, A.NAMA_PERUSAHAAN, 
									A.GAJI_BULANAN, A.DESKRIPSI_KERJA, A.IDPENGALAMAN
							 FROM t_pengalaman_kerja A
							 LEFT JOIN m_bidang B ON A.IDBIDANG = B.IDBIDANG
							 LEFT JOIN m_posisi C ON A.IDPOSISI = C.IDPOSISI
							 WHERE A.IDKARYAWAN = %s"
							, $idkary
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		
		foreach($result as $key){
			
			$iddata		=	$enkripsi->encode($key['IDPENGALAMAN']);
			$data	.=	"<tr id='row".$iddata."' class='rowdata'>
							<td>".$key['NAMA_PERUSAHAAN']."</td>
							<td>".$key['NAMA_BIDANG']."</td>
							<td>".$key['NAMA_POSISI']."</td>
							<td align='center'>".$key['MULAI_KERJA']."</td>
							<td align='center'>".$key['SELESAI_KERJA']."</td>
							<td align='right'>Rp. ".number_format($key['GAJI_BULANAN'], 0, ',', '.').",-</td>
						 </tr>";
		}
		
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='6'><center>Tidak ada data</center></td></tr>";
	}
	//HABIS -- GET LIST PENGALAMAN
	
?>
<div class="boxSquareWhite">
    <h4>Tambahkan Data Pengalaman</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <form action="#" id="addForm" method="post">
                <div class="form-group">
                    <input type="text" name="namapers" id="namapers" class="form-control" maxlength="75" placeholder="Nama Perusahaan" autocomplete="off" />
                </div>
                <div class="form-group">
                    <select id="bidang" name="bidang" class="form-control" required>
                        <option value="">- Pilih Bidang Pekerjaan -</option>
                    </select>
                </div>
                <div class="form-group">
                    <select id="posisi" name="posisi" class="form-control" required>
                        <option value="">- Pilih Posisi Kerja -</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="tanggalawal" id="tanggalawal" class="form-control" maxlength="4" placeholder="Tanggal Awal" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-1 col-xs-12"> s/d </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="tanggalakhir" id="tanggalakhir" class="form-control" maxlength="4" placeholder="Tanggal Akhir" autocomplete="off" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" name="gaji" id="gaji" class="form-control" maxlength="11" placeholder="Gaji yang diterima" autocomplete="off" style="text-align:right" />
                </div>
                <div class="form-group">
                    <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Deskripsi pekerjaan anda" rows="5"></textarea>
                </div>
                <div id="button_container">
                    <input type="button" id="submit" name="submit" value="Simpan" class="btn btn-sm btn-custom2" onclick="addData()" />
                </div>
            </form>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite">
    <h4>Data Pengalaman Kerja</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Nama Perusahaan</th>
                    <th class="text-center">Bidang</th>
                    <th class="text-center">Posisi</th>
                    <th class="text-center">Mulai Kerja</th>
                    <th class="text-center">Berakhir</th>
                    <th class="text-center">Gaji Bulanan</th>
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
	$('#tanggalakhir, #tanggalawal').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		lang:'id',
		closeOnDateSelect:true,
		scrollInput : false
	});
	getDataOpt('getDataBidang','noparam','bidang','','- Pilih Bidang Pekerjaan -');
	getDataOpt('getDataPosisi','noparam','posisi','','- Pilih Posisi Kerja -');
	function addData(){
		
		var data		=	$('#addForm input, #addForm select, #addForm textarea').serialize();
		$('#addForm input, #addForm select, #addForm textarea').prop('disabled', true);
		
		$.post("<?=APP_URL?>karyawan/pengalaman.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#addForm input, #addForm select').prop('disabled', false);
			if(data['respon_code'] == '00001'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
			} else if(data['respon_code'] == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
				$('#tableData').prepend(data['respon_msg']);
				$('#addForm input, #addForm select, #addForm textarea').val('');
				getDataOpt('getDataBidang','noparam','bidang','','- Pilih Bidang Pekerjaan -');
				getDataOpt('getDataPosisi','noparam','posisi','','- Pilih Posisi Kerja -');
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