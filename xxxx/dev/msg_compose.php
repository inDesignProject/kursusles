<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();

	if($session->cekSession() <> 2){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."&rdr=msg_compose;t=".$_GET['t'].";;r=".$_GET['r']."'</script>";
		 die();
	}
	
	if(!isset($_GET['r']) && $_GET['t'] <> ''){
		$sqlUnion	=	"";
	} else {
		
		if($_GET['r'] == 'pengajar_profil'){
			$sqlUnion	=	sprintf("SELECT NAMA, FOTO, '1' AS TYPE, IDPENGAJAR AS IDUSER, NOW() AS TGL_PESAN
									 FROM m_pengajar
									 WHERE IDPENGAJAR = %s
									 
									 UNION ALL "
									, $enkripsi->decode($_GET['t'])
									);
		} else {
			$sqlUnion	=	'';
		}
		
	}
	
	$typeUser	=	$_SESSION['KursusLes']['TYPEUSER'] == '1' ? '2' : '1';
	$sqlList	=	sprintf("SELECT * FROM (%s
											
											SELECT IFNULL(IF(A.TYPEPENGIRIM = 1, B.NAMA, C.NAMA), 'ADMIN') AS NAMA, 
												   IF(A.TYPEPENGIRIM = 1, B.FOTO, C.FOTO) AS FOTO,
												   A.TYPEPENGIRIM AS TYPE, 
												   IFNULL(IF(A.TYPEPENGIRIM = 1, B.IDPENGAJAR, C.IDMURID), 0) AS IDUSER, 
												   A.TGL_PESAN
											FROM t_pesan_pribadi A
											LEFT JOIN m_pengajar B ON A.IDUSERPENGIRIM = B.IDPENGAJAR
											LEFT JOIN m_murid C ON A.IDUSERPENGIRIM = C.IDMURID
											WHERE A.IDUSERTUJUAN = '%s' AND A.TYPETUJUAN = '%s'
											
											UNION ALL
											
											SELECT IFNULL(IF(A.TYPETUJUAN = 1, B.NAMA, C.NAMA), 'ADMIN') AS NAMA, 
												   IF(A.TYPETUJUAN = 1, B.FOTO, C.FOTO) AS FOTO,
												   A.TYPETUJUAN AS TYPE, 
												   IFNULL(IF(A.TYPETUJUAN = 1, B.IDPENGAJAR, C.IDMURID), 0) AS IDUSER,
												   A.TGL_PESAN
											FROM t_pesan_pribadi A
											LEFT JOIN m_pengajar B ON A.IDUSERTUJUAN = B.IDPENGAJAR
											LEFT JOIN m_murid C ON A.IDUSERTUJUAN = C.IDMURID
											WHERE A.IDUSERPENGIRIM = '%s' AND A.TYPEPENGIRIM = '%s'
							 ) AS A
							 GROUP BY IDUSER, TYPE
							 ORDER BY TGL_PESAN DESC"
							, $sqlUnion
							, $_SESSION['KursusLes']['IDUSER']
							, $typeUser
							, $_SESSION['KursusLes']['IDUSER']
							, $typeUser
							);
	$resultList	=	$db->query($sqlList);
	$idUser		=	$resultList[0]['IDUSER'] == 0 ? "admin" : $resultList[0]['IDUSER'];
	$tyUser		=	$resultList[0]['TYPE'] == 0 ? "admin" : $resultList[0]['TYPE'];
	
	//FUNGSI DIGUNAKAN DETAIL PESAN
	if( $enkripsi->decode($_GET['func']) == "detailPesan" && isset($_GET['func'])){
		
		$idUserLawan=	$enkripsi->decode($_POST['id']) == "admin" || $enkripsi->decode($_POST['id']) == '' ? "0" : $enkripsi->decode($_POST['id']);
		$tyUserLawan=	$enkripsi->decode($_POST['param']) == "admin" || $enkripsi->decode($_POST['id']) == '' ? "0" : $enkripsi->decode($_POST['param']);
		$sqldetail	=	sprintf("SELECT IFNULL(IF(A.TYPEPENGIRIM = 1, B.NAMA, C.NAMA), 'ADMIN') AS NAMA, 
									    IF(A.TYPEPENGIRIM = 1, B.FOTO, C.FOTO) AS FOTO,
									    A.TGL_PESAN, A.SUBYEK, A.PESAN, A.TYPEPENGIRIM
								 FROM t_pesan_pribadi A
								 LEFT JOIN m_pengajar B ON A.IDUSERPENGIRIM = B.IDPENGAJAR
								 LEFT JOIN m_murid C ON A.IDUSERPENGIRIM = C.IDMURID
								 WHERE (A.IDUSERTUJUAN = '%s' AND A.TYPETUJUAN = '%s' AND 
								 		A.IDUSERPENGIRIM = '%s' AND A.TYPEPENGIRIM = '%s')
								 	OR (A.IDUSERTUJUAN = '%s' AND A.TYPETUJUAN = '%s' AND 
										A.IDUSERPENGIRIM = '%s' AND A.TYPEPENGIRIM = '%s')
								 ORDER BY A.TGL_PESAN DESC"
								, $_SESSION['KursusLes']['IDUSER']
								, $typeUser
								, $idUserLawan
								, $tyUserLawan
								, $idUserLawan
								, $tyUserLawan
								, $_SESSION['KursusLes']['IDUSER']
								, $typeUser
								);
		$resultDetail=	$db->query($sqldetail);
		
		if($resultDetail <> '' && $resultDetail <> false){
			
			$text	=	'';
			foreach($resultDetail as $key){
				
				$foto	=	"<img src='".APP_IMG_URL."generate_pic.php?type=pr&q=".$enkripsi->encode($key['FOTO'])."&w=40&h=40' class='img-circle' style='margin: 4px;'>";	
				$class	=	$key['TYPEPENGIRIM'] == '1' ? "pull-right text-right" : "";
				$tanggal=	date('Y-m-d') == date('Y-m-d', strtotime($key['TGL_PESAN'])) ? "Hari ini" : date('Y-m-d', strtotime($key['TGL_PESAN']));

				if($key['TYPEPENGIRIM'] == '1'){
					$headSender	=	$key['NAMA']." ".$foto;
				} else {
					$headSender	=	$foto." ".$key['NAMA'];
				}

				$text	.=	"	<div class='row' style='margin: 0 5px 8px 5px;'>
									<div class='listPesan ".$class."' style='width: 55%; padding: 0 !important'>
										<div class='media'>
											<div class='header-sender'>
												".$headSender."<br/>
											</div>
											<div style='padding:6px'>
												<h5 class='media-heading'>
													<strong>
														<i class='fa fa-envelope'></i> ".$key['SUBYEK']."
													</strong>
												</h5>
												<p>
													<small>".$key['PESAN']."</small>
												</p>
												<small>
													<i class='fa fa-calendar'></i> ".$tanggal." &nbsp;&nbsp;
													<i class='fa fa-clock-o'></i> ".date('H:i:s', strtotime($key['TGL_PESAN']))."
												</small>
											</div>
										</div>
									</div>
								</div>";
			}
			echo $text;
			
		} else {
		
			echo "<center><b>Tidak ada pesan yang ditampilkan</b></center>";
		
		}
		die();
		
	}
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
	//FUNGSI DIGUNAKAN KIRIM PESAN
	if( $enkripsi->decode($_GET['func']) == "kirimPesan" && isset($_GET['func'])){
		$idpenerima	=	$enkripsi->decode($_POST['penerima']);
		$type		=	$enkripsi->decode($_POST['type']);
		
		if($idpenerima == 'admin' || $type == 'admin' || $idpenerima == '' || $type == ''){
			echo -1;
			die();
		}
		
		$sqlIns		=	sprintf("INSERT INTO t_pesan_pribadi 
								 (IDUSERTUJUAN,IDUSERPENGIRIM,SUBYEK,PESAN,TGL_PESAN,TYPETUJUAN,TYPEPENGIRIM)
								 VALUES
								 ('%s','%s','%s','%s',NOW(),'%s','%s')"
								, $idpenerima
								, $_SESSION['KursusLes']['IDUSER']
								, $_POST['subyek']
								, $_POST['pesan']
								, $type
								, $typeUser
								);
		$affected	=	$db->execSQL($sqlIns, 0);
		
		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo "1";
		} else {
			echo "0";
		}
		
		die();
	}
	//HABIS -- FUNGSI DIGUNAKAN KIRIM PESAN
	
	header('Content-type: text/html; charset=utf-8');
	$showhtml	=	($_GET['r'] == 'imurd' || $_GET['r'] == 'ipgjr') && isset($_GET['r']) ? false : true;
	//echo $showhtml; die();

if($showhtml == "true"){
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
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>
    
<?php
        echo $session->getTemplate('header', $show_login);
        
}
?>
		<style>
            .tab_list{margin-bottom: 4px !important; width: 100%;}
            .boxSquare{padding:1% !important}
            .text-right{text-align: right}
            .header-sender{
                width: 100%;
                background: none repeat scroll 0% 0% rgb(204, 204, 204);
                height: 48px;
                margin-bottom: 4px;
                color: rgb(255, 255, 255);
                font-weight: bold;
            }
        </style>

<?php
if($showhtml == true){
?>
		<div class="container">
        	<h3 class="text-left text_kursusles page-header">KIRIM PESAN</h3>
<?php
}
?>
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Pesan Sebelumnya</b></div>
                            <div class="panel-body" id="detailPesan" style="max-height: 450px; overflow-y: scroll; overflow-x: hidden;">
								
                            </div>
                        </div>
                    </div>
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Tulis</b></div>
                            <div class="panel-body">
                                <form id="composeForm" method="POST" action="#">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <input type="text" id="subyek" name="subyek" class="form-control" placeholder="Masukkan subyek" value="" />
                                            </div>
                                            <div class="form-group">
                                                <textarea id="pesan" name="pesan" maxlength="2000" class="form-control" placeholder="Isi Pesan" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" name="idpenerima" id="idpenerima" value="<?=$enkripsi->encode($idUser)?>" />
                                                <input type="hidden" name="typepenerima" id="typepenerima" value="<?=$enkripsi->encode($tyUser)?>" />
                                                <input type="button" name="kirim" id="kirim" value="Kirim" class="btn btn-sm btn-custom2" onClick="sendMsg()" />
                                            </div>
                                        </div>
                                	</div>
                            	</form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Orang yang terhubung</b></div>
                            <div class="panel-body">
							<?php
							if($resultList == '' || $resultList == false){
								echo "<center><b>- Tidak ada data -</b></center>";
							} else {
							?>
                            <div role="tabpanel">
                                <ul class="nav nav-tabs" role="tablist" style="max-height:450px; overflow-y: scroll; overflow-x: hidden;">
                            <?php
								$i		=	0;
								foreach($resultList as $key){
									$active		=	$i == 0 ? "active" : "";
									$userid		=	$key['IDUSER'] == 0 ? $enkripsi->encode("admin") : $enkripsi->encode($key['IDUSER']);
									$usertype	=	$key['TYPE'] == 0 ? $enkripsi->encode("admin") : $enkripsi->encode($key['TYPE']);
							?>
                                    <li role="presentation" class="tab_list <?=$active?>">
                                        <a href="#" id="<?=$userid?>" onclick="getDetail(this.id,'<?=$usertype?>')" aria-controls="<?=$enkripsi->encode($usertype."|".$key['TYPE'])?>" role="tab" data-toggle="tab">
                                            <b> <?=$key['NAMA']?> </b>
                                            <img src="<?=APP_IMG_URL?>generate_pic.php?type=pr&q=<?=$enkripsi->encode($key['FOTO'])?>&w=40&h=40" class="img-circle pull-right" style="margin-top: -10px;">
                                            <br>
                                        </a>
                                     </li>	
						  	<?php
									$i++;
								}
							?>
                            	</ul>
                            </div>
							<?php
							}
						  	?>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
<?php
if($showhtml == true){
?>
        </div><br/>
<?php
}
?>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
        <script>
		
			function generateMsg(msg){
				return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
						"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
						"<strong><small id='message_response'>"+msg+"</small></strong>"+
					"</div>";
			}
			
			function sendMsg(){
				var	subyek	=	$('#subyek').val();
					pesan	=	$('#pesan').val();
					penerima=	$('#idpenerima').val();
					type	=	$('#typepenerima').val();
					
				$('#message_response_container').slideUp('fast').html("");
				if(subyek == ''){
					$('#message_response_container').slideDown('fast').html(generateMsg('Masukkan subyek / judul dahulu'));
				} else if(pesan == ''){
					$('#message_response_container').slideDown('fast').html(generateMsg('Pesan tidak boleh kosong'));
				} else {
					
					$.post( "msg_compose?func=<?=$enkripsi->encode('kirimPesan')?>", {subyek: subyek, pesan: pesan, penerima: penerima, type: type})
					.done(function( data ) {
						
						if(data == "1"){
							$('#message_response_container').slideDown('fast').html(generateMsg('Pesan terkirim'));
							$('#subyek').val('');
							$('#pesan').val('');
							
							$('#'+penerima).click();
							
						} else if(data == "-1"){
							$('#message_response_container').slideDown('fast').html(generateMsg('Pengiriman pesan ditolak'));
						} else {
							$('#message_response_container').slideDown('fast').html(generateMsg('Gagal mengirim pesan'));
						}
						
					});

				}
			}
			
			function getDetail(id, param){
				
				$('.tab_list').removeClass('active');
				$('#'+id).closest('li').addClass('active');
				$('#detailPesan').html("<center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
				
				$.post( "msg_compose?func=<?=$enkripsi->encode('detailPesan')?>", {id: id, param: param})
				.done(function( data ) {
					
					$('#idpenerima').val(id);
					$('#typepenerima').val(param);
					$('#detailPesan').html(data);
					
				});
				
			}
			
			$(document).ready(function(){
				getDetail('<?=$enkripsi->encode($resultList[0]['IDUSER'])?>', '<?=$enkripsi->encode($resultList[0]['TYPE'])?>');
			});
		</script>

<?php
if($showhtml == true){
		echo $session->getTemplate('footer')
?>
    </body>
</html>
<?php
}
?>