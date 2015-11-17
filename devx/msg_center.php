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
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
	//DAFTAR PESAN USER
	$type			=	$_GET['r'] == "pengajar_profil" ? "1" : "2";
	$iduser			=	$_SESSION['KursusLes']['IDUSER'];

	$sqlListP		=	sprintf("SELECT IDPESAN,NAMA_PENGIRIM,EMAIL,TGL_PESAN,ISREAD
								 FROM t_pesan
								 WHERE IDUSER = %s AND TYPE = %s"
								, $iduser
								, $type
								);
	$resultListP	=	$db->query($sqlListP);
	//DAFTAR PESAN USER
	
	//FUNGSI DIGUNAKAN MELIHAT DETAIL PESAN
	if( $enkripsi->decode($_GET['func']) == "detailPesan" && isset($_GET['func'])){
		$idpesan		=	$enkripsi->decode($_POST['idpesan']);
		$sqlPesan		=	sprintf("SELECT NAMA_PENGIRIM,EMAIL,PESAN,TGL_PESAN
									 FROM t_pesan
									 WHERE IDPESAN = %s AND TYPE = %s
									 LIMIT 0,1"
									, $idpesan
									, $_POST['type']
									);
		$resultPesan	=	$db->query($sqlPesan);
		
		if($resultPesan <> false && $resultPesan <> ''){
			$resultPesan=	$resultPesan[0];
			
			$detailPesan=	"	<div class='media'>
									<div class='media-body'>
										<h5 class='media-heading'><strong>".$resultPesan['NAMA_PENGIRIM']." <small>- <i class='fa fa-envelope'></i> ".$resultPesan['EMAIL']."</small></strong></h5>
										<p>
											<small>".$resultPesan['PESAN']."</small>
										</p>
										<small><i class='fa fa-calendar'></i> ".date('d-m-Y',strtotime($resultPesan['TGL_PESAN']))." &nbsp;&nbsp;<i class='fa fa-clock-o'></i> ".date('H:i:s',strtotime($resultPesan['TGL_PESAN']))."</small>
									</div>
								</div>";
								
			$sqlupd		=	sprintf("UPDATE t_pesan SET ISREAD = 1 WHERE IDPESAN = %s", $idpesan);
			$db->execSQL($sqlupd, 0);
			
		} else {
			$detailPesan=	"<center><b>Tidak ada detail yang ditampilkan</b></center>";
		}
		
		echo $detailPesan;
		die();
		
	}
	
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
        <div id="message_response_container" style="position: fixed;top: 0px;width: 100%;border-radius: 0px !important;z-index:999; display:none">
        </div>
		<style>
			.tab_list{margin-bottom: 4px !important; width: 100%;}
			.tab-panel{width: 50%; text-align:center}
		</style>
    	<?=$session->getTemplate('header', $show_login)?>
        
        <div class="container">
        	<h3 class="text-left text_kursusles page-header">
            	PUSAT PESAN
            </h3>
            <div class="row">
				<?php
                    if($_GET['r'] == '' || !isset($_GET['r'])){
                        echo "<div class='boxSquareWhite'><center>Anda tidak berhak untuk mengakses halaman ini. Silakan login dahulu.</center></div>";
                    } else {
                ?>      <div role="tabpanel">
							<ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active tab-panel">
                                	<a href="#pesan-publik" id="apesanpublik" aria-controls="pesan-publik" role="tab" data-toggle="tab">Pesan Publik</a>
                                </li>
                                <li role="presentation" class="tab-panel">
                                	<a href="#pesan-pribadi" id="apesanpribadi" aria-controls="pesan-pribadi" role="tab" data-toggle="tab">Pesan Pribadi</a>
                                </li>
                            </ul>
                            <div class="tab-content" style="background: #eee; min-height: 500px;">
                                <div role="tabpanel" class="tab-pane active" id="pesan-publik">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                            <div class="boxSquare">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading"><b>Daftar Pesan</b></div>
                                                    <div class="panel-body">
                                                        <?php
                                                            if($resultListP <> false && $resultListP <> ''){
                                                        ?>
                                                                <div role="tabpanel">
                                                                    <ul class="nav nav-tabs" role="tablist" style="max-height:450px; overflow-y: scroll; overflow-x: hidden;">
                                                                        <?php
                                                                            $i		=	0;
                                                                            $list	=	'';
                                                                            foreach($resultListP as $key){
                                                                                $active	=	$i == 0 ? "active" : "";
                                                                                $new	=	$key['ISREAD'] == '0' ? "<small class='text-danger pull-right' id='new".$enkripsi->encode($key['IDPESAN'])."'>* Baru</small>" : "";
                                                                                $list	.=	"<li role='presentation' class='".$active." tab_list'>
                                                                                                <a href='#' id='".$enkripsi->encode($key['IDPESAN'])."' onclick='getDetail(this.id)' aria-controls='".$enkripsi->encode($key['IDPESAN'])."' role='tab' data-toggle='tab'>
                                                                                                    <b>".$key['NAMA_PENGIRIM']."</b>".$new."<br/>
                                                                                                    <i class='fa fa-envelope'></i> ".$key['EMAIL']."<br/>
                                                                                                    <i class='fa fa-calendar'></i> ".$key['TGL_PESAN']."<br/>
                                                                                                </a>
                                                                                             </li><br/>";
                                                                                $i++;
                                                                            }
                                                                            echo $list;
                                                                        ?>
                                                                    </ul>
                                                                </div>
                                                        <?php
                                                            } else {
                                                                echo "<center><b>Tidak ada pesan</b></center>";
                                                            }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                            <div class="boxSquare">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading"><b>Detail Pesan</b></div>
                                                    <div class="panel-body" id="detailPesanPublik">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                	</div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="pesan-pribadi">
                                </div>
                            </div>
                        </div> <br/>
                <?php
					}
				?>
            </div>
        </div>
		<br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
		<script>
			function getDetail(id){
				$('.tab_list').removeClass('active');
				$('#'+id).closest('li').addClass('active');
				$.post( "msg_center?func=<?=$enkripsi->encode('detailPesan')?>", {idpesan: id, type: <?=$type?>})
				.done(function( data ) {
					
					$('#detailPesanPublik').html(data);
					$('#new'+id).remove();
					
				});
		
			}
			
			$(document).ready(function(){
				getDetail('<?=$enkripsi->encode($resultListP[0]['IDPESAN'])?>');
				$("#pesan-pribadi").load("<?=APP_URL?>msg_compose?q=<?=$enkripsi->encode($iduser)?>&r=ipgjr");
			});
        </script>
        
    	<?=$session->getTemplate('footer')?>

    </body>
</html>