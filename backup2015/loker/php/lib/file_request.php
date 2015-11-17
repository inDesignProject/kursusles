<?php
	/*
		file_request.php digunakan untuk memberi respon terhadap file yang membutuhkan / memanggil file javascript atau css style
		parameter yang dikirim berupa string terenkripsi dengan kode tertentu dalam variabel $_GET['get'] di dalam URL request
	*/

	//include enkripsi
	include('enkripsi.php');
	require "defines.php";
	$enkripsi	=	new Enkripsi();

	//decode dan split parameter untuk menentukan jenis file
	$param		=	explode(".",$_GET['get']);
	$namefile	=	$enkripsi->decode($param[0]);
	$namefile	=	substr($namefile,8,strlen($namefile)-8);
	$extension	=	$param[1];
	$jstype		=	'';
	
	//membedakan ekstensi js atau css
	switch($extension){
		
		//jika js
		case "jsfile"	:	//cek nama file
							switch($namefile){
							
								case	"jssjquerymin"		:	$loc	=	"../../js/jquery.js";
																break;
								case	"jssjqueryuimin"	:	$loc	=	"../../js/jquery-ui.js";
																break;
								case	"jssajax"			:	$loc	=	"../../js/ajax.js";
																break;
								case	"jssindex"			:	$loc	=	"../../js/index.js";
																break;
								case	"jssscrollbar"		:	$loc	=	"../../js/perfect-scrollbar.js";
																break;
								case	"jssfileapiexif"	:	$loc	=	"../../js/FileAPI.exif.js";
																break;
								case	"jssfileapi"		:	$loc	=	"../../js/FileAPI.min.js";
																break;
								case	"jssjqueryfileapi"	:	$loc	=	"../../js/jquery.fileapi.min.js";
																break;
								case	"jssjqueryjcrop"	:	$loc	=	"../../js/jquery.Jcrop.min.js";
																break;
								case	"jssjquerymodal"	:	$loc	=	"../../js/jquery.modal.js";
																break;
								case	"jssdatepicker"		:	$loc	=	"../../js/jquery.datetimepicker.js";
																break;
								case	"jssvalidate"		:	$loc	=	"../../js/jquery.validate.min.js";
																break;
								case	"jsssignupakun"		:	$loc	=	"../../js/halaman/signup_akun.js";
																$jstype	=	'halaman';
																break;
								case	"jsseditakun"		:	$loc	=	"../../js/halaman/edit_akun.js";
																$jstype	=	'halaman';
																break;
								case	"jsslevel"			:	$loc	=	"../../js/halaman/level.js";
																$jstype	=	'halaman';
																break;
								case	"jsspengajarprofil"	:	$loc	=	"../../js/halaman/pengajar_profil.js";
																$jstype	=	'halaman';
																break;
								case	"jssmap"			:	$loc	=	"../../js/halaman/map.js";
																$jstype	=	'halaman';
																break;
								case	"jsspesan"			:	$loc	=	"../../js/halaman/pesan.js";
																$jstype	=	'halaman';
																break;
								case	"jssprofil"			:	$loc	=	"../../js/halaman/profil.js";
																$jstype	=	'halaman';
																break;
								case	"jsstestimoni"		:	$loc	=	"../../js/halaman/testimoni.js";
																$jstype	=	'halaman';
																break;
								case	"jsskjadwal"		:	$loc	=	"../../js/halaman/kjadwal.js";
																$jstype	=	'halaman';
																break;
								case	"jsssearchdetail"	:	$loc	=	"../../js/halaman/search_detail.js";
																$jstype	=	'halaman';
																break;
								case	"jssmprofil"		:	$loc	=	"../../js/halaman/mprofil.js";
																$jstype	=	'halaman';
																break;
								case	"jssmpencarian"		:	$loc	=	"../../js/halaman/mpencarian.js";
																$jstype	=	'halaman';
																break;
								default						:	$loc	=	"none";
																break;
							
							}
							break;
		
		//jika css
		case "cssfile"	:	//cek nama file
							switch($namefile){
								case	"cssstyle"			:	$loc	=	"../../css/style.css";
																break;
								case	"cssjqueryui"		:	$loc	=	"../../css/jquery-ui.css";
																break;
								case	"cssindex"			:	$loc	=	"../../css/index.css";
																break;
								case	"cssscrollbar"		:	$loc	=	"../../css/perfect-scrollbar.css";
																break;
								case	"csspengajarprofile":	$loc	=	"../../css/pengajar_profile.css";
																break;
								case	"csslevelpengajar"	:	$loc	=	"../../css/level_pengajar.css";
																break;
								case	"cssmap"			:	$loc	=	"../../css/map.css";
																break;
								case	"cssjqueryjcrop"	:	$loc	=	"../../css/jquery.Jcrop.min.css";
																break;
								case	"cssdatepicker"		:	$loc	=	"../../css/jquery.datetimepicker.css";
																break;
								case	"cssfileuploader"	:	$loc	=	"../../css/file_uploader.css";
																break;
								case	"csspesan"			:	$loc	=	"../../css/pesan.css";
																break;
								case	"csstesti"			:	$loc	=	"../../css/testi.css";
																break;
								case	"cssKjadwal"		:	$loc	=	"../../css/Kjadwal.css";
																break;
								case	"csslogin"			:	$loc	=	"../../css/login.css";
																break;
								case	"cssdaftarrekening"	:	$loc	=	"../../css/daftar_rekening.css";
																break;
								case	"cssmain"			:	$loc	=	"../../css/main.css";
																break;
								case	"cssanimate"		:	$loc	=	"../../css/animate.css";
																break;
								case	"cssbootstrap"		:	$loc	=	"../../css/bootstrap.css";
																break;
								case	"cssfontawesome"	:	$loc	=	"../../css/font-awesome.css";
																break;
								case	"cssindexpengajar"	:	$loc	=	"../../css/index_pengajar.css";
																break;
								case	"cssveriakun"		:	$loc	=	"../../css/veri_akun.css";
																break;
								default						:	$loc	=	"none";
																break;
							}
							break;
		
		//jika tidak keduanya / default
		default			:	$loc	=	"none";
							break;
	
	}
	
	//jika file ditemukan, maka respon sebagai file dan lakukan cache (dengan waktu sekitar 1 tahun - max-age=31557600)
	if($loc <> 'none'){
		
		if($jstype == 'halaman'){
			
			$handle = @fopen($loc, "r");
			if ($handle) {

				while (($buffer = fgets($handle, 8192)) !== false) {
					
					if(preg_match('/'.preg_quote('ENCODE(').'(.*?)'.preg_quote(')ENCODE').'/is', $buffer, $match)){
					
						$str_encode	=	$enkripsi->encode($match[1]);
						$buffer		=	str_replace('ENCODE('.$match[1].')ENCODE', $str_encode, $buffer);
						
					}
					
					if(preg_match('/'.preg_quote('DATA__').'(.*?)'.preg_quote('__DATA').'/is', $buffer, $match)){
					
						$str_data	=	$_GET[$match[1]];
						$buffer		=	str_replace('DATA__'.$match[1].'__DATA', $str_data, $buffer);
						
					}

					$hasil	.= trim($buffer);
					//$hasil	.= $buffer;
			
				}
			
			}
			
		} else {
			$hasil	=	file_get_contents($loc);
		}
			
		$hasil	=	str_replace("APP_IMAGE_URL",APP_IMG_URL, $hasil);
		$hasil	=	str_replace("APP_FONTS_URL",APP_FONT_URL, $hasil);
		$hasil	=	str_replace("APP_URL",APP_URL, $hasil);
		
		//jika file berupa js
		if($extension == 'jsfile'){
			header("Content-type: text/javascript");
			header("Cache-Control: public, max-age=31557600");
			header("Pragma:");
			header('Last-Modified: ' . date("r",$last_modified));
		//jika file berupa css
		} else {
			header("Content-type: text/css");
			header("Cache-Control: public, max-age=31557600");
			header("Pragma:");
			header('Last-Modified: ' . date("r",$last_modified));
		}
		
		echo $hasil;
	
	//jika tidak ditemukan	
	} else {
	
		header("HTTP/1.0 304 Not Modified");
		exit();
	
	}
	
?>