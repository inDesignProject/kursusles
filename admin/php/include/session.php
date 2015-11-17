<?php

/*
	Database function in class Db_connection :
	-	connect
	-	query
	-	exec
*/

class Session{
	
	function registerSession($iduser){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();

		//SELECT DATA LENGKAP
		$sqlUser	=	sprintf("SELECT USERNAME, LEVEL
								 FROM admin_user
								 WHERE IDUSER = %s
								 LIMIT 0,1"
								, $iduser);
		$resultUser	=	$db->query($sqlUser);
		$resultUser	=	$resultUser[0];
		
		//INSERT LOG ACTIVITY
		$sqlinsert	=	sprintf("INSERT INTO admin_log_login 
								 SET USERNAME = '%s', DATETIME =  NOW()"
								, $resultUser['USERNAME']
								);
		$db->execSQL($sqlinsert, 0);
		
		//REGISTER SESSION
		session_start();
		$_SESSION['KursusLesAdmin']['IDUSER'] 	= $iduser;
		$_SESSION['KursusLesAdmin']['USERNAME'] = $resultUser['USERNAME'];
		$_SESSION['KursusLesAdmin']['LEVEL'] 	= $resultUser['LEVEL'];
	
	}
	
	function setSession(){
		
		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();
	
		session_start();
		//UPDATE M_USER LAST LOGIN DAN SESSION ID
		$sqlUpdate	=	sprintf("UPDATE admin_user SET LAST_LOGIN = NOW(), SESSION_ID = '%s' WHERE IDUSER = %s"
						, session_id()
						, $_SESSION['KursusLesAdmin']['IDUSER']
						);
		$affected	=	$db->execSQL($sqlUpdate, 0);
		
		if($affected > 0){
			return true;
		} else {
			return false;
		}
	
	}
	
	function cekSession(){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();
	
		session_start();
		
		//QUERY SELECT USER CHILD
		$sql		=	sprintf("SELECT LAST_LOGIN FROM admin_user WHERE SESSION_ID = '%s' LIMIT 0,1", session_id());
		$result		=	$db->query($sql);
		
		$lastLogin	=	$result[0]['LAST_LOGIN'];
		$lastLogin	=	strtotime($lastLogin);
		$now		=	strtotime(date("Y-m-d H:i:s"));
		$interval	=	$now-$lastLogin;

		if($interval >= 3600){
			//TERLALU LAMA TIDAK MELAKUKAN AKTIVITAS
			return "1";
		} else if(!isset($lastLogin) || $lastLogin == "" || $lastLogin == "null" || !isset($_SESSION['KursusLesAdmin'])){
			//TIDAK / BELUM LOGIN
			return "0";
		} else {
			//UPDATE M_USER LAST LOGIN DAN SESSION ID
			$sqlUpdate	=	sprintf("UPDATE admin_user SET LAST_LOGIN = NOW(), SESSION_ID = '%s' WHERE IDUSER = %s"
							, session_id()
							, $_SESSION['KursusLesAdmin']['IDUSER']
							);
			$affected	=	$db->execSQL($sqlUpdate, 0);
			return "2";
		}
			
	}
	
	function cekGCaptcha($rcpResponse){
		
		require "defines.php";
		$recaptcha	=	$rcpResponse;
		
		if(!empty($recaptcha)){
			
			$google_url		=	GOOGLE_RECAPTCHA_URL."api/siteverify";
			$secret			=	GOOGLE_RECAPTCHA_SECRET;
			$ip				=	APP_BASE_URL;
			$url			=	$google_url."?secret=".$secret."&response=".$recaptcha."&remoteip=".$ip;
			$curl			=	curl_init();
			
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
			$curlData		=	curl_exec($curl);
			curl_close($curl);

			$res			=	json_decode($curlData, true);

			if($res['success']){
				return 2;
			} else {
				return "1";
			}
		
		} else {
			return "1";
		}
		
	}
	
	function sendEmail($to, $toName, $subject, $htmlMsg){
			
		/*CEK JIKA EMAIL TIDAK BISA TERKIRIM
			https://support.google.com/mail/answer/78754
		*/
		
		date_default_timezone_set('Asia/Jakarta');
		require 'PHPMailerAutoload.php';
		
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host			= 'smtp.gmail.com';
		$mail->Port			= 587;
		$mail->SMTPSecure	= 'tls';
		$mail->SMTPAuth 	= true;
		$mail->Username 	= "kursusles@gmail.com";
		$mail->Password 	= "kursusles2014";
		$mail->setFrom('NoReply@kursusles.com', 'No Reply KursusLes.com');
		$mail->addReplyTo('kursusles@gmail.com', 'Admin KursusLes');
		$mail->addAddress($to, $toName);
		$mail->Subject		= $subject;
		$mail->msgHTML($htmlMsg);
		
		if (!$mail->send()) {
			return false;
		} else {
			return true;
		}

	}

}
?>