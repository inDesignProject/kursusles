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

	//FUNGSI DIGUNAKAN SIMPAN PERUBAHAN
	if( $enkripsi->decode($_GET['func']) == "saveAll" && isset($_GET['func'])){
		
		$idlevel	=	$enkripsi->decode($_POST['idlevel']);
		$sqlIns		=	sprintf("INSERT INTO admin_menu_level (IDLEVEL,IDMENU)
								 SELECT '%s' AS LEVEL, IDMENU
								 FROM admin_menu
								 ON DUPLICATE KEY UPDATE STATUS = 0"
								, $idlevel
								);
		$db->execSQL($sqlIns, 0);

		foreach($_POST['ck'] as $key => $value){
			
			$idmenu	=	$enkripsi->decode($key);
			$sqlupd	=	sprintf("UPDATE admin_menu_level SET STATUS = 1 WHERE IDMENU = %s AND IDLEVEL = %s", $idmenu, $idlevel);
			$db->execSQL($sqlupd, 0);
			
		}
		
		echo json_encode(array("respon_msg"=>"Data perubahan disimpan"));
		die();
		
	}
	
	//FUNGSI DIGUNAKAN TAMBAH DATA
	if( $enkripsi->decode($_GET['func']) == "addData" && isset($_GET['func'])){
		
		if($_POST['level'] == ''){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Harap masukkan level yang anda inginkan"));
			die();
		}
		
		$level			=	$db->db_text($_POST['level']);
		$sqlcek			=	sprintf("SELECT IDLEVEL FROM admin_level WHERE NAMA = '%s'", $level);
		$result			=	$db->query($sqlcek);
		
		if($result <> '' && $result <> false){
			
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Data level sudah ada sebelumnya"));
			die();
			
		} else {
		
			$sqlIns			=	sprintf("INSERT INTO admin_level SET NAMA = '%s'"
										, $level
										);
			$lastID			=	$db->execSQL($sqlIns, 1);
	
			//JIKA DATA SUDAH MASUK, KIRIM RESPON
			if($lastID > 0){
				echo json_encode(array("respon_code"=>'00000', 'respon_msg'=>'Data tersimpan', 'respon_body'=>$body));
				die();
			} else {
				echo json_encode(array("respon_code"=>'00003', 'respon_msg'=>'Gagal menyimpan data. Coba lagi nanti'));
				die();
			}
		}
		
	}

	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		if(isset($_POST['level']) && $_POST['level'] <> ''){
			$level	=	$enkripsi->decode($_POST['level']);
		} else {
			$data	=	"<tr><td colspan='3'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Harap pilih level terlebih dahulu", "totData"=> '0', "respon"=>$data, "startData"=>0, "endData"=>0, "pagination"=>''));
			die();
		}
		
		$dataperpage=	100;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA
		$sql		=	sprintf("SELECT B.NAMA_MENU, B.GROUPMENU, B.IDMENU, A.STATUS
								 FROM admin_menu B
								 LEFT JOIN (SELECT * FROM admin_menu_level WHERE IDLEVEL = %s) A ON B.IDMENU = A.IDMENU
								 ORDER BY B.ORDERGROUP, B.ORDERMENU"
								, $level
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDMENU) AS TOTDATA FROM (%s) AS A", $sql);
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
			
			$hiddenInput	=	"<input type='hidden' name='idlevel' id='idlevel' value='".$_POST['level']."'/>";
			foreach($result as $key){
				$checked=	$key['STATUS'] == 1 ? "checked" : "";
				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDMENU'])."'>
									<td>".$key['GROUPMENU']."</td>
									<td>".$key['NAMA_MENU']."</td>
									<td width='50' align='center'>
										<input ".$checked." type='checkbox' name='ck[".$enkripsi->encode($key['IDMENU'])."]' id='ck".$enkripsi->encode($key['IDMENU'])."' value='1' /> 
									</td>
								</tr>";
				if($i == 1){$data .= $hiddenInput;}
				$i++;
			}
		} else {
			$data	=	"	<tr><td colspan = '3'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
		}
		
		echo json_encode(array("respon_code"=>"00000", "totData"=>$totData, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}	
	
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-wrench"></i> Pengaturan</a></li>
        <li>Menu Level</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Atur Menu Level User Admin</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Level</label>
                <span class="field">
					<select id="filterlevel" name="filterlevel" class="form-control" onchange="filterData(1)" >
                    	<option value="">- Pilih Level -</option>
                    </select>
				</span>
			</p>
        </div>
        <div id="divposting" style="display:none">
            <p>
                <label>Level</label>
                <span class="field">
					<input type="text" id="level" name="level" maxlength="25" class="smallinput" />
				</span>
			</p>
		</div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="tambah" id="tambah" class="submit radius2 pull-right" value="Tambah Level" type="button" onclick="showComposer(true)">
            <input name="simpanall" id="simpanall" class="submit radius2 pull-right" value="Simpan Perubahan" type="button" onclick="saveAll()">
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
                            <td class="head0" style="text-align:center;">Menu</td>
                            <td class="head1" style="text-align:center;">Sub Menu</td>
                            <td class="head0" style="text-align:center;">Cek</td>
                        </tr>
                    </thead>
                    <tbody id="list_data">
                    	<tr><td colspan="3"><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>
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
		var level	=	$('#filterlevel').val();
			
		$('#message_response_container').slideDown('fast').html("");
		$('#list_data').html("<tr><td colspan = '3'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/603menulevel.php?func=<?=$enkripsi->encode('filterData')?>", {level:level, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			
			if(data['respon_code'] != '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}

			$('#list_data').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			
		});
	}
	function saveAll(){

		var data		=	$("#list_data input").serialize();
	
		$("#list_data input").prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		
		$.post( "<?=APP_URL?>page/603menulevel.php?func=<?=$enkripsi->encode('saveAll')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			$("#list_data input").prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			
		});
		
	}
	function submitData(){

		var data		=	$("#divposting input").serialize();
	
		$("#divposting input").prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		
		$.post( "<?=APP_URL?>page/603menulevel.php?func=<?=$enkripsi->encode('addData')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$("#divposting input").prop('disabled', false);
			} else {
				$("#divposting input").prop('disabled', false).val('');
				$('#showfilter').html("1 Data Ditambahkan");
				showComposer(false);
				getDataOpt('getDataLevel','noparam','filterlevel','','- Pilih Level -');
			}
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			
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
	$(document).ready(function(){
		getDataOpt('getDataLevel','noparam','filterlevel','','- Pilih Level -');
	});
</script>