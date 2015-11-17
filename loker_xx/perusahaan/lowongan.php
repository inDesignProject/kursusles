<?php

	include('../php/lib/db_connection.php');
	include('../php/lib/enkripsi.php');
	include('../php/lib/session.php');
	require "../php/lib/defines.php";

	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	$idpers		=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLesLoker']['IDUSER'] : $enkripsi->decode($_GET['q']) ;

	//FUNGSI DIGUNAKAN UNTUK FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		//CEK KIRIMAN TANGGAL BATAS
		if($_POST['tglposting'] == '' || !isset($_POST['tglposting'])){
			$contgl	=	"1=1";
		} else {
			$contgl	=	"A.TGL_PUBLISH = '".$_POST['tglposting']."'";
		}
		
		//CEK KIRIMAN STATUS
		if($_POST['status'] == '-2' || !isset($_POST['status'])){
			$constat=	"1=1";
		} else {
			$constat=	"A.STATUS = '".$_POST['status']."'";
		}
	
		$sqlSel		=	sprintf("SELECT A.TGL_PUBLISH, A.TGL_KADALUARSA, A.JUDUL, B.TOTPELAMAR, A.IDLOWONGAN
								 FROM t_lowongan A
								 LEFT JOIN
										(SELECT COUNT(IDLAMARAN) AS TOTPELAMAR, IDLOWONGAN FROM t_lamaran GROUP BY IDLOWONGAN) 
								 B ON A.IDLOWONGAN = B.IDLOWONGAN
								 WHERE A.IDPERUSAHAAN = %s AND %s AND %s
								 ORDER BY A.TGL_PUBLISH DESC"
								, $idpers
								, $constat
								, $contgl
								);
		$resultSel	=	$db->query($sqlSel);
	
		if($resultSel <> false && $resultSel <> ""){
			foreach($resultSel as $key){
				$dataR	.=	"<tr id='row".$enkripsi->encode($key['IDLOWONGAN'])."' class='rowdata'>
								<td align='center'>".$key['TGL_PUBLISH']."</td>
								<td align='center'>".$key['TGL_KADALUARSA']."</td>
								<td>".$key['JUDUL']."</td>
								<td align='center'><input type='button' value='Lihat Pelamar [".$key['TOTPELAMAR']."]' class='btn btn-sm btn-custom2' onclick='showListPenawar(\"".$enkripsi->encode($key['IDLOWONGAN'])."\")'></td>
							 </tr>";
			}
		} else {
			$dataR	=	"<tr id='rownodata'><td colspan ='4'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
		}
	
		echo json_encode(array("respon_code"=>"00000", "respon_msg"=>$dataR));
		die();
	}
	
	//FUNGSI DIGUNAKAN UNTUK DETAIL DATA PENAWAR
	if( $enkripsi->decode($_GET['func']) == "detailPenawar" && isset($_GET['func'])){
		
		$idwanted	=	$enkripsi->decode($_POST['iddata']);
		$sqlDetail	=	sprintf("SELECT B.NAMA, B.FOTO, A.KETERANGAN, C.NAMA_PAKET, C.IDPAKET, A.TGL_INSERT, B.IDPENGAJAR
								 FROM t_wantedlist_in A
								 LEFT JOIN m_pengajar B ON A.IDPENGAJAR = B.IDPENGAJAR
								 LEFT JOIN t_paket C ON A.IDPAKET = C.IDPAKET
								 WHERE A.IDWANTED = %s
								 ORDER BY A.TGL_INSERT"
								, $idwanted
								);
		$resultDet	=	$db->query($sqlDetail);
	
		if($resultDet <> false && $resultDet <> ""){
			
			foreach($resultDet as $key){
				$foto	=	$enkripsi->encode($key['FOTO']);
				$idp	=	$enkripsi->encode($key['IDPENGAJAR']);
				$idpkt	=	$enkripsi->encode($key['IDPAKET']);
				$paket	=	$key['NAMA_PAKET'] == "" || $key['NAMA_PAKET'] == "null" ? "-" : $key['NAMA_PAKET'];
				$dataD	.=	"<div class='boxSquareWhite'>
								<div class='row'>
									<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
										<a href='".APP_URL."pengajar_profil.php?q=".$idp."' target='_blank'>
											<img src='".APP_IMG_URL."generate_pic.php?type=pr&q=".$foto."&w=48&h=48' class='img-circle'/>
											<b>".$key['NAMA']."</b><br/>
										</a><br/>
										Pada : 
										<i class='fa fa-calendar'></i><small> ".date('Y-m-d',strtotime($key['TGL_INSERT']))."</small>
										<i class='fa fa-clock-o'></i><small> ".date('H:i:s',strtotime($key['TGL_INSERT']))."</small>
										<p>&ldquo;".$key['KETERANGAN']."&rdquo;</p>
										Paket Ditawarkan  : <i class='fa fa-envelope'></i> ".$paket."
										<a href='detail_paket?q=".$idp."&idp=".$idpkt."' class='btn btn-custom btn-xs pull-right' target='_blank'>Pilih >> </a>
									</div>
								</div>
							</div>
							<br/>";
			}
			
		} else {
			$dataD	.=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
		}
		
		echo $dataD;
		die();
	}

	$sql		=	sprintf("SELECT A.TGL_PUBLISH, A.TGL_KADALUARSA, A.JUDUL, B.TOTPELAMAR, A.IDLOWONGAN
							 FROM t_lowongan A
							 LEFT JOIN
							 		(SELECT COUNT(IDLAMARAN) AS TOTPELAMAR, IDLOWONGAN FROM t_lamaran GROUP BY IDLOWONGAN) 
							 B ON A.IDLOWONGAN = B.IDLOWONGAN
							 WHERE A.IDPERUSAHAAN = %s
							 ORDER BY A.TGL_PUBLISH DESC"
							, $idpers
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		foreach($result as $key){
			$data	.=	"<tr id='row".$enkripsi->encode($key['IDLOWONGAN'])."' class='rowdata'>
							<td align='center'>".$key['TGL_PUBLISH']."</td>
							<td align='center'>".$key['TGL_KADALUARSA']."</td>
							<td>".$key['JUDUL']."</td>
							<td align='center'><input type='button' value='Lihat Pelamar [".$key['TOTPELAMAR']."]' class='btn btn-sm btn-custom2' onclick='showListPenawar(\"".$enkripsi->encode($key['IDLOWONGAN'])."\")'></td>
						 </tr>";
		}
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='4'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
	}
	
?>
<style>
	.rowDetail{
		border-bottom: 3px solid #ccc;
		border-left: 3px solid #ccc;
		border-right: 3px solid #ccc;
		max-height: 300px;
		overflow:scroll;
	}
	.rowData-show{
		border-top: 3px solid #ccc;
		border-left: 3px solid #ccc;
		border-right: 3px solid #ccc;
	}
</style>
<input type="button" value="Posting Lowongan Baru" class="btn btn-sm btn-custom2 pull-right" onclick="window.open('<?=APP_URL?>posting_job', '_blank');"><br/><br/>
<div class="boxSquareWhite">
    <h4>Saring Data</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="form-filter">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="tglposting" id="tglposting" class="form-control" maxlength="10" placeholder="Tanggal Posting" autocomplete="off" readonly />
                    </div>
            	</div>
            </div>
            <div class="form-group">
                Status &nbsp; &nbsp;
                <input name="status" value="-2" id="status1" type="radio" checked/> <small>Semua</small> &nbsp; 
                <input name="status" value="1" id="status1" type="radio" /> <small>Aktif</small> &nbsp; 
                <input name="status" value="0" id="status2" type="radio" /> <small>Tidak Aktif</small>
            </div>
            <div class="form-group">
                <input type="button" value="Saring" class="btn btn-sm btn-custom2" onclick="applyFilter()">
			</div>
    	</div>
	</div>
</div>
<hr />
<div class="boxSquareWhite">
    <h4>Data Lowongan Anda</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered" id="Kjadwal_table">
            <thead>
                <tr>
                    <th class="text-center">Tgl Posting</th>
                    <th class="text-center">Tgl Batas</th>
                    <th class="text-center">Judul</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody id="bodyData">
            	<?=$data?>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssvalidate');?>.jsfile"></script>
<script>
	$('#tglposting').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		lang:'id',
		closeOnDateSelect:true
	});
	function generateMsg(msg){
		return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
				"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
				"<strong><small id='message_response'>"+msg+"</small></strong>"+
			"</div>";
	}
	function applyFilter(){
	
		$('#bodyData').html("<tr id='rownodata'><td colspan ='4'><center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		var sendData = $('#form-filter input, #form-filter radio').serialize();
		$('#form-filter input, #form-filter radio').prop('disabled', true);
		
		$.post( "<?=APP_URL?>perusahaan/lowongan.php?func=<?=$enkripsi->encode('filterData')?>", sendData)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				$('#bodyData').html(data['respon_msg']);
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			$('#form-filter input, #form-filter radio').prop('disabled', false);
		});
	
	}
	function showListPelamar(value){
		$('.rowDetail').slideUp('fast').remove();
		$('#bodyData').find('tr').removeClass('rowData-show');
		$("#row"+value).after("<tr class='rowDetail'><td colspan ='4' id='con-"+value+"'><center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>").addClass('rowData-show');
		
		$.post( "<?=APP_URL?>perusahaan/lowongan.php?func=<?=$enkripsi->encode('detailPelamar')?>", {iddata : value})
		.done(function( data ) {
			$("#con-"+value).html(data);
		});
		
	}
</script>