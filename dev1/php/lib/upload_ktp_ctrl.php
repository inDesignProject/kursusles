<?php

	include('db_connection.php');
	include('enkripsi.php');
	include('session.php');
	
	require_once('SimpleImage.php');
	
	$db				=	new	Db_connection();
	$enkripsi		=	new Enkripsi();
	$session		=	new Session();
	$imageResize	=	new SimpleImage();
	
	session_start();

	//CEK DATA UPLOAD, JIKA PARAMETER VALID
	if($_FILES['filedata']['name']){
		
		//JIKA FILE TIDAK MENGANDUNG ERROR
		if(!$_FILES['filedata']['error']){
			
			//NAMA FILE (BENTUK ENKRIPSI)
			$new_file_name = $enkripsi->encode($_SESSION['KursusLes']['IDPRIME']).'.jpg';
			
			//JIKA FILE LEBIH BESAR DARI 1MB
			if($_FILES['filedata']['size'] > (2048000)){
				$valid_file = false;
			//JIKA UKURAN FILE MASIH DIIJINKAN		
			} else {
				$valid_file = true;
			}
			
			//JIKA UKURAN DIIJINKAN
			if($valid_file){
				
				$filename	=	'../../images/ktp/tmp_'.$new_file_name;
				if(file_exists($filename)) unlink($filename);
				
				//JIKA UPLOAD BERHASIL
				if(move_uploaded_file($_FILES['filedata']['tmp_name'], $filename)) {
					
					//CEK UKURAN IMAGE FOTO
					$sizearray	= getimagesize($filename);
					$width		= $sizearray[0];
					$height		= $sizearray[1];
					
					//CEK IMAGE MINIMAL HARUS 300px ATAU LEBIH
					if($width < 300 || $height < 300){
						$response	=	-5;
						//"-5-".$width."-".$height;
					} else {
						
						$imageResize->load($filename);
						$imageResize->resize(400, 240);
						$imageResize->save($filename);
						
						$response	=	1;
					}
					
				//JIKA UPLOAD GAGAL
				} else { 
					$response	=	-4;
				}

			//RESPONSE JIKA FILE TERLALU BESAR
			} else {
				$response	=	-3;
			}
		
		//JIKA FILE ERROR
		} else {
			
			$response	=	-2;

		}
		
	//JIKA PARAMETER TIDAK SESUAI
	} else {
		
		$response	=	-1;
		
	}
	
	echo $response;
	die();
		
?>