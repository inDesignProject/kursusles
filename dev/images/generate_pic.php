<?php

include('../php/lib/enkripsi.php');
include('../php/lib/SimpleImage.php');
require "../php/lib/defines.php";
$enkripsi		=	new Enkripsi();

switch($_GET['type']){
	case "pr"	:	$result	=	$enkripsi->decode($_GET['q']);
					$bool	=	true;
					$dir	=	"profile";
					break;
	case "mr"	:	$result	=	$enkripsi->decode($_GET['q']);
					$bool	=	true;
					$dir	=	"profile";
					break;
	case "kr"	:	$result	=	$enkripsi->decode($_GET['q']);
					$bool	=	true;
					$dir	=	"karyawan";
					break;
	case "ktp"	:	$result	=	$enkripsi->decode($_GET['q']);
					$bool	=	true;
					$dir	=	"ktp";
					break;
	default		:	$result	=	"";
					$bool	=	false;
					break;
}

if($bool	==	false){
	die("HTTP/1.1 404 Not Found");
} else {
	
	if (file_exists($dir."/".$result)) {
		$image_name	=	$result;
	} else {
		$image_name	=	"_default.jpg";
	}

	$image_name		=	$image_name == "" ? "_default.jpg" : $image_name;
	$namefile		=	$dir."/".$image_name;

	if(isset($_GET['w']) && isset($_GET['h']) && $_GET['w'] <> '' && $_GET['h'] <> ''){

		header	("Content-Type: image/jpeg");
		$image 		= 	imagecreatefromjpeg($namefile);
		$new_image	=	imagecreatetruecolor($_GET['w'], $_GET['h']);
		imagecopyresampled($new_image, $image, 0, 0, 0, 0, $_GET['w'], $_GET['h'], imagesx($image), imagesy($image));
		
		imagejpeg		($new_image,NULL);
		imagedestroy	($new_image);

	} else {

		header	("Content-Type: image/jpeg");
		$image 		= 	imagecreatefromjpeg($namefile);
		imagejpeg		($image,NULL);
		imagedestroy	($image);

	}
	
}

?>