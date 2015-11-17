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
	
	//FUNGSI DIGUNAKAN SIMPAN BALASAN
	if( $enkripsi->decode($_GET['func']) == "saveBalas" && isset($_GET['func'])){
		
		if($_POST['balasan'] == '' || !isset($_POST['balasan'])){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Harap isi pesan balasan"));
			die();
		}
		
		$sqlupd		=	sprintf("UPDATE t_helpdesk
								 SET BALASAN	=	'%s',
								 	 TGL_BALAS	=	NOW(),
									 IDUSERBALAS=	%s"
								, $db->db_text($_POST['balasan'])
								, $_SESSION['KursusLesAdmin']['IDUSER']
								);
		$affected	=	$db->execSQL($sqlupd, 0);
		
		if($affected > 0){
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Balasan tersimpan"));
		} else {
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Gagal menyimpan. Silakan coba lagi nanti"));
		}
		die();		
	}
	
	//FUNGSI DIGUNAKAN LIHAT DETAIL
	if( $enkripsi->decode($_GET['func']) == "getDetail" && isset($_GET['func'])){
		
		$idhelpdek	=	$enkripsi->decode($_POST['id']);
		$sql		=	sprintf("SELECT B.NAMA, A.TYPE_PENGIRIM, A.SUBYEK, A.TGL_KIRIM, A.PESAN, A.BALASAN, A.TGL_BALAS, C.USERNAME
								 FROM t_helpdesk A
								 LEFT JOIN m_murid B ON A.IDPENGIRIM = B.IDMURID
								 LEFT JOIN admin_user C ON A.IDUSERBALAS = C.IDUSER
								 WHERE A.IDHELPDESK = %s
								 LIMIT 0,1"
								, $idhelpdek
								);
		$result		=	$db->query($sql);
		$result		=	$result[0];
		$statuspeng	=	$result['TYPE_PENGIRIM'] == "1" ? "Pengajar" : "Murid";
		
		if($result['USERNAME'] <> '' && $result['USERNAME'] <> 'null'){

			$databalas	=	"<h4>Data Balasan</h4>
							 Subyek : ".$result['SUBYEK']."<br/>
							 Dikirim pada : ".$result['TGL_KIRIM']."<br/>
							 Pesan Balasan :<br/>
							 ".$result['BALASAN'];

		} else {
			$databalas	=	"<h4>Balas Pesan</h4>
							 <textarea id='balasan' name='balasan' style='height: 100px; width: 75%'></textarea><br/>
							 <a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='saveBalas(\"".$_POST['id']."\")'>
								<i class='fa fa-floppy-o'></i> Balas
							 </a>";
		}
		
		echo "	<tr class='detail-con'>
					<td style='padding: 24px;' colspan='5'>
						<h4>Data Kiriman</h4>
						Dari : ".$result['NAMA']."<br/>
						Status Pengirim : ".$statuspeng."<br/>
						Subyek : ".$result['SUBYEK']."<br/>
						Dikirim pada : ".$result['TGL_KIRIM']."<br/><br/>
						Pesan :
						<p>
							".$result['PESAN']."
						</p><br/>
						".$databalas."
					</td>
				</tr>";
		die();
	}

	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA
		$sql		=	sprintf("SELECT A.TGL_KIRIM, B.NAMA, A.SUBYEK, C.USERNAME, A.IDHELPDESK
								 FROM t_helpdesk A
								 LEFT JOIN m_murid B ON A.IDPENGIRIM = B.IDMURID
								 LEFT JOIN admin_user C ON A.IDUSERBALAS = C.IDUSER
								 WHERE A.TGL_KIRIM BETWEEN '%s' AND '%s'
								 ORDER BY A.TGL_KIRIM DESC"
								, $_POST['tglfrom']
								, $_POST['tglto']
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDHELPDESK) AS TOTDATA FROM (%s) AS A", $sql);
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
				
				$data	.=	"	<tr id='baris".$enkripsi->encode($key['IDHELPDESK'])."'>
									<td align='center'>".$key['TGL_KIRIM']."</td>
									<td>".$key['NAMA']."</td>
									<td>".$key['SUBYEK']."</td>
									<td>".$key['USERNAME']."</td>
									<td width='75' align='center'>
										<a href='#' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='getDetail(\"".$enkripsi->encode($key['IDHELPDESK'])."\")'>
											<i class='fa fa-arrow-circle-down'></i> Detail
										</a>
									</td>
								</tr>";
				$i++;
			}
		} else {
			$data		=	"	<tr><td colspan = '5'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
			$startData	=	0;
			$endData	=	0;
		}
		
		echo json_encode(array("totData"=>$totData *1, "respon"=>$data, "startData"=>$startData *1, "endData"=>$endData *1, "pagination"=>$pagination));
		die();
	}
?>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-desktop"></i> Monitoring</a></li>
        <li>Helpdesk</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Kiriman Pengguna Pada Helpdesk</h1>
</div>
<div id="contentwrapper" class="elements">
    <form method="post" enctype="multipart/form-data" class="stdform" action="">
        <div id="divfilter">
            <p>
                <label>Tanggal Kiriman</label>
                <span class="field">
                	<input type="text" name="tglfrom" id="tglfrom" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-01')?>" style="width:75px; text-align: center;" />
                     s.d 
                	<input type="text" name="tglto" id="tglto" maxlength="10" autocomplete="off" readonly value="<?=date('Y-m-t')?>" style="width:75px; text-align: center;" />
                </span>
            </p>
        </div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="filter" id="filter" class="submit radius2 pull-right" value="Saring" type="button" onclick="filterData(1)" style="margin-left: 5px;">
        </div>
	</form><br/>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
                <div id="rsh" style="margin:4px; font-weight:bold"><code id="rshow"> Menampilkan data: 1 s.d 1 dari 1</code></div>
            	<table id="table_data" class="stdtable" border="0" cellspacing="0" style="margin: 0 4px;width: 99%;">
                    <thead>
                        <tr align="center" style="font-weight:bold;">
                            <td class="head1" style="text-align:center;">Tanggal</td>
                            <td class="head0" style="text-align:center;">Pengirim</td>
                            <td class="head1" style="text-align:center;">Subyek</td>
                            <td class="head0" style="text-align:center;">Dibalas Oleh</td>
                            <td class="head1" style="text-align:center;"></td>
                        </tr>
                    </thead>
                    <tbody id="list_data">
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
	$('#tglfrom, #tglto').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		lang:'id',
		closeOnDateSelect:true
	});
	function filterData(page){
		var tglfrom	=	$('#tglfrom').val();
			tglto	=	$('#tglto').val();

		$('#list_data').html("<tr><td colspan = '5'><center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		$.post( "<?=APP_URL?>page/404helpdesk.php?func=<?=$enkripsi->encode('filterData')?>", {tglfrom: tglfrom, tglto: tglto, page:page})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#list_data').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Ditemukan");
			$('#rsh').html("Menampilkan data: "+data['startData']+" s.d "+data['endData']+" dari "+data['totData']);
			$('#pagination').html(data['pagination']);
			
		});
		return true;
	}
	function getDetail(id){
		$('.detail-con').slideUp('fast').remove();
		$.post( "<?=APP_URL?>page/404helpdesk.php?func=<?=$enkripsi->encode('getDetail')?>", {id:id})
		.done(function( data ) {
			$('#baris'+id).after(data);
			
		});
	}
	function saveBalas(id){
		var balasan	=	$('#balasan').val();

		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		$('#balasan').prop('disabled', true);
		$.post( "<?=APP_URL?>page/404helpdesk.php?func=<?=$enkripsi->encode('saveBalas')?>", {id: id, balasan:balasan})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#balasan').prop('disabled', false);
			} else {
				filterData(1);
			}

			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			
		});
		return true;
	}
	$(document).ready(function(){
		filterData(1);
	});
</script>