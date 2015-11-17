<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
		
	//FUNGSI DIGUNAKAN UNTUK SIMPAN JADWAL PENGAJAR
	if( $enkripsi->decode($_GET['func']) == "saveJadwal" && isset($_GET['func'])){
		
		sleep(3);
		
		$idpengajar	=	$enkripsi->decode($_POST['idpengajar']);
		$sqlReset	=	sprintf("UPDATE t_jadwal_pengajar SET STATUS = 0 WHERE IDPENGAJAR = %s", $idpengajar);
		$affected	=	$db->execSQL($sqlReset, 0);
		
		if($_POST['typePost'] == 'update'){
			
			$i			=	0;
			
			foreach($_POST['arrInput'] as $key){
				
				if(substr($key,0,5) == 'input'){
					
					$param		=	str_replace("input","",$key);
					$explode	=	explode("_",$param);
					$hari		=	$explode[0];
					$waktu		=	$explode[1];
					
					$sqlUpdate	=	sprintf("UPDATE t_jadwal_pengajar
											 SET STATUS = 1
											 WHERE IDPENGAJAR = %s AND HARI = %s AND WAKTU = %s"
											 , $idpengajar
											 , $hari
											 , $waktu
											);
											
					if($db->execSQL($sqlUpdate, 0) > 0){
						$i++;
					}
			
				}
					
			}

			$respon_code	=	$i > 0 ? "success" : "null";
			$affected		=	$i;
			
		} else {
			$respon_code	=	$affected > 0 ? "success" : "failed";
		}
		
		echo json_encode(array("respon_code"=>$respon_code,
							   "affected"=>$affected)
						);
		die();
		
	}
	
	$idpengajar		=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	$idpengajarenc	=	isset($_GET['q']) ? $_GET['q'] : $enkripsi->encode($_SESSION['KursusLes']['IDUSER']);
	
	$sql		=	sprintf("SELECT HARI, WAKTU, STATUS
							 FROM t_jadwal_pengajar
							 WHERE IDPENGAJAR = '%s'
							 ORDER BY WAKTU, HARI"
							, $idpengajar
							);
	$result		=	$db->query($sql);

	if(!isset($result) || $result == false){
		
		for($i=1;$i<=7;$i++){
			for($j=1;$j<=4;$j++){
				$sqlIns		=	sprintf("INSERT IGNORE INTO t_jadwal_pengajar
										 SET IDPENGAJAR	=	'%s', 
										 	 HARI		=	'%s',
											 WAKTU		=	'%s'"
										, $idpengajar
										, $i
										, $j
								);
				$db->execSQL($sqlIns, 0);
			}
		}
		
		$result		=	$db->query($sql);
		$jumAsal	=	0;

	} else {

		$sqljum		=	sprintf("SELECT COUNT(HARI) AS JUMASAL FROM t_jadwal_pengajar WHERE IDPENGAJAR = '%s' AND STATUS = 1", $_SESSION['KursusLes']['IDUSER']								);
		$resultjum	=	$db->query($sqljum);
		$jumAsal	=	$resultjum[0]['JUMASAL'];

	}
	
if(!isset($_GET['q']) || $_GET['q'] == ''){

	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
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

	<?=$session->getTemplate('header', $show_login)?>
    	
        <div class="container">
        	<h3 class="text-left text_kursusles page-header">JADWAL MENGAJAR SAYA</h3>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
<?php
}
?>
						<style>
                        @import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141223'.'cssKjadwal');?>.cssfile");
                        </style>
                        <div id="Kjadwal_container">
                            <?php
                            if($_SESSION['KursusLes']['TYPEUSER'] == 2 && isset($_SESSION['KursusLes']['IDUSER']) && $_SESSION['KursusLes']['IDUSER'] == $idpengajar){
                            ?>
                            <div id="editor_button" style="text-align:right; margin-bottom:10px">
                                <div id="msg_process" class="hide"></div>
                                <input type="hidden" name="idpengajar" id="idpengajar" value="<?=$idpengajarenc?>"/>
                                <input type="button" name="ubahjadwal" id="ubahjadwal" value="<?=$jumAsal == 0 ? "Susun Jadwal" : "Ubah Jadwal"?>" onclick="showEditor('true');" class="btn-xs btn btn-custom margin0" />
                                <input type="button" name="simpanjadwal" id="simpanjadwal" value="Simpan Perubahan" onclick="saveJadwal();" style="display:none" class="btn-xs btn btn-custom margin0" />
                            </div>
                            <?php
                            }
                            ?>
                           <table class="table table-bordered" id="Kjadwal_table">
                                <thead>
                                    <tr>
                                        <th class="text-center">Waktu/Hari</th>
                                        <th class="text-center">Senin</th>
                                        <th class="text-center">Selasa</th>
                                        <th class="text-center">Rabu</th>
                                        <th class="text-center">Kamis</th>
                                        <th class="text-center">Jumat</th>
                                        <th class="text-center">Sabtu</th>
                                        <th class="text-center">Minggu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $waktu	=	array();
                                    $x		=	0;
                                    
                                    foreach($result as $key){
                                        
                                        if(!in_array($key['WAKTU'], $waktu)){
                                            switch($key['WAKTU']){
                                                case "1"	:	$strwaktu	=	"Pagi"; $class_waktu = "seling"; break;
                                                case "2"	:	$strwaktu	=	"Siang"; $class_waktu = ""; break;
                                                case "3"	:	$strwaktu	=	"Sore"; $class_waktu = "seling"; break;
                                                case "4"	:	$strwaktu	=	"Malam"; $class_waktu = ""; break;
                                                default		:	$strwaktu	=	"--"; $class_waktu = ""; break;
                                            }
                                            array_push($waktu,$key['WAKTU']);
                                            
                                            if($x <> 0) echo "</tr>";
                                            echo "<tr class='".$class_waktu."'>
                                                    <td>".$strwaktu."</td>";
                                        
                                        }
                                        
                                    ?>
                                            <td class="text-center" id="jadwal<?=$key['HARI']."_".$key['WAKTU']?>">
                                                <?=$key['STATUS'] == 1 ? "<i class='fa fa-check-circle fa-lg avilable'></i>" : "<i class='fa fa-check-circle fa-lg unavilable'></i>"?>
                                                <input class="checkJadwal" name="input<?=$key['HARI']."_".$key['WAKTU']?>" id="input<?=$key['HARI']."_".$key['WAKTU']?>" onclick="" value="<?=$key['HARI']."_".$key['WAKTU']?>" type="checkbox" style="display:none" <?=$key['STATUS'] == 1 ? "checked" : ""?>>
                                            </td>
                                    <?php
                                        $x++;
                                    }
                                    ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141224'.'jsskjadwal');?>.jsfile&jumAsal=<?=$jumAsal?>"></script>
<?php
if(!isset($_GET['q']) || $_GET['q'] == ''){
?>
					</div>
            	</div>
            </div>
		</div>
		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>
        <br><br><br>
    	<?=$session->getTemplate('footer')?>
<?php
}
?>
