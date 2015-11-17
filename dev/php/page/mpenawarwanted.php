<?php

include('../lib/db_connection.php');
include('../lib/enkripsi.php');
include('../lib/session.php');
require "../lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

session_start();

$sql = '';
	$sql .= 'select idmurid from m_murid where iduser ='.$_SESSION['KursusLes']['IDPRIME'];
	
	$ismurid	=	$db->query($sql);
	

$idmurid	=	$_GET['q'] == "" && !isset($_GET['q']) ? $ismurid[0]['idmurid'] : $enkripsi->decode($_GET['q']) ;

$sql		=	sprintf("SELECT A.IDWANTED, A.JUDUL, A.HARGA, A.JENIS, A.JML_PERTEMUAN, A.JML_MURID, A.TGL_BATAS,
								COUNT(B.IDWANTEDIN) AS TOT_PENAWAR
						 FROM t_wantedlist A
						 LEFT JOIN t_wantedlist_in B ON A.IDWANTED = B.IDWANTED
						 WHERE A.IDMURID = %s
						 GROUP BY A.IDWANTED
						 ORDER BY A.TGL_BATAS DESC"
						, $idmurid
						);
$result		=	$db->query($sql);

if($result <> false && $result <> ""){
	foreach($result as $key){
		$jenis	=	$key['JENIS'] == 1 ? "Privat" : "Grup";
		$data	.=	"<tr id='row".$enkripsi->encode($key['IDWANTED'])."' class='rowdata'>
					 	<td>".$key['JUDUL']."</td>
					 	<td align='right'>Rp. ".number_format($key['HARGA'],0,",",".")."</td>
					 	<td>".$jenis."</td>
					 	<td>".$key['JML_PERTEMUAN']."</td>
					 	<td>".$key['JML_MURID']."</td>
					 	<td align='center'>".date("Y-m-d",strtotime($key['TGL_BATAS']))."</td>
					 	<td align='center'><input type='button' value='Lihat Penawar [".$key['TOT_PENAWAR']."]' class='btn btn-sm btn-custom2' onclick='showListPenawar(\"".$enkripsi->encode($key['IDWANTED'])."\")'></td>
					 </tr>";
	}
} else {
	$data	=	"<tr id='rownodata'><td colspan ='7'><center>Tidak ada data</center></td></tr>";
}

//FUNGSI DIGUNAKAN UNTUK FILTER DATA
if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
	
	//CEK KIRIMAN TANGGAL BATAS
	if($_POST['tglbatas'] == '' || !isset($_POST['tglbatas'])){
		$contgl	=	"1=1";
	} else {
		$contgl	=	"A.TGL_BATAS = '".$_POST['tglbatas']."'";
	}
	
	//CEK KIRIMAN JENIS
	if($_POST['jns_paket'] == '0' || !isset($_POST['jns_paket'])){
		$conjns	=	"1=1";
	} else {
		$conjns	=	"A.JENIS = '".$_POST['jns_paket']."'";
	}

	//CEK KIRIMAN STATUS
	if($_POST['status'] == '-2' || !isset($_POST['status'])){
		$constat=	"1=1";
	} else {
		$constat=	"A.STATUS = '".$_POST['status']."'";
	}

	$sqlSel		=	sprintf("SELECT A.IDWANTED, A.JUDUL, A.HARGA, A.JENIS, A.JML_PERTEMUAN, A.JML_MURID, A.TGL_BATAS,
									COUNT(B.IDWANTEDIN) AS TOT_PENAWAR
							 FROM t_wantedlist A
							 LEFT JOIN t_wantedlist_in B ON A.IDWANTED = B.IDWANTED
							 WHERE A.IDMURID = %s AND %s AND %s AND %s
							 GROUP BY A.IDWANTED
							 ORDER BY A.TGL_BATAS DESC"
							, $idmurid
							, $conjns
							, $constat
							, $contgl
							);
	$resultSel	=	$db->query($sqlSel);

	if($resultSel <> false && $resultSel <> ""){
		foreach($resultSel as $key){
			$jenis	=	$key['JENIS'] == 1 ? "Privat" : "Grup";
			$dataR	.=	"<tr id='row".$enkripsi->encode($key['IDWANTED'])."' class='rowdata'>
							<td>".$key['JUDUL']."</td>
							<td align='right'>Rp. ".number_format($key['HARGA'],0,",",".")."</td>
							<td>".$jenis."</td>
							<td>".$key['JML_PERTEMUAN']."</td>
							<td>".$key['JML_MURID']."</td>
							<td align='center'>".date("Y-m-d",strtotime($key['TGL_BATAS']))."</td>
					 		<td align='center'><input type='button' value='Lihat Penawar [".$key['TOT_PENAWAR']."]' class='btn btn-sm btn-custom2' onclick='showListPenawar(\"".$enkripsi->encode($key['IDWANTED'])."\")'></td>
						 </tr>";
		}
	} else {
		$dataR	=	"<tr id='rownodata'><td colspan ='7'><center>Tidak ada data ditemukan</center></td></tr>";
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
<div class="boxSquareWhite">
    <h4>Saring Data</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="form-filter">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="form-group">
                        <input type="text" name="tglbatas" id="tglbatas" class="form-control" maxlength="10" placeholder="Tanggal Batas Penawaran" autocomplete="off" readonly />
                    </div>
            	</div>
            </div>
            <div class="form-group">
                Jenis Paket &nbsp; &nbsp;
                <input name="jns_paket" value="0" id="jns_paket1" type="radio" checked/> <small>Semua</small> &nbsp; 
                <input name="jns_paket" value="1" id="jns_paket1" type="radio" /> <small>Privat</small> &nbsp; 
                <input name="jns_paket" value="2" id="jns_paket2" type="radio" /> <small>Grup</small>
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
    <h4>Data Penawaran Kursus</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered" id="Kjadwal_table">
            <thead>
                <tr>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Harga</th>
                    <th class="text-center">Jenis</th>
                    <th class="text-center">Jumlah<br/>Pertemuan</th>
                    <th class="text-center">Jumlah<br/>Murid</th>
                    <th class="text-center">Tgl Batas</th>
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
	getDataOpt('getDataJenjang','noparam','idjenjang','','- Pilih Jenjang Pendidikan -');
	$('#tglbatas').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		minDate: 0,
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
	
		$('#bodyData').html("<tr id='rownodata'><td colspan ='7'><center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>");
		var sendData = $('#form-filter input, #form-filter radio').serialize();
		$('#form-filter input, #form-filter radio').prop('disabled', true);
		
		$.post( "<?=APP_URL?>php/page/mpenawarwanted.php?func=<?=$enkripsi->encode('filterData')?>", sendData)
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
	function showListPenawar(value){
		$('.rowDetail').slideUp('fast').remove();
		$('#bodyData').find('tr').removeClass('rowData-show');
		$("#row"+value).after("<tr class='rowDetail'><td colspan ='7' id='con-"+value+"'><center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>").addClass('rowData-show');
		
		$.post( "<?=APP_URL?>php/page/mpenawarwanted.php?func=<?=$enkripsi->encode('detailPenawar')?>", {iddata : value})
		.done(function( data ) {
			$("#con-"+value).html(data);
		});
		
	}
</script>