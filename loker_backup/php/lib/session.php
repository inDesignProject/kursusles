<?php

/*
	Database function in class Db_connection :
	-	connect
	-	query
	-	exec
*/

class Session{
	
	function registerSessionPers($iduser){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();

		//SELECT DATA LENGKAP
		$sqlPeng	=	sprintf("SELECT NAMA_PERUSAHAAN, ALAMAT_KANTOR, EMAIL, LOGO
								 FROM m_perusahaan
								 WHERE IDPERUSAHAAN = %s
								 LIMIT 0,1"
								, $iduser
								);
		$resultPeng	=	$db->query($sqlPeng);
		$resultPeng	=	$resultPeng[0];
		
		//INSERT LOG ACTIVITY
		$sqlinsert	=	sprintf("INSERT INTO log_login (TYPE_USER, IDUSER, DATETIME)
								 SELECT '1' AS TYPEUSER, %s AS IDUSER, NOW() AS DATETIME"
								, $iduser
								);
		$db->execSQL($sqlinsert, 0);
		
		//UNREG SESSION YANG SAMA DENGAN ID BERBEDA
		$this->unregOtherSession($iduser, 1);
		
		//REGISTER SESSION
		session_start();
		$_SESSION['KursusLesLoker']['IDUSER'] 	= $iduser;
		$_SESSION['KursusLesLoker']['TYPEUSER'] = '1';
		$_SESSION['KursusLesLoker']['NAMA'] 	= $resultPeng['NAMA_PERUSAHAAN'];
		$_SESSION['KursusLesLoker']['ALAMAT'] 	= $resultPeng['ALAMAT_KANTOR'];
		$_SESSION['KursusLesLoker']['EMAIL'] 	= $resultPeng['EMAIL'];
		$_SESSION['KursusLesLoker']['LOGO'] 	= $resultPeng['LOGO'];
		
	}
	
	function registerSessionKary($iduser){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();

		//SELECT DATA LENGKAP
		$sqlKary	=	sprintf("SELECT NAMA,ALAMAT,EMAIL,FOTO
								 FROM m_karyawan
								 WHERE IDKARYAWAN = %s
								 LIMIT 0,1"
								, $iduser
						);
		$resultKary	=	$db->query($sqlKary);
		$resultKary	=	$resultKary[0];
		
		//INSERT LOG ACTIVITY
		$sqlinsert	=	sprintf("INSERT INTO log_login (TYPE_USER, IDUSER, DATETIME)
								 SELECT '2' AS TYPEUSER, %s AS IDUSER, NOW() AS DATETIME"
								, $iduser
								);
		$db->execSQL($sqlinsert, 0);
		
		//UNREG SESSION YANG SAMA DENGAN ID BERBEDA
		$this->unregOtherSession($iduser, 2);
		
		//REGISTER SESSION
		session_start();
		$_SESSION['KursusLesLoker']['IDUSER'] 	= $iduser;
		$_SESSION['KursusLesLoker']['TYPEUSER'] = '2';
		$_SESSION['KursusLesLoker']['NAMA'] 	= $resultKary['NAMA'];
		$_SESSION['KursusLesLoker']['ALAMAT'] 	= $resultKary['ALAMAT'];
		$_SESSION['KursusLesLoker']['EMAIL'] 	= $resultKary['EMAIL'];
		$_SESSION['KursusLesLoker']['FOTO'] 	= $resultKary['FOTO'];
	
	}
	
	function setSession(){
		
		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();
	
		session_start();
		$table		=	$_SESSION['KursusLesLoker']['TYPEUSER'] == "1" ? "m_perusahaan" : "m_karyawan";
		$field		=	$_SESSION['KursusLesLoker']['TYPEUSER'] == "1" ? "IDPERUSAHAAN" : "IDKARYAWAN";
		
		//UPDATE M_USER LAST LOGIN DAN SESSION ID
		$sqlUpdate	=	sprintf("UPDATE %s SET LAST_LOGIN = NOW(), SESSION_ID = '%s' WHERE %s = %s"
						, $table
						, session_id()
						, $field
						, $_SESSION['KursusLesLoker']['IDUSER']
						);
		$affected	=	$db->execSQL($sqlUpdate, 0);
		
		if($affected > 0){
			return true;
		} else {
			return false;
		}
	
	}
	
	function unregOtherSession($iduser, $type){

		$db			=	new Db_connection();
		session_start();
		$table		=	$type == "1" ? "m_perusahaan" : "m_karyawan";
		$field		=	$type == "1" ? "IDPERUSAHAAN" : "IDKARYAWAN";
		
		//KOSONGKAN DATA SESSION UNTUK USER YANG LAIN SELAIN ID YANG AKTIF
		$sqlupd		=	sprintf("UPDATE %s SET SESSION_ID = '' WHERE %s <> %s AND SESSION_ID = '%s'"
								, $table
								, $field
								, $iduser
								, session_id()
								);
		$db->execSQL($sqlupd, 0);
		
	}
	
	function cekSession(){

		$db			=	new Db_connection();
		$enkripsi	=	new Enkripsi();
	
		session_start();

		$table		=	$_SESSION['KursusLesLoker']['TYPEUSER'] == "1" ? "m_perusahaan" : "m_karyawan";
		$field		=	$_SESSION['KursusLesLoker']['TYPEUSER'] == "1" ? "IDPERUSAHAAN" : "IDKARYAWAN";

		//QUERY SELECT USER CHILD
		$sql		=	sprintf("SELECT LAST_LOGIN FROM %s WHERE SESSION_ID = '%s' LIMIT 0,1", $table, session_id());
		$result		=	$db->query($sql);
		$lastLogin	=	$result[0]['LAST_LOGIN'];
		$lastLogin	=	strtotime($lastLogin);
		$now		=	strtotime(date("Y-m-d H:i:s"));
		$interval	=	$now-$lastLogin;
		
		if($interval >= 3600){
			//TERLALU LAMA TIDAK MELAKUKAN AKTIVITAS
			unset($_SESSION['KursusLesLoker']);
			return "1";
		} else if(!isset($lastLogin) || $lastLogin == "" || $lastLogin == "null" || !isset($_SESSION['KursusLesLoker'])){
			//TIDAK / BELUM LOGIN
			return "0";
		} else {
			//UPDATE M_USER LAST LOGIN DAN SESSION ID
			$sqlUpdate	=	sprintf("UPDATE %s SET LAST_LOGIN = NOW(), SESSION_ID = '%s' WHERE %s = %s"
							, $table
							, session_id()
							, $field
							, $_SESSION['KursusLesLoker']['IDUSER']
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
			$indexuser	=	$_SESSION['KursusLesLoker']['TYPEUSER'] == "1" ? "index_pers" : "index_kary";
			$top_header	=	@file_get_contents("template/top-header.php") or $top_header	=	@file_get_contents("../../template/top-header.php");;
			$top_header	=	str_replace(
									array('APP_IMG_URL', 'PROFILE_NAME', 'IDUSER_ENCODE', 'INDEX_USER'),
									array(APP_IMG_URL, $_SESSION['KursusLesLoker']['NAMA'], $enkripsi->encode($_SESSION['KursusLesLoker']['IDUSER']), $indexuser),
									$top_header
								   );
		} else {
			
			$login_f	=	'';
			$top_header	=	'';
			
		}
		
		$file	=	@file_get_contents("template/".$type.".php") or $file	=	@file_get_contents("../../template/".$type.".php");
		$file	=	str_replace(
								array('year_now()', 'APP_IMG_URL', 'LOGIN_FORM', 'TOP_HEADER','APP_URL','INDEX_USER'),
								array(date('Y'), APP_IMG_URL, $login_f, $top_header, APP_URL, $indexuser), 
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