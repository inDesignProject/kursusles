<?php
	/*
		file_request.php digunakan untuk memberi respon terhadap file yang membutuhkan / memanggil file javascript atau css style
		parameter yang dikirim berupa string terenkripsi dengan kode tertentu dalam variabel $_GET['get'] di dalam URL request
	*/

	//include enkripsi
	include('../include/enkripsi.php');
	require "../include/defines.php";
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
								case	"jssdatepicker"		:	$loc	=	"../../js/jquery.datetimepicker.js";
																break;
								case	"jssajax"			:	$loc	=	"../../js/ajax.js";
																break;
								case	"jsstinyeditor"		:	$loc	=	"../../js/tiny.editor.js";
																break;
								case	"jssalert"			:	$loc	=	"../../js/jalert.js";
																break;
								default						:	$loc	=	"none";
																break;
							
							}
							break;
		
		//jika css
		case "cssfile"	:	//cek nama file
							switch($namefile){
								case	"cssmain"			:	$loc	=	"../../css/main.css";
																break;
								case	"cssstyle_contrast"	:	$loc	=	"../../css/style_contrast.css";
																break;
								case	"cssroboto"			:	$loc	=	"../../css/roboto.css";
																break;
								case	"csspopUp"			:	$loc	=	"../../css/popUp.css";
																break;
								case	"cssuniform"		:	$loc	=	"../../css/uniform.css";
																break;
								case	"cssgrowl"			:	$loc	=	"../../css/growl.css";
																break;
								case	"cssdatepicker"		:	$loc	=	"../../css/datepicker.css";
																break;
								case	"cssautocomplete"	:	$loc	=	"../../css/autocomplete.css";
																break;
								case	"csstagsinput"		:	$loc	=	"../../css/tagsinput.css";
																break;
								case	"cssspinner"		:	$loc	=	"../../css/spinner.css";
																break;
								case	"csschosen"			:	$loc	=	"../../css/chosen.css";
																break;
								case	"cssuploader"		:	$loc	=	"../../css/uploader.css";
																break;
								case	"cssanimate"		:	$loc	=	"../../css/animate.css";
																break;
								case	"cssmainorigin"		:	$loc	=	"../../css/main-origin.css";
																break;
								case	"cssfontawesome"	:	$loc	=	"../../css/font-awesome.css";
																break;
								case	"csstinyeditor"		:	$loc	=	"../../css/tiny.editor.css";
																break;
								case	"cssjqueryui"		:	$loc	=	"../../css/jquery-ui.css";
																break;
								case	"cssdatetimepicker"	:	$loc	=	"../../css/jquery.datetimepicker.css";
																break;
								case	"cssbootstrap"		:	$loc	=	"../../css/bootstrap.css";
																break;
								default						:	$loc	=	"none";
																break;
							}
							break;
		
		//jika tidak keduanya / default
		default			:	$loc	=	"none";
							break;
	
	}
//echo $namefile; die();	
	//jika file ditemukan, maka respon sebagai file dan lakukan cache (dengan waktu sekitar 1 tahun - max-age=31557600)
	if($loc <> 'none'){
		
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
			
		$hasil	=	str_replace("APP_IMG_URL",APP_IMG_URL, $hasil);
		$hasil	=	str_replace("APP_FONT_URL",APP_FONT_URL, $hasil);
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