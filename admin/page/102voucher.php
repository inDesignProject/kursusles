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
					case "nama"		:	$respon_msg	=	"Harap isi nama voucher"; break;
					case "kode"		:	$respon_msg	=	"Harap isi kode voucher"; break;
					case "nominal"	:	$respon_msg	=	"Harap isi nominal voucher"; break;
					case "harga"	:	$respon_msg	=	"Harap isi harga voucher"; break;
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

		$sqlIns			=	sprintf("INSERT INTO m_voucher
									 (NAMA_VOUCHER,KODE_VOUCHER,NOMINAL,HARGA,STATUS)
									 VALUES
									 ('%s','%s','%s','%s','1')"
									, $nama
									, $kode
									, $nominal
									, $harga
									);
		$lastID			=	$db->execSQL($sqlIns, 1);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($lastID > 0){
			$body	=	"	<tr id='baris".$enkripsi->encode($lastID)."'>
									<td>".$nama."</td>
									<td>".$kode."</td>
									<td align='right'>".number_format($nominal, 0, ',' ,'.')."</td>
									<td align='right'>".number_format($harga, 0, ',' ,'.')."</td>
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
			$conStatus	=	$_POST['status'] == 0 ? "1=1" : "STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		//SELECT DATA VOUCHER
		$sql		=	sprintf("SELECT IDVOUCHER, NAMA_VOUCHER, KODE_VOUCHER, NOMINAL, HARGA, STATUS
								 FROM m_voucher
								 WHERE %s
								 ORDER BY STATUS"
								, $conStatus
								);
		$result		=	$db->query($sql);
		$totData	=	0;
		$data		=	'';

		if($result <> '' && $result <> false){
			$i		=	1;
			foreach($result as $key){
				
				$status	=	$key['STATUS'] == "1" ? "Non Aktifkan" : "Aktifkan";
				$seling	=	$i%2 == 0 ? "seling" : "";

				$data	.=	"	<tr class='".$seling."' id='baris".$enkripsi->encode($key['IDVOUCHER'])."'>
									<td>".$key['NAMA_VOUCHER']."</td>
									<td>".$key['KODE_VOUCHER']."</td>
									<td align='right'>".number_format($key['NOMINAL'], 0, ',' ,'.')."</td>
									<td align='right'>".number_format($key['HARGA'], 0, ',' ,'.')."</td>
									<td width='100' align='center'>
										<a href='#' id='nonA".$enkripsi->encode($key['IDVOUCHER'])."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($key['IDVOUCHER'])."\")'>
											".$status."
										</a>
									</td>
								</tr>";
				$totData++;
				$i++;
			}
		} else {
			$data	=	"	<tr><td colspan = '5'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data));
		die();
	}
	
	//FUNGSI DIGUNAKAN AKTIFKAN / NON AKTIFKAN DATA
	if( $enkripsi->decode($_GET['func']) == "nonAktif" && isset($_GET['func'])){
		
		$iddata			=	$enkripsi->decode($_POST['iddata']);
		$sqlCek			=	sprintf("SELECT STATUS
									 FROM m_voucher
									 WHERE IDVOUCHER = %s"
									, $iddata
									);
		$resultCek		=	$db->query($sqlCek);
		$status			=	$resultCek[0]['STATUS'];
		
		if($status == 1){
			$respon_code	=	"00000";
			$updStatus		=	"2";
		} else {
			$respon_code	=	"00001";
			$updStatus		=	"1";
		}
		
		$sqlUpd			=	sprintf("UPDATE m_voucher SET STATUS = %s
									 WHERE IDVOUCHER = %s"
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
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-folder-o"></i> Master</a></li>
        <li>Voucher</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Master Voucher Siswa</h1>
</div>
<div id="contentwrapper" class="elements">
	<form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Status</label>
                <span class="field">
                    <input name="status" value="0" id="status1" onclick="filterData()" checked type="radio"> Semua &nbsp; 
                    <input name="status" value="1" id="status1" onclick="filterData()" type="radio"> Aktif &nbsp; 
                    <input name="status" value="2" id="status2" onclick="filterData()" type="radio"> Tidak Aktif
                </span>
            </p>
        </div>
        <div id="divposting" style="display:none">
            <p>
                <label>Nama Voucher </label>
                <span class="field">
					<input type="text" id="nama" name="nama" maxlength="150" class="mediuminput" />
				</span>
			</p>
            <p>
                <label>Kode </label>
                <span class="field">
					<input type="text" id="kode" name="kode" maxlength="10" class="smallinput" />
				</span>
			</p>
            <p>
                <label>Nominal </label>
                <span class="field">
					<input type="text" id="nominal" name="nominal" maxlength="11" style="text-align:right" class="smallinput" onkeyup="setValue(this.id)" />
				</span>
			</p>
            <p>
                <label>Harga </label>
                <span class="field">
					<input type="text" id="harga" name="harga" maxlength="11" style="text-align:right" class="smallinput" onkeyup="setValue(this.id)" />
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
            	<table id="table_voucher" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Nama</td>
                            <td class="head1" style="text-align:center;">Kode</td>
                            <td class="head0" style="text-align:center;">Nominal</td>
                            <td class="head1" style="text-align:center;">Harga</td>
                            <td class="head0" style="text-align:center;"> </td>
                        </tr>
                    </thead>
                    <tbody id="list_voucher">
                    </tbody>
				</table>
            </div>
        </div>
    </div>
</div>
<script>
	function nonAktif(id){
		$.post( "<?=APP_URL?>page/102voucher.php?func=<?=$enkripsi->encode('nonAktif')?>", {iddata: id})
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				$('#showfilter').html("1 Data Voucher Dinon-aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Aktifkan');
			} else if(data['respon_code'] == '00001'){
				$('#showfilter').html("1 Data Voucher Di Aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Non Aktifkan');
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
	}
	function filterData(){
		var status	=	$('input[name=status]:checked', '#divfilter').val();

		$('#list_voucher').html("<tr><td colspan = '5'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/102voucher.php?func=<?=$enkripsi->encode('filterData')?>", {status: status})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_voucher').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Voucher Ditemukan");
			
		});
	}
	function setValue(id){
		var nominal	=	formatNum($('#'+id).val());
		$('#'+id).val(nominal);
	}
	function formatNum(num) {num= num.toString().replace(/\./g,'*'); num= num.toString().replace(/\,/g,'.'); num= num.toString().replace(/\*/g,','); num= num.toString().replace(/Rp|\,/g,''); if(isNaN(num)) num = "0"; return num;}
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

		var data		=	$("#divposting input").serialize();
	
		$("#divposting input").prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		
		$.post( "<?=APP_URL?>page/102voucher.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
				$("#divposting input").prop('disabled', false);
			} else {
				$("#divposting input").prop('disabled', false).val('');
				$('#list_voucher').prepend(data['respon_body']);
				$('#showfilter').html("1 Data Ditambahkan");
				showComposer(false);
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
		
	}
	$(document).ready(function(){
		filterData();
	});
	
</script>