<?php
	include('../php/include/enkripsi.php');
	include('../php/include/session.php');
	include('../php/lib/db_connection.php');
	require "../php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	if($session->cekSession() <> 2 && !isset($_GET['func'])){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode($session->cekSession())."'</script>";
		 die();
	}
	
	//FUNGSI DIGUNAKAN TAMBAH DATA
	if( $enkripsi->decode($_GET['func']) == "addData" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				switch($key){
					case "bank"		:	$respon_msg	=	"Harap pilih bank"; break;
					case "norek"	:	$respon_msg	=	"Harap isi Nomor Rekening"; break;
					case "unit"		:	$respon_msg	=	"Harap isi unit bank"; break;
					case "cabang"	:	$respon_msg	=	"Harap isi cabang bank"; break;
					case "atasnama"	:	$respon_msg	=	"Harap isi atas nama"; break;
					default			:	$respon_msg	=	"Lengkapi data isian anda"; break;
				}
				break;
			} else {
				${$key}		=	str_replace("'","",$value);
			}
		}
		
		if($respon_msg <> ''){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>$respon_msg));
			die();
		}
		
		$idbank			=	$enkripsi->decode($_POST['bank']);
		$sqlIns			=	sprintf("INSERT INTO admin_rekening
									 (IDBANK,NO_REKENING,UNIT_BANK,CABANG_BANK,ATAS_NAMA,STATUS)
									 VALUES
									 ('%s','%s','%s','%s','%s','1')"
									, $idbank
									, $norek
									, $unit
									, $cabang
									, $atasnama
									);
		$lastID			=	$db->execSQL($sqlIns, 1);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($lastID > 0){
			
			$sqlbank	=	sprintf("SELECT CLASS, NAMA_BANK FROM m_bank WHERE IDBANK = %s LIMIT 0,1", $idbank);
			$resBank	=	$db->query($sqlbank);
			$resBank	=	$resBank[0];
			$class		=	$resBank['CLASS'];
			$namabank	=	$resBank['NAMA_BANK'];
			
			$body	=	"	<tr id='baris".$enkripsi->encode($lastID)."'>
									<td class='".$class."'></td>
									<td>".$namabank."</td>
									<td>".$norek."</td>
									<td>".$unit."</td>
									<td>".$cabang."</td>
									<td>".$atasnama."</td>
									<td width='100' align='center'>
										<a href='#' id='nonA".$enkripsi->encode($lastID)."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($lastID)."\")'>
											Non Aktifkan
										</a>
									</td>
								</tr>";
			echo json_encode(array("respon_code"=>'00000', 'respon_msg'=>'Data tersimpan', 'respon_body'=>$body));
			die();
		} else {
			echo json_encode(array("respon_code"=>'00004', 'respon_msg'=>'Gagal menyimpan data. Coba lagi nanti'));
			die();
		}
		
	}
	
	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		if(isset($_POST['status']) && $_POST['status'] <> ''){
			$conStatus	=	$_POST['status'] == 2 ? "1=1" : "A.STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		//SELECT DATA REKENING
		$sql		=	sprintf("SELECT A.IDREKENING, B.CLASS, B.NAMA_BANK, A.NO_REKENING, 
										A.UNIT_BANK, A.CABANG_BANK, A.ATAS_NAMA, A.STATUS
								 FROM admin_rekening A
								 LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
								 WHERE %s
								 ORDER BY A.STATUS DESC"
								, $conStatus
								);
		$result		=	$db->query($sql);
		$totData	=	0;
		$data		=	'';

		if($result <> '' && $result <> false){
			$i		=	1;
			foreach($result as $key){
				
				$status	=	$key['STATUS'] == "1" ? "Non Aktifkan" : "Aktifkan";

				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDREKENING'])."'>
									<td class='".$key['CLASS']."'></td>
									<td>".$key['NAMA_BANK']."</td>
									<td>".$key['NO_REKENING']."</td>
									<td>".$key['UNIT_BANK']."</td>
									<td>".$key['CABANG_BANK']."</td>
									<td>".$key['ATAS_NAMA']."</td>
									<td width='100' align='center'>
										<a href='#' id='nonA".$enkripsi->encode($key['IDREKENING'])."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($key['IDREKENING'])."\")'>
											".$status."
										</a>
									</td>
								</tr>";
				$totData++;
				$i++;
			}
		} else {
			$data	=	"	<tr><td colspan = '7'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data));
		die();
	}

	//FUNGSI DIGUNAKAN AKTIFKAN / NON AKTIFKAN DATA
	if( $enkripsi->decode($_GET['func']) == "nonAktif" && isset($_GET['func'])){
		
		$iddata			=	$enkripsi->decode($_POST['iddata']);
		$sqlCek			=	sprintf("SELECT STATUS
									 FROM admin_rekening
									 WHERE IDREKENING = %s"
									, $iddata
									);
		$resultCek		=	$db->query($sqlCek);
		$status			=	$resultCek[0]['STATUS'];
		
		if($status == 1){
			$respon_code	=	"00000";
			$updStatus		=	"0";
		} else {
			$respon_code	=	"00001";
			$updStatus		=	"1";
		}
		
		$sqlUpd			=	sprintf("UPDATE admin_rekening SET STATUS = %s
									 WHERE IDREKENING = %s"
									, $updStatus
									, $iddata
									);
		$affected		=	$db->execSQL($sqlUpd, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected <= 0){
			$respon_code=	"00003";
			$respon_msg	=	"Gagal mengubah data";
		}
		
		echo json_encode(array("respon_code"=>$respon_code, "respon_msg"=>$respon_msg));
		die();
	}
?>
<style>
	#standart_table tbody tr{height: 40px;}
	#standart_table tbody tr td{min-width: 40px;background-repeat: no-repeat;background-position: center center;}
	#standart_table tbody tr td.bri{ background-image: url('<?=APP_IMG_URL?>bank/bri.jpg');}
	#standart_table tbody tr td.bni{ background-image: url('<?=APP_IMG_URL?>bank/bni.jpg');}
	#standart_table tbody tr td.bca{ background-image: url('<?=APP_IMG_URL?>bank/bca.jpg');}
	#standart_table tbody tr td.mandiri{ background-image: url('<?=APP_IMG_URL?>bank/mandiri.jpg');}
</style>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-folder-o"></i> Master</a></li>
        <li>Rekening Bank</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Master Data Rekening Bank</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Status</label>
                <span class="field">
                    <input name="status" value="2" id="status1" onclick="filterData()" checked type="radio"> Semua &nbsp; 
                    <input name="status" value="1" id="status1" onclick="filterData()" type="radio"> Aktif &nbsp; 
                    <input name="status" value="0" id="status2" onclick="filterData()" type="radio"> Tidak Aktif
                </span>
            </p>
        </div>
        <div id="divposting" style="display:none">
            <p>
                <label>Bank</label>
                <span class="field">
					<select id="bank" name="bank" class="form-control">
                    	<option value="">- Pilih Bank -</option>
                    </select>
				</span>
			</p>
            <p>
                <label>No. Rekening</label>
                <span class="field">
					<input type="text" id="norek" name="norek" maxlength="25" class="smallinput" />
				</span>
			</p>
            <p>
                <label>Unit</label>
                <span class="field">
					<input type="text" id="unit" name="unit" maxlength="50" class="smallinput" />
				</span>
			</p>
            <p>
                <label>Cabang</label>
                <span class="field">
					<input type="text" id="cabang" name="cabang" maxlength="50" class="smallinput" />
				</span>
			</p>
            <p>
                <label>Atas Nama</label>
                <span class="field">
					<input type="text" id="atasnama" name="atasnama" maxlength="75" class="mediuminput" />
				</span>
			</p>
		</div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="tambah" id="tambah" class="submit radius2 pull-right" value="Tambah Data" type="button" onclick="showComposer(true)">
            <input name="simpan" id="simpan" class="submit radius2 pull-right" value="Simpan" type="button" onclick="submitData()" style="display:none">
            <input name="batal" id="batal" class="reset radius2 pull-right" value="Batal" type="reset" onclick="showComposer(false)" style="display:none; margin-right:5px">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
            	<table id="standart_table" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head1" style="text-align:center;"></td>
                            <td class="head0" style="text-align:center;">Bank</td>
                            <td class="head1" style="text-align:center;">No. Rekening</td>
                            <td class="head0" style="text-align:center;">Unit</td>
                            <td class="head1" style="text-align:center;">Cabang</td>
                            <td class="head0" style="text-align:center;">Atas Nama</td>
                            <td class="head1" style="text-align:center;"> </td>
                        </tr>
                    </thead>
                    <tbody id="list_rekening">
                    </tbody>
				</table>
            </div>
        </div>
    </div>
</div>
<script>
	function nonAktif(id){
		$.post( "<?=APP_URL?>page/103rekbank.php?func=<?=$enkripsi->encode('nonAktif')?>", {iddata: id})
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				$('#showfilter').html("1 Data Rekening Dinon-aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Aktifkan');
			} else if(data['respon_code'] == '00001'){
				$('#showfilter').html("1 Data Rekening Di Aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Non Aktifkan');
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
	}
	function filterData(){
		var status	=	$('input[name=status]:checked', '#divfilter').val();

		$('#list_rekening').html("<tr><td colspan = '7'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/103rekbank.php?func=<?=$enkripsi->encode('filterData')?>", {status: status})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_rekening').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Rekening Ditemukan");
			
		});
	}
	function showComposer(status){
		if(status == true){
			$('#divfilter').slideUp('fast');
			$('#divposting').slideDown('fast');
			$('#tambah').hide();
			$('#simpan').show();
			$('#batal').show();
		} else {
			$('#divposting').slideUp('fast');
			$('#divfilter').slideDown('fast');
			$('#simpan').hide();
			$('#batal').hide();
			$('#tambah').show();
		}
	}

	function submitData(){

		var data		=	$("#divposting input, #divposting select").serialize();
	
		$("#divposting input, #divposting select").prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		
		$.post( "<?=APP_URL?>page/103rekbank.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
				$("#divposting input, #divposting select").prop('disabled', false);
				getDataOpt('getDataBank','noparam','bank','','- Pilih Bank -');
			} else {
				$("#divposting input, #divposting select").prop('disabled', false).val('');
				getDataOpt('getDataBank','noparam','bank','','- Pilih Bank -');
				$('#list_rekening').prepend(data['respon_body']);
				$('#showfilter').html("1 Data Ditambahkan");
				showComposer(false);
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
		
	}

	$(document).ready(function(){
		filterData();
		getDataOpt('getDataBank','noparam','bank','','- Pilih Bank -');
	});

</script>