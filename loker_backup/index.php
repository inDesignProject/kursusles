<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login	=	$session->cekSession() == 2 ? "false" : "true";
	
	//LIST LOWONGAN TERBARU
	$sqllistlow	=	sprintf("SELECT IDLOWONGAN, JUDUL FROM t_lowongan WHERE STATUS = 1 ORDER BY IDLOWONGAN DESC LIMIT 0,5");
	$reslistlow	=	$db->query($sqllistlow);
	
	if($reslistlow <> '' && $reslistlow <> false){
		
		$listlow	=	'';
		foreach($reslistlow as $key){
			$idlow	=	$enkripsi->encode($key['IDLOWONGAN']);
			$listlow.=	"<a href='".APP_URL."lowongan?i=".$idlow."' class='list-group-item' target='_blank'>".$key['JUDUL']."</a>";
		}
		
	} else {
		$listlow	=	"<a href='#' class='list-group-item'><center><b>- Tidak ada data -</b></center></a>";
	}
	
	//JUMLAH PENCARI KERJA
	$sqlpenk	=	sprintf("SELECT COUNT(IDKARYAWAN) AS JML_PENCARI FROM m_karyawan WHERE STATUS = 1");
	$respenk	=	$db->query($sqlpenk);
	$totpenk	=	$respenk[0]['JML_PENCARI'];

	//JUMLAH PEMBERI KERJA
	$sqlpemk	=	sprintf("SELECT COUNT(IDPERUSAHAAN) AS JML_PEMBERI FROM m_perusahaan WHERE STATUS = 1");
	$respemk	=	$db->query($sqlpemk);
	$totpemk	=	$respemk[0]['JML_PEMBERI'];

	//JUMLAH LOWONGAN
	$sqllowk	=	sprintf("SELECT COUNT(IDLOWONGAN) AS JML_LOWONGAN FROM t_lowongan WHERE STATUS = 1");
	$reslowk	=	$db->query($sqllowk);
	$totlowk	=	$reslowk[0]['JML_LOWONGAN'];

	//JUMLAH LOWONGAN PER BIDANG
	$sqlbidang	=	sprintf("SELECT COUNT(A.IDLOWONGAN) AS JML_LOWONGAN, B.NAMA_BIDANG, B.IDBIDANG
							 FROM t_lowongan A
							 LEFT JOIN m_bidang B ON A.IDBIDANG = B.IDBIDANG
							 WHERE A.STATUS = 1
							 GROUP BY A.IDBIDANG");
	$resbidang	=	$db->query($sqlbidang);
	
	if($resbidang <> '' && $resbidang <> false){
		$listBidang		=	'';
		foreach($resbidang as $key){
			$idbidang	=	$enkripsi->encode($key['IDBIDANG']);
			$listBidang	.=	"	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
									<div class='tab-mapel'>
										<a href='".APP_URL."search_category?t=1&q=".$idbidang."'>".$key['NAMA_BIDANG']." <small><em>(".$key['JML_LOWONGAN'].")</em></small></a>
									</div>
								</div>";
		}
	} else {
		$listBidang	=	"<center><b>Tidak ada data</b></center>";
	}

	//JUMLAH LOWONGAN PER POSISI
	$sqlposisi	=	sprintf("SELECT COUNT(A.IDLOWONGAN) AS JML_LOWONGAN, B.NAMA_POSISI, B.IDPOSISI
							 FROM t_lowongan A
							 LEFT JOIN m_posisi B ON A.IDPOSISI = B.IDPOSISI
							 WHERE A.STATUS = 1
							 GROUP BY A.IDPOSISI");
	$resposisi	=	$db->query($sqlposisi);
	
	if($resposisi <> '' && $resposisi <> false){
		$listposisi		=	'';
		foreach($resposisi as $key){
			$idposisi	=	$enkripsi->encode($key['IDPOSISI']);
			$listposisi	.=	"	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
									<div class='tab-mapel'>
										<a href='".APP_URL."search_category?t=2&q=".$idposisi."'>".$key['NAMA_POSISI']." <small><em>(".$key['JML_LOWONGAN'].")</em></small></a>
									</div>
								</div>";
		}
	} else {
		$listposisi	=	"<center><b>Tidak ada data</b></center>";
	}
	
	//JUMLAH LOWONGAN PER LOKASI
	$sqlpropinsi=	sprintf("SELECT COUNT(A.IDLOWONGAN) AS JML_LOWONGAN, B.NAMA_PROPINSI, B.IDPROPINSI
							 FROM t_lowongan A
							 LEFT JOIN m_propinsi B ON A.IDPROPINSI = B.IDPROPINSI
							 WHERE A.STATUS = 1
							 GROUP BY A.IDPROPINSI");
	$respropinsi=	$db->query($sqlpropinsi);
	
	if($respropinsi <> '' && $respropinsi <> false){
		$listpropinsi		=	'';
		foreach($respropinsi as $key){
			$idpropinsi		=	$enkripsi->encode($key['IDPROPINSI']);
			$listpropinsi	.=	"	<div class='col-lg-4 col-md-4 col-sm-4 col-xs-12'>
									<div class='tab-mapel'>
										<a href='".APP_URL."search_category?t=3&q=".$idpropinsi."'>".$key['NAMA_PROPINSI']." <small><em>(".$key['JML_LOWONGAN'].")</em></small></a>
									</div>
								</div>";
		}
	} else {
		$listpropinsi	=	"<center><b>Tidak ada data</b></center>";
	}
	
	//HABIS - CEK SESSION
	header('Content-type: text/html; charset=utf-8');

?>

<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<title>TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!</title>
		<meta name="description" content="TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!">
		<meta name="author" content="inDesign Project">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile">
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
    	<?php if($pesan <> ""){ ?>
            <div class="alert alert-success alert-dismissible" role="alert" style="text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><small id="login_msg"><?=$pesan?></small></strong>
            </div>
        <? } ?>
        
    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>
        
		<div class="container">
			<div class="boxSquareWhite">
				<h3>Pencarian Lowongan Kerja</h3>
				<form class="form-inline form-searchjobs" method="POST" action="<?=APP_URL?>search">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-search"></i></div>
							<input type="text" class="form-control" placeholder="Bidang Pekerjaan" name="bidang" id="bidang" onkeyup="getDataBidangByInput(this.value, this.id)">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-map-marker"></i></div>
							<input type="text" class="form-control" placeholder="Lokasi pekerjaan" name="lokasi" id="lokasi" onkeyup="getDataPropinsiByInput(this.value, this.id)">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-briefcase"></i></div>
							<input type="text" class="form-control" placeholder="Posisi Pekerjaan" name="posisi" id="posisi" onkeyup="getDataPosisiByInput(this.value, this.id)">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-graduation-cap"></i></div>
							<input type="text" class="form-control" placeholder="Jenjang Pendidikan" name="pendidikan" id="pendidikan" onkeyup="getDataPendidikanByInput(this.value, this.id)">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-graduation-cap"></i></div>
						<select class="form-control" placeholder="Gaji min" name="gaji_min" id="gaji_min">
							<option value='0'>0</option>
							<option value='1000000'>1 Juta+</option>
							<option value='2000000'>2 Juta+</option>
							<option value='5000000'>5 Juta+</option>
							<option value='10000000'>10 Juta+</option>
							
						</select>-<select class="form-control" placeholder="Gaji max" name="gaji_max" id="gaji_max">
							<option value='0'>0</option>
							<option value='1000000'>1 Juta+</option>
							<option value='2000000'>2 Juta+</option>
							<option value='5000000'>5 Juta+</option>
							<option value='10000000' Selected>10 Juta+</option>
							
						</select> 
						</div>
					</div>
					<input type="submit" value="Cari" class="btn btn-default" />
				</form>
			</div>
		</div>
		<div class="coklatslogan">
			<div class="container">
				<h4 class=" text-left">DAFTAR PEKERJAAN</h4>
			</div>
		</div><br/><br/>
		<div class="container">
			<div class="row">
				<div class="col-sm-9">
					<div role="tabpanel">
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#bidang" aria-controls="bidang" role="tab" data-toggle="tab">BIDANG</a></li>
							<li role="presentation"><a href="#posisi" aria-controls="posisi" role="tab" data-toggle="tab">POSISI</a></li>
							<li role="presentation"><a href="#lokasi" aria-controls="lokasi" role="tab" data-toggle="tab">LOKASI</a></li>
						</ul>
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="bidang">
								<div class="row">
                                	<?=$listBidang?>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="posisi">
								<div class="row">
									<?=$listposisi?>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="lokasi">
								<div class="row">
									<?=$listpropinsi?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<h4>Lowongan Kerja Terbaru</h4>
					<div class="list-group">
						<?=$listlow?>
					</div>
				</div>
			</div>
		</div>
		<div class="infoslogan text-center">
			<div class="container">
				<div class="row">
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3><?=$totpenk?><br/>PENCARI KERJA</h3></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3><?=$totlowk?><br/>LOWONGAN KERJA</h3></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3><?=$totpemk?><br/>PEMBERI KERJA</h4></div>
				</div>
			</div>
		</div><br/><br/><br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
    	<?=$session->getTemplate('footer')?>

    </body>
</html>