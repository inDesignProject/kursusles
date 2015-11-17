<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	$response	=	"";
	
	session_start();
	
	//CEK SESSION UNTUK FORM LOGIN
	$show_login		=	$session->cekSession() == 2 ? "false" : "true";
	//HABIS - CEK SESSION
	
	//JIKA ADA KIRIMAN DATA PENCARIAN
	if($enkripsi->decode($_GET['func']) == "resetPassword" && isset($_GET['func'])){
		
		if($_POST['reset_type'] == 1){
			$sqlCek		=	sprintf("SELECT IDPERUSAHAAN AS IDUSER, NAMA_PERUSAHAAN AS NAMA, EMAIL, USERNAME
									 FROM m_perusahaan
									 WHERE EMAIL = '%s'
									 LIMIT 0,1"
									, $_POST['email']
								);
			$jnsFunc	=	'1';
		} else {
			$sqlCek		=	sprintf("SELECT IDKARYAWAN AS IDUSER, NAMA, EMAIL, USERNAME
									 FROM m_karyawan
									 WHERE EMAIL = '%s'
									 LIMIT 0,1"
									, $_POST['email']
								);
			$jnsFunc	=	'2';
		}
		$resultCek	=	$db->query($sqlCek);
		
		if($resultCek <> "" && $resultCek <> false){
			
			$resultCek	=	$resultCek[0];
			$message	=	"<html>
							<head>
							</head>
							
							<body>
								
								<p>
								Halo ".$resultCek['NAMA'].",<br/><br/>
								Anda meminta untuk mengatur ulang akun di Loker KursusLes dengan data:<br /><br/>
								Username : ".$resultCek['USERNAME']."<br />
								Password : ".$_POST['password']."<br /><br/>
								harap klik tautan dibawah ini agar akun dapat diaktivasi ulang:<br /><br />
							
								".APP_URL."signup_activation/?t=".$enkripsi->encode('resetPwdK')."&u=".$enkripsi->encode($resultCek['IDUSER'])."&p=".md5($_POST['password'])."&tu=".$jnsFunc."
								<br /><br />
							
								abaikan email ini jika anda tidak merasa meminta pengaturan ulang akun yang ada di Loker KursusLes.com.<br /><br /><br><br>
								
								Best regards,<br /><br />
								Admin KursusLes.com
								</p>
							
							</body>
							</html>";
			
			$session->sendEmail($resultCek['EMAIL'], $resultCek['NAMA'], "Reset akun di KursusLes.com", $message);
			
		}
		
		$response	=	"	<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
								<div class='boxSquare'>
									<center><b>Data akun anda sudah kami reset. Cek email untuk aktivasi ulang</b></center>
								</div>
							</div>";
		
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
    	<?php if($pesan <> ""){ ?>
        <div id="login_msg"><?=$pesan?></div>
		<? } ?>
        
    	<?=$session->getTemplate('header', $show_login, $param_t1, $param_t2, $username, $password)?>
        
        <div class="container">
        	<h3 class="text-left text_kursusles page-header">LUPA PASSWORD</h3>
            <div class="row">
				<?php
                if($response <> ""){
                    echo $response;
                } else {
                ?>
                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                    <div class="boxSquare">
						<div class="panel panel-default">
                            <div class="panel-heading"><b>Form Pengaturan Ulang</b></div>
                            <div class="panel-body">
                                <form action="forgot_password.php?func=<?=$enkripsi->encode('resetPassword')?>" id="login" method="post">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            	<small><b>Status Anda Sebagai</b></small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                <input type="radio" id="reset_type" name="reset_type" value="1" checked /> <small>Pemberi Kerja</small>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                <input type="radio" id="reset_type" name="reset_type" value="2" /> <small>Pencari Kerja</small>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="email" id="email" class="form-control" maxlength="25" placeholder="Masukkan email anda" />
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" id="password" class="form-control" maxlength="25" placeholder="Masukkan password baru anda" />
                                    </div>
                                        
                                    <span class="devider"></span>
                        
                                    <div id="button_container">
                                        <input type="submit" id="submit" name="submit" value="Reset Password" class="btn btn-sm btn-custom2" />
                                    </div>
                                </form>
                            </div>
                    	</div>
                	</div>
                </div>
                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
                    <div class="boxSquare">
						<div class="panel panel-default">
                            <div class="panel-heading"><b>Informasi</b></div>
                            <div class="panel-body">
                                <p>
                                    Masukkan Email dan password baru yang diinginkan. Kami akan mengirimkan tautan verifikasi ke email anda
                                </p>
                        	</div>
						</div>
                    </div>
                </div>
                <?php
				}
				?>
            </div>
        </div>
        <br/>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.4.1/holder.js"></script>

    	<?=$session->getTemplate('footer')?>

    </body>
</html>