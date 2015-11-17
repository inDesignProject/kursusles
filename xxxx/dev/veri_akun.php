<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	session_start();
	if($session->cekSession() == 1 || !isset($_GET['q']) || $_GET['q'] == ''){
		
		header("Location: login?authResult=".$enkripsi->encode('3')."&t=2");
		die();
		
	} else if($session->cekSession() == 2){
		
		$idpeng	=	$enkripsi->decode($_GET['q']);
		$sql	=	sprintf("SELECT KTP, VERIFIED_STATUS FROM m_pengajar WHERE IDPENGAJAR = %s LIMIT 0,1", $idpeng);
		$result	=	$db->query($sql);
		$result	=	$result[0];
		$ktp	=	$result['KTP'];
		$status	=	$result['VERIFIED_STATUS'];
		
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
    	<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile" />
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssveriakun');?>.cssfile">
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
    <body>
    
        <?=$session->getTemplate('header')?>
        
  		<div class="container">
        	<h3 class="text-left text_kursusles page-header">VERIFIKASI AKUN</h3>
				<div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="boxSquare">
							<div class="panel panel-default">
                                <div class="panel-heading">
                                    <b> Kartu Identitas </b>
                                </div>
                                <div class="panel-body">
                                    <div class="alert alert-success alert-dismissible" role="alert" style="text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;">
                                    	<strong>
                                        	<small id="message_response">
												<?php
                                                    if($status == 0){
                                                ?>
                                                		Kami perlu mengetahui identitas asli anda untuk memverifikasi akun yang anda miliki. Silakan upload hasil scan kartu identitas ( KTP/SIM ) dengan klik tombol <strong>upload foto</strong>
                                                <?php
													} else {
														echo "Anda sudah mengupload foto sebelumnya, tunggu hingga admin memverifikasi kiriman sebelumnya";
													}
												?>
                                            </small>
                                        </strong>
                                   	</div>
                                    <center>
                                        <img src="<?=APP_IMG_URL."generate_pic.php?type=ktp&q=".$enkripsi->encode($ktp)?>" />
                                    </center><br/>
                                    <div id="button_container">
                                        <a onclick="history.back();" class="btn btn-kursusles btn-sm">
                                        	< Kembali
                                        </a>
                                        <?php
											if($status == 0){
										?>
                                        <a href="#" id="button_uploadktp" class="btn btn-kursusles btn-sm" onclick="openWindowUpload()">
                                            Upload Identitas
                                        </a>
                                        <?php
											}
										?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div><br/><br/>
        <div id="dialog-confirm">
          <p id="text_dialog"></p>
        </div>
        
        <script src="php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
		<script>
			<?php
				if($status == 0){
			?>
			function openWindowUpload(){
				$("#dialog-confirm").dialog({
					closeOnEscape: false,
					resizable: true,
					modal: true,
					minWidth: 500,
					title: "Upload File",
					position: {
						my: 'top', 
						at: 'top'
					},
					open: function() {
					  $(this).html("<object id='object_uploader' type='type/html' data='<?=APP_URL?>php/page/upload_foto_ktp.php' width='100%' height='400px'></object>");
					},
					close: function() {
						$(this).dialog( "close" );
					},
					buttons: {
						"Simpan": function() {
							
							var data;
								objUploader	=	document.getElementById("object_uploader");
								contentUpl	=	objUploader.contentDocument;
								resultUpload=	contentUpl.getElementById("resultupload").value;
								msgElem		=	contentUpl.getElementById("editor_status");
								msgTxt		=	contentUpl.getElementById("editor_message");
							
							if(resultUpload != 1){
								blink(msgElem, 4, 150);
							} else {
								$.post("<?=APP_URL?>php/page/upload_foto_ktp.php?func=<?=$enkripsi->encode('setFoto')?>")
								.done(function( data ) {
									
									data			=	JSON.parse(data);
									if(data['respon_code'] != 1){
										msgElem.className	=	"error";
										msgTxt.innerHTML	=	data['respon_message'];
										blink(msgElem, 4, 150);
										return false;
									} else {
										$("#dialog-confirm").html("");
										window.location.href	=	'<?=APP_URL?>veri_akun?q=<?=$_GET['q']?>';
									}
								});
							}
									
							$(this).dialog("close");
			
						},
						"Batal": function() {
							$( this ).dialog( "close" );
						}
					}
				});
			}
			<?php
				}
			?>
			function blink(elem, times, speed) {
				if (times > 0 || times < 0) {
					if ($(elem).hasClass("blink")) {
						$(elem).removeClass("blink");
					} else {
						$(elem).addClass("blink");
					}
				}
			
				clearTimeout(function () {
					blink(elem, times, speed);
				});
			
				if (times > 0 || times < 0) {
					setTimeout(function () {
						blink(elem, times, speed);
					}, speed);
					times -= .5;
				}
			}
		</script>
        
    	<?=$session->getTemplate('footer')?>

    </body>
</html>
<?php		
	}
?>