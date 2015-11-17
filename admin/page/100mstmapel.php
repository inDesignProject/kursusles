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
					case "jenjang"		:	$respon_msg	=	"Harap pilih jenjang"; break;
					case "namamapel"	:	$respon_msg	=	"Harap isi nama mata pelajaran"; break;
					case "kode"			:	$respon_msg	=	"Harap isi kode mata pelajaran"; break;
					default				:	$respon_msg	=	"Lengkapi data isian anda"; break;
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
		
		$idjenjang		=	$enkripsi->decode($jenjang);
		$sqlIns			=	sprintf("INSERT INTO m_mapel
									 (IDJENJANG,NAMA_MAPEL,KODE_MAPEL,STATUS)
									 VALUES
									 ('%s','%s','%s','1')"
									, $idjenjang
									, $namamapel
									, $kode
									);
		$lastID			=	$db->execSQL($sqlIns, 1);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($lastID > 0){
			
			$sqljnjang	=	sprintf("SELECT NAMA_JENJANG FROM m_jenjang WHERE IDJENJANG = %s LIMIT 0,1", $idjenjang);
			$resjnjang	=	$db->query($sqljnjang);
			$resjnjang	=	$resjnjang[0];
			$namajnjang	=	$resjnjang['NAMA_JENJANG'];
			
			$body	=	"	<tr id='baris".$enkripsi->encode($lastID)."'>
									<td>".$namajnjang."</td>
									<td>".$namamapel."</td>
									<td>".$kode."</td>
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
		
		if(isset($_POST['jenjang']) && $_POST['jenjang'] <> ''){
			$conJenjang	=	$_POST['jenjang'] == 2 ? "1=1" : "A.IDJENJANG = ".$enkripsi->decode($_POST['jenjang']);
		} else {
			$conJenjang	=	"1=1";
		}
		
		switch($_POST['urutan']){
			case "1"	:	$urutan	=	"A.NAMA_MAPEL"; break;
			case "2"	:	$urutan	=	"B.NAMA_JENJANG"; break;
			case "3"	:	$urutan	=	"A.STATUS DESC"; break;
			default		:	$urutan	=	"A.STATUS DESC, A.NAMA_MAPEL"; break;
		}
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA MAPEL
		$sql		=	sprintf("SELECT A.IDMAPEL, B.NAMA_JENJANG, A.NAMA_MAPEL, A.KODE_MAPEL, A.STATUS
								 FROM m_mapel A
								 LEFT JOIN m_jenjang B ON A.IDJENJANG = B.IDJENJANG
								 WHERE %s AND %s
								 ORDER BY %s"
								, $conStatus
								, $conJenjang
								, $urutan
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDMAPEL) AS TOTDATA FROM (%s) AS A", $sql);
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

				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDMAPEL'])."'>
									<td>".$key['NAMA_JENJANG']."</td>
									<td>".$key['NAMA_MAPEL']."</td>
									<td>".$key['KODE_MAPEL']."</td>
									<td width='100' align='center'>
										<a href='#' id='nonA".$enkripsi->encode($key['IDMAPEL'])."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($key['IDMAPEL'])."\")'>
											".$status."
										</a>
									</td>
								</tr>";
				$i++;
			}
		} else {
			$data	=	"	<tr><td colspan = '7'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}	

	//FUNGSI DIGUNAKAN AKTIFKAN / NON AKTIFKAN DATA
	if( $enkripsi->decode($_GET['func']) == "nonAktif" && isset($_GET['func'])){
		
		$iddata			=	$enkripsi->decode($_POST['iddata']);
		$sqlCek			=	sprintf("SELECT STATUS
									 FROM m_mapel
									 WHERE IDMAPEL = %s"
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
		
		$sqlUpd			=	sprintf("UPDATE m_mapel SET STATUS = %s
									 WHERE IDMAPEL = %s"
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
        <li>Mata Pelajaran</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Master Data Jenjang dan Mata Pelajaran</h1>
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
                <label>Jenjang</label>
                <span class="field">
					<select id="filterjenjang" name="filterjenjang" class="form-control" onchange="filterData(1)" >
                    	<option value="">- Pilih Jenjang -</option>
                    </select>
				</span>
			</p>
            <p>
                <label>Urutan</label>
                <span class="field">
					<select id="urutan" name="urutan" class="form-control" onchange="filterData(1)" >
                    	<option value="1">Nama Pelajaran</option>
                    	<option value="2">Jenjang</option>
                    	<option value="3">Status</option>
                    </select>
				</span>
			</p>
        </div>
        <div id="divposting" style="display:none">
            <p>
                <label>Jenjang</label>
                <span class="field">
					<select id="jenjang" name="jenjang" class="form-control">
                    	<option value="">- Pilih Jenjang -</option>
                    </select>
				</span>
			</p>
            <p>
                <label>Nama Mata Pelajaran</label>
                <span class="field">
					<input type="text" id="namamapel" name="namamapel" maxlength="25" class="mediuminput" />
				</span>
			</p>
            <p>
                <label>Kode</label>
                <span class="field">
					<input type="text" id="kode" name="kode" maxlength="6" class="smallinput" />
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
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="standart_table" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head0" style="text-align:center;">Jenjang</td>
                            <td class="head1" style="text-align:center;">Nama Mata Pelajaran</td>
                            <td class="head0" style="text-align:center;">Kode</td>
                            <td class="head1" style="text-align:center;"> </td>
                        </tr>
                    </thead>
                    <tbody id="list_mapel">
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
	function nonAktif(id){
		$.post( "<?=APP_URL?>page/100mstmapel.php?func=<?=$enkripsi->encode('nonAktif')?>", {iddata: id})
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				$('#showfilter').html("1 Data Mata Pelajaran Dinon-aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Aktifkan');
			} else if(data['respon_code'] == '00001'){
				$('#showfilter').html("1 Data Mata Pelajaran Di Aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Non Aktifkan');
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
	}
	function filterData(page){
		var status	=	$('input[name=status]:checked', '#divfilter').val();
			jenjang	=	$('#filterjenjang').val();
			urutan	=	$('#urutan').val();

		$('#list_mapel').html("<tr><td colspan = '7'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/100mstmapel.php?func=<?=$enkripsi->encode('filterData')?>", {status: status, jenjang:jenjang, urutan:urutan, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_mapel').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Mata Pelajaran Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
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
		
		$.post( "<?=APP_URL?>page/100mstmapel.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
				$("#divposting input, #divposting select").prop('disabled', false);
				getDataOpt('getDataJenjang','noparam','jenjang','','- Pilih Jenjang -');
			} else {
				$("#divposting input, #divposting select").prop('disabled', false).val('');
				getDataOpt('getDataJenjang','noparam','jenjang','','- Pilih Jenjang -');
				$('#list_mapel').prepend(data['respon_body']);
				$('#showfilter').html("1 Data Ditambahkan");
				showComposer(false);
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
		
	}

	$(document).ready(function(){
		filterData(1);
		getDataOpt('getDataJenjang','noparam','jenjang','','- Pilih Jenjang -');
		getDataOpt('getDataJenjang','noparam','filterjenjang','','- Pilih Jenjang -');
	});

</script>