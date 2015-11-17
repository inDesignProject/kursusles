<?php

/*
	Database function in class Db_connection :
	-	connect
	-	query
	-	exec
*/

class Session{
	
	function registerSessionPengajar($iduserPrime,$iduserChild){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();

		//SELECT DATA LENGKAP
		$sqlPeng	=	sprintf("SELECT NAMA,ALAMAT,EMAIL,CURRENT_BALANCE,FOTO,KTP,GPS_L,GPS_B,VERIFIED_STATUS
								 FROM m_pengajar
								 WHERE IDPENGAJAR = %s
								 LIMIT 0,1"
								, $iduserChild);
		$resultPeng	=	$db->query($sqlPeng);
		$resultPeng	=	$resultPeng[0];
		
		//INSERT LOG ACTIVITY
		$sqlinsert	=	sprintf("INSERT INTO log_login (TYPE_USER, IDUSER, DATETIME)
								 SELECT '2' AS TYPEUSER, %s AS IDUSER, NOW() AS DATETIME"
								, $iduserPrime
								);
		$db->execSQL($sqlinsert, 0);
		
		//UNREG SESSION YANG SAMA DENGAN ID BERBEDA
		$this->unregOtherSession($iduserPrime);
		
		//REGISTER SESSION
		session_start();
		$_SESSION['KursusLes']['IDUSER'] 	= $iduserChild;
		$_SESSION['KursusLes']['IDPRIME'] 	= $iduserPrime;
		$_SESSION['KursusLes']['TYPEUSER'] 	= '2';
		$_SESSION['KursusLes']['NAMA'] 		= $resultPeng['NAMA'];
		$_SESSION['KursusLes']['ALAMAT'] 	= $resultPeng['ALAMAT'];
		$_SESSION['KursusLes']['EMAIL'] 	= $resultPeng['EMAIL'];
		$_SESSION['KursusLes']['BALANCE'] 	= $resultPeng['CURRENT_BALANCE'];
		$_SESSION['KursusLes']['FOTO'] 		= $resultPeng['FOTO'];
		$_SESSION['KursusLes']['KTP'] 		= $resultPeng['KTP'];
		$_SESSION['KursusLes']['GPS'] 		= $resultPeng['GPS_L'].",".$resultPeng['GPS_B'];
		$_SESSION['KursusLes']['VERIFIED'] 	= $resultPeng['VERIFIED_STATUS'];
	
	}
	
	function registerSessionMurid($iduserPrime,$iduserChild){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();

		//SELECT DATA LENGKAP
		$sqlMurid	=	sprintf("SELECT NAMA,ALAMAT,EMAIL,FOTO,GPS_L,GPS_B
								 FROM m_murid
								 WHERE IDMURID = %s
								 LIMIT 0,1"
								, $iduserChild
						);
		$resultMurid=	$db->query($sqlMurid);
		$resultMurid=	$resultMurid[0];
		
		//INSERT LOG ACTIVITY
		$sqlinsert	=	sprintf("INSERT INTO log_login (TYPE_USER, IDUSER, DATETIME)
								 SELECT '1' AS TYPEUSER, %s AS IDUSER, NOW() AS DATETIME"
								, $iduserPrime
								);
		$db->execSQL($sqlinsert, 0);
		
		//UNREG SESSION YANG SAMA DENGAN ID BERBEDA
		$this->unregOtherSession($iduserPrime);
		
		//REGISTER SESSION
		session_start();
		$_SESSION['KursusLes']['IDUSER'] 	= $iduserChild;
		$_SESSION['KursusLes']['IDPRIME'] 	= $iduserPrime;
		$_SESSION['KursusLes']['TYPEUSER'] 	= '1';
		$_SESSION['KursusLes']['NAMA'] 		= $resultMurid['NAMA'];
		$_SESSION['KursusLes']['ALAMAT'] 	= $resultMurid['ALAMAT'];
		$_SESSION['KursusLes']['EMAIL'] 	= $resultMurid['EMAIL'];
		$_SESSION['KursusLes']['FOTO'] 		= $resultMurid['FOTO'];
		$_SESSION['KursusLes']['GPS'] 		= $resultMurid['GPS_L'].",".$resultMurid['GPS_B'];
	
	}
	
	function registerSessionGlobal($iduserPrime,$iduserChild, $sNAMA){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();

		//INSERT LOG ACTIVITY
		$sqlinsert	=	sprintf("INSERT INTO log_login (TYPE_USER, IDUSER, DATETIME)
								 SELECT '2' AS TYPEUSER, %s AS IDUSER, NOW() AS DATETIME"
								, $iduserPrime
								);
		$db->execSQL($sqlinsert, 0);
		
		//UNREG SESSION YANG SAMA DENGAN ID BERBEDA
		$this->unregOtherSession($iduserPrime);
		
		//REGISTER SESSION
		session_start();
		$_SESSION['KursusLes']['IDUSER'] 	= $iduserChild;
		$_SESSION['KursusLes']['IDPRIME'] 	= $iduserPrime;
		$_SESSION['KursusLes']['NAMA'] 		= $sNAMA;
		$_SESSION['KursusLesLoker']['IDUSER'] 	= $iduserChild;
		$_SESSION['KursusLesLoker']['IDPRIME'] 	= $iduserPrime;
		$_SESSION['KursusLesLoker']['NAMA'] 	= $sNAMA;
		
	}
	
	
	function setSession(){
		
		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();
	
		session_start();
		//UPDATE M_USER LAST LOGIN DAN SESSION ID
		$sqlUpdate	=	sprintf("UPDATE m_user SET LAST_LOGIN = NOW(), SESSION_ID = '%s' WHERE IDUSER = %s"
						, session_id()
						, $_SESSION['KursusLes']['IDPRIME']
						);
		$affected	=	$db->execSQL($sqlUpdate, 0);
		
		if($affected > 0){
			return true;
		} else {
			return false;
		}
	
	}
	
	function unregOtherSession($iduser){

		$db			=	new Db_connection();
		session_start();
		//KOSONGKAN DATA SESSION UNTUK USER YANG LAIN SELAIN ID YANG AKTIF
		$sqlupd		=	sprintf("UPDATE m_user SET SESSION_ID = '' WHERE IDUSER <> %s AND SESSION_ID = '%s'"
								, $iduser
								, session_id()
								);
		$db->execSQL($sqlupd, 0);
		
	}
	
	function cekSession(){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();
	
		session_start();
		
		//QUERY SELECT USER CHILD
		$sql		=	sprintf("SELECT LAST_LOGIN FROM m_user WHERE SESSION_ID = '%s' LIMIT 0,1", session_id());
		$result		=	$db->query($sql);
		$lastLogin	=	$result[0]['LAST_LOGIN'];
		$lastLogin	=	strtotime($lastLogin);
		$now		=	strtotime(date("Y-m-d H:i:s"));
		$interval	=	$now-$lastLogin;
		
		if($interval >= 3600){
			//TERLALU LAMA TIDAK MELAKUKAN AKTIVITAS
			unset($_SESSION['KursusLes']);
			return "1";
		} else if(!isset($lastLogin) || $lastLogin == "" || $lastLogin == "null" || !isset($_SESSION['KursusLes'])){
			//TIDAK / BELUM LOGIN
			return "0";
		} else {
			//UPDATE M_USER LAST LOGIN DAN SESSION ID
			$sqlUpdate	=	sprintf("UPDATE m_user SET LAST_LOGIN = NOW(), SESSION_ID = '%s' WHERE IDUSER = %s"
							, session_id()
							, $_SESSION['KursusLes']['IDPRIME']
							);
			$affected	=	$db->execSQL($sqlUpdate, 0);
			return "2";
		}
			
	}
	
	function getTemplate($type = 'header', $login = 'false', $param_t1 = '', $param_t2 = '', $username = '', $password = ''){
		
		require "defines.php";
		$enkripsi	=	new Enkripsi();

		if($login == 'true'){
			
			$login_f	=	@file_get_contents("template/login.php") or $login_f	=	@file_get_contents("../../template/login.php");
			$login_f	=	str_replace(
									array('param_t1', 'param_t2', 'param_username', 'param_password'),
									array($param_t1, $param_t2, $username, $password), 
									$login_f
								   );
			$indexuser	=	"login?authResult=ZQ";
			$top_header	=	'';
			
		} else if($login == 'false') {
		
			$login_f	=	'';
			$show_veri	=	$_SESSION['KursusLes']['VERIFIED'] == "0" ? "inline-block" : "none";
			//$indexuser	=	$_SESSION['KursusLes']['TYPEUSER'] == "1" ? "index_murid" : "index_pengajar";
			$indexuser	=	"profile_global";
			$top_header	=	@file_get_contents("template/top-header.php") or $top_header	=	@file_get_contents("../../template/top-header.php");;
			$top_header	=	str_replace(
									array('APP_IMG_URL', 'IMG_PROFILE_ENCODE', 'PROFILE_NAME', 'DISPLAY_VERIFIED', 'IDUSER_ENCODE', 'INDEX_USER'),
									array(APP_IMG_URL, $enkripsi->encode($_SESSION['KursusLes']['FOTO']), $_SESSION['KursusLes']['NAMA'], $show_veri, $enkripsi->encode($_SESSION['KursusLes']['IDUSER']), $indexuser),
									$top_header
								   );
		} else {
			
			$login_f	=	'';
			$top_header	=	'';
			
		}
		
		$file	=	@file_get_contents("template/".$type.".php") or $file	=	@file_get_contents("../../template/".$type.".php");
		$file	=	str_replace(
								array('year_now()', 'APP_IMG_URL', 'LOGIN_FORM', 'TOP_HEADER','APP_URL','APP_BASE_URL'),
								array(date('Y'), APP_IMG_URL, $login_f, $top_header, APP_URL, APP_BASE_URL), 
								$file
							   );
		return $file;

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