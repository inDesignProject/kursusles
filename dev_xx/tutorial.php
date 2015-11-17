<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	$pathinfo = isset($_SERVER['PATH_INFO'])
		? $_SERVER['PATH_INFO']
		: $_SERVER['REDIRECT_URL'];
	
	$params = preg_split('|/|', $pathinfo, -1, PREG_SPLIT_NO_EMPTY);
	
	if(end($params) <> 'tutorial'){
		$_GET['q'] = end($params);
	}
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login	=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
	//SELECT DATA KATEGORI TUTOR
	$sqlkat		=	sprintf("SELECT IDKATEGORI,NAMA_KATEGORI FROM m_tutorial_kategori WHERE STATUS = 1 ORDER BY NAMA_KATEGORI");
	$resultkat	=	$db->query($sqlkat);
	$datakat	=	'';
	
	if($resultkat <> '' && $resultkat <> false){
		$firstKat		=	$enkripsi->encode($resultkat[0]['IDKATEGORI']);
		foreach($resultkat as $key){
			$idKat		=	$enkripsi->encode($key['IDKATEGORI']);
			$datakat	.=	"<a href='#' id='".$idKat."' onClick='getTutor(this.id, 1)' class='list-group-item'>".$key['NAMA_KATEGORI']."</a>";
		}
	} else {
		
		$datakat		=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
		
	}
	
	//JIKA MELIHAT DATA DETAIL
	if(isset($_GET['q']) && $_GET['q'] <> ''){
		$idkatDetail	=	$enkripsi->decode($_GET['q']);
		$sqlDetail		=	sprintf("SELECT A.IDTUTORIAL, A.JUDUL, A.ISI, A.TGL_POSTING, B.USERNAME, A.IDKATEGORI
									 FROM t_tutorial A
									 LEFT JOIN admin_user B ON A.IDUSERPOSTING = B.IDUSER
									 WHERE A.IDTUTORIAL = %s AND A.STATUS = 1
									 LIMIT 0,1"
									, $idkatDetail
									);
		$resultDetail	=	$db->query($sqlDetail);
		
		if($resultDetail <> false && $resultDetail <> ''){
			$resultDetail	=	$resultDetail[0];
			
			$sqlTerkait		=	sprintf("SELECT IDTUTORIAL, JUDUL
										 FROM t_tutorial
										 WHERE IDKATEGORI = %s AND STATUS = 1 AND IDTUTORIAL <> %s"
										, $resultDetail['IDKATEGORI']
										, $idkatDetail
										);
			$resultTerkait	=	$db->query($sqlTerkait);
			$dataTerkait	=	"";
			
			if($resultTerkait <> false && $resultTerkait <> ''){
				foreach($resultTerkait as $keyT){
					$dataTerkait	.=	"<a href='".APP_URL."tutorial.php/".$enkripsi->encode($keyT['IDTUTORIAL'])."' class='list-group-item'>".$keyT['JUDUL']."</a>";
				}
			} else {
				$dataTerkait=	"<center><b>Tidak ada data yang ditampilkan</b></center>";
			}
			
			$detailTutor	=	"	<div class='boxSquareWhite'>
										<h3><span class='tutor_name'>".$resultDetail['JUDUL']."</span></h3>
										<small class='tutorial_detail'><i class='fa fa-calendar'></i> ".date('d-m-Y', strtotime($resultDetail['TGL_POSTING']))." | <i class='fa fa-user'></i> ".$resultDetail['USERNAME']."</small><hr>
										<p>
											".$resultDetail['ISI']."
										</p><hr>
										<ul class='list-inline'>
											<li>Bagikan tutorial ini</li>
											<li><a href='http://www.facebook.com/sharer.php?u=".APP_URL."tutorial.php/".$_GET['q']."' onclick='window.open(this.href, 'mywin','left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;' class='btn btn-primary btn-sm'><i class='fa fa-facebook'></i> Facebook</a></li>
											<li><a href='http://twitter.com/share?url=".APP_URL."tutorial.php/".$_GET['q']."&text=".$resultDetail['JUDUL']." @KursusLes' onclick='window.open(this.href, 'mywin','left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;' class='btn btn-info btn-sm'><i class='fa fa-twitter'></i> Twitter</a></li>
											<li><a href='https://plus.google.com/share?url=".APP_URL."tutorial.php/".$_GET['q']."' onclick='window.open(this.href, 'mywin','left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;' class='btn btn-danger btn-sm'><i class='fa fa-google-plus'></i> Google+</a></li>
										</ul>
									</div>";
		} else {
			$detailTutor	=	"<div class='boxSquareWhite'><center><b>Tidak ada data yang ditampilkan</b></center></div>";
		}
		
	}

	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		$idkategori	=	$enkripsi->decode($_POST['idkategori']);
		$dataperpage=	10;
		$startLimit	=	($_POST['page'] - 1) * $dataperpage;
		
		//SELECT DATA
		$sql		=	sprintf("SELECT A.IDTUTORIAL, A.JUDUL, LEFT(A.ISI,300) AS ISI, A.TGL_POSTING, B.USERNAME
								 FROM t_tutorial A
								 LEFT JOIN admin_user B ON A.IDUSERPOSTING = B.IDUSER
								 WHERE A.IDKATEGORI = %s AND A.STATUS = 1
								 ORDER BY A.TGL_POSTING DESC"
								, $idkategori
								);
		$sqlCount	=	sprintf("SELECT COUNT(IDTUTORIAL) AS TOTDATA FROM (%s) AS A", $sql);
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
		$totData	=	0;
		$data		=	'';
		$pagination	=	'';

		if($result <> '' && $result <> false){
			
			if($totpage == 1){
				$pagination	=	"<li class='active'><a href='#'>1</a></li>";
			} else {
				
				if($_POST['page'] <> 1 && $totpage > 1){
					$prevPage	=	($_POST['page'] *1) - 1;
					$pagination	.=	"	<li>
											<a href='#' aria-label='Previous' onClick='getTutor(\"".$_POST['idkategori']."\", ".$prevPage.")'>
												<span aria-hidden='true'>&laquo;</span>
											</a>
										</li>";
				}
				
				for($i=1; $i<=$totpage; $i++){
					$active		=	$i == $_POST['page'] ? "active" : "";
					$pagination	.=	"<li class='".$active."'><a href='#' onClick='getTutor(\"".$_POST['idkategori']."\", ".$i.")'>".$i."</a></li>";
				}

				if($_POST['page'] <> $totpage){
					$nextPage	=	($_POST['page'] *1) + 1;
					$pagination	.=	"	<li>
											<a href='#' aria-label='Next' onClick='getTutor(\"".$_POST['idkategori']."\", ".$nextPage.")'>
												<span aria-hidden='true'>&raquo;</span>
											</a>
										</li>";
				}
				
			}
			
			foreach($result as $key){
				$idtutor=	$enkripsi->encode($key['IDTUTORIAL']);
				$data	.=	"	<div class='boxSquareWhite'>
									<a href='#' onClick='gotoDetail(\"".$idtutor."\")'><span class='tutor_name'>".$key['JUDUL']."</span></a><br/>
									<small class='tutorial_detail'><i class='fa fa-calendar'></i> ".date('d-m-Y', strtotime($key['TGL_POSTING']))." | <i class='fa fa-user'></i> ".$key['USERNAME']."</small>
									<p>
										".$key['ISI']."
									</p>
									<a href='#' class='label label-danger' onClick='gotoDetail(\"".$idtutor."\")'>Baca selengkapnya...</a>
								</div><br/>";
			}
			
		} else {
			$data		=	"<div class='boxSquareWhite'><center><b>Tidak ada data yang ditampilkan</b></center></div>";
			$pagination	=	"<li class='active'><a href='#'>1</a></li>";
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data, "pagination"=>$pagination));
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
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>
        
		<div class="container">
			<h3 class="text-left text_kursusles page-header">TUTORIAL</h3>
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
					<div class="boxSquare">
                    	<div id="data-con">
                        	<?php
								if(isset($_GET['q']) && $_GET['q'] <> ''){
									echo $detailTutor;
								}
							?>
                        </div>
						<nav>
							<ul class="pagination" id="pagination-con">
							</ul>
						</nav>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
					<div class="boxSquare">
						<div class="segmenPaket">
							<div class="panel panel-default">
								<div class="panel-heading">KATEGORI</div>
								<div class="panel-body list-group">
									<?=$datakat?>
								</div>
							</div>
						</div>
						<?php
							if(isset($_GET['q']) && $_GET['q'] <> ''){
						?>
                        <div class="segmenKeahlian">
							<div class="panel panel-default">
								<div class="panel-heading">TUTORIAL TERKAIT</div>
								<div class="panel-body list-group">
									<?=$dataTerkait?>
								</div>
							</div>
						</div>
						<?php
							}
						?>
					</div>
				</div>
			</div>
		</div><br/><br/><br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
    	<script>
			function getTutor(id, page){
				$('#data-con').html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
				$.post( "<?=APP_URL?>tutorial?func=<?=$enkripsi->encode('filterData')?>", {idkategori: id, page:page})
				.done(function( data ) {
					
					data	=	JSON.parse(data);
					$('#data-con').html(data['respon']);
					$('#pagination-con').html(data['pagination']);
					
				});
			}
			
			function gotoDetail(id){
				window.location.href = '<?=APP_URL?>tutorial.php/'+id;
			}
			
			<?php
				if(!isset($_GET['q']) || $_GET['q'] == ''){
			?>
			$(document).ready(function(){
				getTutor('<?=$firstKat?>',1);
			});
			<?php
				}
			?>
		</script>
		<?=$session->getTemplate('footer')?>
		
    </body>
</html>