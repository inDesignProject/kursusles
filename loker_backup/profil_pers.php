<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login	=	$session->cekSession() == 2 ? "false" : "true";
	
	if($_GET['q'] <> "" && isset($_GET['q'])){
		$idpers		=	$enkripsi->decode($_GET['q']);
	} else {
		if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 1){
			echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
			die();
		} else {
			$idpers	=	$_SESSION['KursusLesLoker']['IDUSER'];
		}
	}
	
	//DATA UTAMA
	$sql		=	sprintf("SELECT A.NAMA_PERUSAHAAN, A.ALAMAT_KANTOR, B.NAMA_PROPINSI, C.NAMA_KOTA,
									A.KODEPOS, A.TELPON, A.EMAIL, A.WEBSITE, D.NAMA_USAHA, A.IDPROPINSI,
									A.IDKOTA, A.JENIS_USAHA, A.TENTANG, A.TGL_BERDIRI
							 FROM m_perusahaan A
							 LEFT JOIN m_propinsi B ON A.IDPROPINSI = B.IDPROPINSI
							 LEFT JOIN m_kota C ON A.IDKOTA = C.IDKOTA
							 LEFT JOIN m_jenis_usaha D ON A.JENIS_USAHA = D.IDUSAHA
							 WHERE A.IDPERUSAHAAN = %s
							 LIMIT 0,1"
							, $idpers
							);
	$result		=	$db->query($sql);
	$result		=	$result[0];
	$tentang	=	$result['TENTANG'] == "" ? "Tidak ada yang ditampilkan" : $result['TENTANG'];
	$website	=	substr($result['WEBSITE'],0,4) == "www." ? "" : "";
	
	header("Content-type: text/html; charset=utf-8");

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
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssbootstrap');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssfontawesome');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
        <!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

    	<?=$session->getTemplate('header', $show_login)?>
		
		<div class="container" style="font-family: Verdana,Arial,Helvetica,sans-serif;">
			<h3 class="text-left text_kursusles page-header">PROFIL</h3>
           
            <div class="boxSquareWhite">
            	<div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <span class="tutor_name"><?=$result['NAMA_PERUSAHAAN']?></span>
                        <hr>
                        <div class="info">
                            <div class="infolist" id="toplist">
                                <i class="fa fa-home"></i> &nbsp; <?=($show_login == 'false' ? $result['ALAMAT_KANTOR'] : '')?> 
                                <?=$result['NAMA_PROPINSI']." ".$result['NAMA_KOTA']?></b><br/>
                                <?=($show_login == 'false' ? "<i class='fa fa-paper-plane-o'></i> &nbsp; ". $result['KODEPOS'] ."<br/>" : '')?>
                                <?=($show_login == 'false' ? "<i class='fa fa-phone'></i> &nbsp; ". $result['TELPON'] ."<br/>" : '')?>
                                <?=($show_login == 'false' ? "<i class='fa fa-envelope-o'></i> &nbsp; ". $result['EMAIL'] ."<br/>" : '')?>
                                <i class="fa fa-globe"></i> &nbsp; <a href="<?=$website?>" ><?=$result['WEBSITE']?></a><br/>
                                <i class="fa fa-share"></i> &nbsp; <?=$result['NAMA_USAHA']?></b><br/>
                                <i class="fa fa-calendar"></i> &nbsp; <?=$result['TGL_BERDIRI']?></b><br/>
                            </div>
                        </div>
                    </div>
                </div>
            </div><hr>

            <div class="boxSquareWhite">
                <h4><i class="fa fa-info-circle"></i> Tentang</h4>
                <div id="ptentang">
                    <p id="txttentang">
                        <?=$tentang?><br/><br/>
                    </p>
                </div>
            </div><hr/>
            
        </div><br/><br/>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<?=$session->getTemplate('footer')?>

        <div id="dialog-confirm">
          <p id="text_dialog"></p>
        </div>		

    </body>
</html>