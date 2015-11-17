<?php 
	header('Content-type: text/html; charset=utf-8'); 
	require_once "recaptchalib.php";
	$siteKey = "6LfaUwITAAAAAFaBBoco_rYlrNSUlAmpFY0NLgO_";
	$secret = "6LfaUwITAAAAAObkFlGaib55L72osNKKrobm1Su9";
	$lang = "id";
	$resp = null;
	$error = null;
	$reCaptcha = new ReCaptcha($secret);
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
		<link rel="stylesheet" href="dist/vendor/datepicker/jquery.datetimepicker.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="dist/css/animate.css">
		<link rel="stylesheet" href="dist/css/main.css">
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
	<div class="container">
			<div class="row top">
				<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
					<div class="logo">
						<img src="dist/images/logo.png" class="img-responsive" alt="logo" />
					</div>
				</div>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<div class="form-login pull-right">
						<form class="form-inline" method="POST" action="">
							<div class="form-group">
								<label><small>Login sebagai: </small></label>
								<input type="radio" id="login_type" name="login_type" value="1" param_t1 /> <small>Murid</small>
								<input type="radio" id="login_type" name="login_type" value="2" param_t2 /> <small>Pengajar</small>
							</div><br/>
							<div class="form-group">
								<input type="text" class="form-control input-sm" placeholder="Username" name="username" required />
							</div>
							<div class="form-group">
								<input type="password" class="form-control input-sm" placeholder="Password" name="password" required />
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-custom2 btn-sm">MASUK</button>
							</div>
							<br/>
							<div class="tombol text-right">
								<a href="#"><small>Lupa password?</small></a>&nbsp;&nbsp;
								<a href="#" class="btn btn-custom btn-xs">DAFTAR</a>
							</div>
						</form>
					</div>
					<div class="menu">
						<nav class="navbar navbar-default">
							<div class="container-fluid">
								<div class="navbar-header">
									<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar_kursusles">
										<span class="sr-only">Toggle navigation</span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
									</button>
								</div>
								<div class="collapse navbar-collapse" id="navbar_kursusles">
									<ul class="nav navbar-nav navbar-right">
										<li><a href="#">HOME</a></li>
										<li><a href="#">CARI PENGAJAR</a></li>
										<li><a href="#">LOWONGAN</a></li>
										<li><a href="#">TUTORIAL</a></li>
									</ul>
								</div>
							</div>
						</nav>
					</div>
				</div>
			</div>
		</div>
		<div class="blueslogan text-right">
			<div class="container">
				<h3>TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!</h3>
			</div>
		</div>