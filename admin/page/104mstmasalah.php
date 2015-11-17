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

	//FUNGSI DIGUNAKAN AKTIFKAN / NON AKTIFKAN DATA SOLUSI
	if( $enkripsi->decode($_GET['func']) == "rmSolusi" && isset($_GET['func'])){
		
		$iddata			=	$enkripsi->decode($_POST['iddata']);
		$sqlUpd			=	sprintf("UPDATE m_report_solusi SET STATUS = 0
									 WHERE IDSOLUSI = %s"
									, $iddata
									);
		$affected		=	$db->execSQL($sqlUpd, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected <= 0){
			$respon_code=	"00001";
			$respon_msg	=	"Gagal mengubah data";
		} else {
			$respon_code=	"00000";
			$respon_msg	=	"";
		}
		
		echo json_encode(array("respon_code"=>$respon_code, "respon_msg"=>$respon_msg));
		die();
	}
	
	//FUNGSI DIGUNAKAN BUKA FORM TAMBAH
	if( $enkripsi->decode($_GET['func']) == "addSolusi" && isset($_GET['func'])){
		
		echo "	<div id='addSolusi".$_POST['id']."' class='addSolusi'>
				  <input style='width: 500px;margin-bottom: 5px;' id='postsolusi' name='postsolusi' value='' type='text' placeholder='Solusi'>
				  <br/>
				  <textarea id='postketerangansol' name='postketerangansol' style='height: 60px; width: 500px;' placeholder='Keterangan Solusi'></textarea>
				  <br/>
				  <a class='btn btn-kursusles btn-sm' href='#' style='padding: 3px;' onclick='saveSolusi(\"".$_POST['id']."\")'> <i class='fa fa-save'></i> Simpan </a>
				</div>";
		die();
	}

	//FUNGSI DIGUNAKAN TAMBAH DATA SOLUSI
	if( $enkripsi->decode($_GET['func']) == "saveSolusi" && isset($_GET['func'])){
		$idmasalah	=	$enkripsi->decode($_GET['id']);
		
		if(!isset($_POST['postsolusi']) || $_POST['postsolusi'] == ''){
			echo json_encode(array("respon_code"=>'00001', 'respon_msg'=>'Harap masukkan nama solusi', 'respon_body'=>''));
			die();
		}

		if(!isset($_POST['postketerangansol']) || $_POST['postketerangansol'] == ''){
			echo json_encode(array("respon_code"=>'00002', 'respon_msg'=>'Harap masukkan keterangan solusi', 'respon_body'=>''));
			die();
		}
		
		$sqlIns			=	sprintf("INSERT INTO m_report_solusi
									 (IDMASALAH,NAMA_SOLUSI,KETERANGAN)
									 VALUES
									 ('%s','%s','%s')"
									, $idmasalah
									, $db->db_text($_POST['postsolusi'])
									, $db->db_text($_POST['postketerangansol'])
									);
		$lastID			=	$db->execSQL($sqlIns, 1);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($lastID > 0){
			$body	=	"	<li>
								".$_POST['postsolusi']."
								<a href='#' class='btn btn-kursusles btn-sm' style='padding: 3px;' onclick='rmSolusi(\"".$enkripsi->encode($lastID)."\")'>
									<i class='fa fa-timer'></i> Hapus
								</a><br>
								<small><i class='fa fa-chevron-circle-right'></i> ".$_POST['postketerangansol']."</small>
							</li>";
			echo json_encode(array("respon_code"=>'00000', 'respon_msg'=>'Data tersimpan', 'respon_body'=>$body));
			die();
		} else {
			echo json_encode(array("respon_code"=>'00003', 'respon_msg'=>'Gagal menyimpan data. Coba lagi nanti'));
			die();
		}

	}
	
	//FUNGSI DIGUNAKAN LIHAT DETAIL
	if( $enkripsi->decode($_GET['func']) == "getDetail" && isset($_GET['func'])){
		$idmasalah	=	$enkripsi->decode($_POST['id']);
		$sqlket		=	sprintf("SELECT KETERANGAN
								 FROM m_report_masalah
								 WHERE IDMASALAH = %s
								 LIMIT 0,1"
								, $idmasalah
								);
		$resultket	=	$db->query($sqlket);
		$keterangan	=	$resultket[0]['KETERANGAN'];
		
		$sql		=	sprintf("SELECT IDSOLUSI, NAMA_SOLUSI, KETERANGAN
								 FROM m_report_solusi
								 WHERE IDMASALAH = %s AND STATUS =1"
								, $idmasalah
								);
		$result		=	$db->query($sql);
		$listSol	=	'';
		
		if($result <> '' && $result <> false){
			foreach($result as $key){
				$listSol.=	"<li>
								".$key['NAMA_SOLUSI']."
								<a href='#' class='btn btn-kursusles btn-sm' style='padding: 3px;' onclick='rmSolusi(\"".$enkripsi->encode($key['IDSOLUSI'])."\")'>
									<i class='fa fa-timer'></i> Hapus
								</a>
								<br>
								<small><i class='fa fa-chevron-circle-right'></i> ".$key['KETERANGAN']."</small>
							</li>";
			}
		}
		
		echo "	<tr class='detail-con'>
					<td style='padding: 24px;' colspan='3'>
						<h4>Keterangan</h4>
						<p>
							".$keterangan."
						</p><br>
						<h4>Daftar Solusi</h4>
						<ul id='ul".$_POST['id']."'>
							".$listSol."
						</ul>
						<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='addSolusi(\"".$_POST['id']."\")'>
							<i class='fa fa-plus'></i> Solusi
						</a>
					</td>
				</tr>";
		die();
	}
	
	//FUNGSI DIGUNAKAN TAMBAH DATA
	if( $enkripsi->decode($_GET['func']) == "addData" && isset($_GET['func'])){
		
		foreach($_POST as $key => $value){
			if($value == ''){
				switch($key){
					case "postmasalah"		:	$respon_msg	=	"Harap isi masalah"; break;
					case "postketerangan"	:	$respon_msg	=	"Harap isi keterangan"; break;
					default					:	$respon_msg	=	"Lengkapi data isian anda"; break;
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
		
		$sqlIns			=	sprintf("INSERT INTO m_report_masalah
									 (NAMA_MASALAH,KETERANGAN,STATUS)
									 VALUES
									 ('%s','%s','1')"
									, $db->db_text($postmasalah)
									, $db->db_text($postketerangan)
									);
		$lastID			=	$db->execSQL($sqlIns, 1);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($lastID > 0){
			
			$body	=	"	<tr id='baris".$enkripsi->encode($lastID)."'>
									<td>".$postmasalah."</td>
									<td align='right'>0</td>
									<td width='100' align='center'>
										<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='getDetail(\"".$enkripsi->encode($lastID)."\")'>
											Detail <i class='fa fa-caret-down'></i>
										</a>
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
		
		if(isset($_POST['masalah']) && $_POST['masalah'] <> ''){
			$conmasalah	=	$_POST['masalah'] == 2 ? "1=1" : "A.NAMA_MASALAH LIKE '%".$_POST['masalah']."%'";
		} else {
			$conmasalah	=	"1=1";
		}
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA MASALAH
		$sql		=	sprintf("SELECT A.IDMASALAH, A.NAMA_MASALAH, A.KETERANGAN, COUNT(B.IDSOLUSI) AS JML_SOLUSI, A.STATUS
								 FROM m_report_masalah A
								 LEFT JOIN (SELECT * FROM m_report_solusi WHERE STATUS = 1) B ON A.IDMASALAH = B.IDMASALAH 
								 WHERE %s AND %s
								 GROUP BY A.IDMASALAH
								 ORDER BY A.NAMA_MASALAH"
								, $conStatus
								, $conmasalah
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDMASALAH) AS TOTDATA FROM (%s) AS A", $sql);
		$resultC	=	$db->query($sqlCount);
		$resultC	=	$resultC[0];
		$totData	=	$resultC['TOTDATA'];
		$totpage	=	ceil($totData / $dataperpage);

		$sqlSel		=	sprintf("SELECT * FROM (%s) AS A 
								 LIMIT %s, %s"
								, $sql
								, $startLimit
								, $dataperpage
								);
		$result		=	$db->query($sqlSel);
		$data		=	'';

		if($result <> '' && $result <> false){
			
			$i			=	1;
			$startData	=	$startLimit + 1;
			if(($startLimit + $dataperpage) > $totData){
				$endData	=	$totData;
			} else {
				$endData	=	$startLimit + $dataperpage;
			}
			
			if($totpage == 1){
				$pagination	=	"<li class='active'><a href='#'>1</a></li>";
			} else {
				
				if($_POST['page'] <> 1 && $totpage > 1){
					$prevPage	=	($_POST['page'] *1) - 1;
					$pagination	.=	"	<li class='previous' onClick='filterData(".$prevPage.")'><a href='#'>&laquo;</a></li>";
				}
				
				for($i=1; $i<=$totpage; $i++){
					if($i == $_POST['page']){
						$pagination	.=	"	<li><a href='#' class='current'> ".$i."</a></li>";
					} else {
						$pagination	.=	"	<li onClick='filterData(".$i.")'><a href='#'>".$i."</a></li>";
					}
				}

				if($_POST['page'] <> $totpage){
					$nextPage	=	($_POST['page'] *1) + 1;
					$pagination	.=	"	<li onClick='filterData(".$nextPage.")'><a href='#'>&raquo;</a></li>";
				}
				
			}
			
			foreach($result as $key){
				
				$status	=	$key['STATUS'] == "1" ? "Non Aktifkan" : "Aktifkan";

				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDMASALAH'])."'>
									<td>".$key['NAMA_MASALAH']."</td>
									<td align='right'>".$key['JML_SOLUSI']."</td>
									<td width='180' align='center'>
										<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='getDetail(\"".$enkripsi->encode($key['IDMASALAH'])."\")'>
											Detail <i class='fa fa-caret-down'></i>
										</a>
										<a href='#' id='nonA".$enkripsi->encode($key['IDMASALAH'])."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($key['IDMASALAH'])."\")'>
											".$status."
										</a>
									</td>
								</tr>";
				$i++;
			}
		} else {
			$data	=	"	<tr><td colspan = '3'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}
	
	//FUNGSI DIGUNAKAN AKTIFKAN / NON AKTIFKAN DATA
	if( $enkripsi->decode($_GET['func']) == "nonAktif" && isset($_GET['func'])){
		
		$iddata			=	$enkripsi->decode($_POST['iddata']);
		$sqlCek			=	sprintf("SELECT STATUS
									 FROM m_report_masalah
									 WHERE IDMASALAH = %s"
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
		
		$sqlUpd			=	sprintf("UPDATE m_report_masalah SET STATUS = %s
									 WHERE IDMASALAH = %s"
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
        <li>Masalah & Solusi Laporan</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Data Masalah dan Solusi Pelaporan Pengajar</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Status</label>
                <span class="field">
                    <input name="status" value="2" id="status1" onclick="filterData(1)" checked type="radio"> Semua &nbsp; 
                    <input name="status" value="1" id="status1" onclick="filterData(1)" type="radio"> Aktif &nbsp; 
                    <input name="status" value="0" id="status2" onclick="filterData(1)" type="radio"> Tidak Aktif
                </span>
            </p>
            <p>
                <label>Masalah</label>
                <span class="field">
					<input type="text" id="masalah" name="masalah" value="" class="smallinput"/>
				</span>
			</p>
        </div>
        <div id="divposting" style="display:none">
            <p>
                <label>Masalah</label>
                <span class="field">
					<input type="text" id="postmasalah" name="postmasalah" value=""/>
				</span>
			</p>
            <p>
                <label>Keterangan</label>
                <span class="field">
					<textarea id="postketerangan" name="postketerangan" style="height: 100px;"></textarea>
				</span>
			</p>
		</div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="tambah" id="tambah" class="submit radius2 pull-right" value="Tambah Data" type="button" onclick="showComposer(true)">
            <input name="filter" id="filter" class="submit radius2 pull-right" value="Saring" type="button" onclick="filterData(1)" style="margin-right:5px">
            <input name="simpan" id="simpan" class="submit radius2 pull-right" value="Simpan" type="button" onclick="submitData()" style="display:none">
            <input name="batal" id="batal" class="reset radius2 pull-right" value="Batal" type="reset" onclick="showComposer(false)" style="display:none; margin-right:5px">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="standart_table" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Masalah</td>
                            <td class="head1" style="text-align:center;">Jumlah Solusi</td>
                            <td class="head0" style="text-align:center;"> </td>
                        </tr>
                    </thead>
                    <tbody id="list_masalah">
                    </tbody>
				</table><br/>
                <div style="float:right">
                    <ul class="pagination" id="pagination" >
                    </ul>
                </div><br/><br/>
            </div>
        </div>
    </div>
</div>
<script>
	function filterData(page){
		var status	=	$('input[name=status]:checked', '#divfilter').val();
			masalah	=	$('#masalah').val();
			
		$('#list_masalah').html("<tr><td colspan = '8'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/104mstmasalah.php?func=<?=$enkripsi->encode('filterData')?>", {status: status, page:page, masalah:masalah})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_masalah').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Masalah Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
	}
	function nonAktif(id){
		$.post( "<?=APP_URL?>page/104mstmasalah.php?func=<?=$enkripsi->encode('nonAktif')?>", {iddata: id})
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				$('#showfilter').html("1 Data Masalah Dinon-aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Aktifkan');
			} else if(data['respon_code'] == '00001'){
				$('#showfilter').html("1 Data Masalah Di Aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Non Aktifkan');
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
	}
	function rmSolusi(id){
		$.post( "<?=APP_URL?>page/104mstmasalah.php?func=<?=$enkripsi->encode('rmSolusi')?>", {iddata: id})
		.done(function( data ) {
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				filterData(1);
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
		});
	}
	function showComposer(status){
		if(status == true){
			$('#divfilter').slideUp('fast');
			$('#divposting').slideDown('fast');
			$('#tambah, #filter').hide();
			$('#simpan, #batal').show();
		} else {
			$('#divposting').slideUp('fast');
			$('#divfilter').slideDown('fast');
			$('#tambah, #filter').show();
			$('#simpan, #batal').hide();
		}
	}
	function submitData(){
	
		var data		=	$("#divposting input, #divposting textarea").serialize();
	
		$("#divposting input, #divposting textarea").prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		
		$.post( "<?=APP_URL?>page/104mstmasalah.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
				$("#divposting input, #divposting textarea").prop('disabled', false);
			} else {
				$("#divposting input, #divposting textarea").prop('disabled', false).val('');
				$('#list_masalah').prepend(data['respon_body']);
				$('#showfilter').html("1 Data Ditambahkan");
				showComposer(false);
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
		
	}
	function saveSolusi(id){
		var data		=	$("#addSolusi"+id+" input, #addSolusi"+id+" textarea").serialize();
	
		$("#addSolusi"+id+" input, #addSolusi"+id+" textarea").prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		
		$.post( "<?=APP_URL?>page/104mstmasalah.php?func=<?=$enkripsi->encode('saveSolusi')?>&id="+id, data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
				$("#addSolusi"+id+" input, #addSolusi"+id+" textarea").prop('disabled', false);
			} else {
				$('.addSolusi').slideUp('fast').remove();
				$('#ul'+id).append(data['respon_body']);
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
	}
	function addSolusi(id){
		$('.addSolusi').slideUp('fast').remove();
		$.post( "<?=APP_URL?>page/104mstmasalah.php?func=<?=$enkripsi->encode('addSolusi')?>", {id:id})
		.done(function( data ) {
			$('#ul'+id).append(data);
			
		});
	}
	function getDetail(id){
		$('.detail-con').slideUp('fast').remove();
		$.post( "<?=APP_URL?>page/104mstmasalah.php?func=<?=$enkripsi->encode('getDetail')?>", {id:id})
		.done(function( data ) {
			$('#baris'+id).after(data);
			
		});
	}
	$(document).ready(function(){
		filterData(1);
	});
</script>