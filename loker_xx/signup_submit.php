<?php

include('php/lib/db_connection.php');
include('php/lib/enkripsi.php');
include('php/lib/session.php');
require "php/lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

if( $enkripsi->decode($_GET['func']) == "submitFormPemberi" && isset($_GET['func'])){
	
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
	
	//CEK DATA KIRIMAN CAPTCHA
	if($_POST['g-recaptcha-response'] == "" || !isset($_POST['g-recaptcha-response'])){
		echo json_encode(array("respon_code"=>"000004"));
		die();
	} else {
		$cekdata	=	$session->cekGCaptcha($_POST['g-recaptcha-response']);
		if($cekdata <> 2){
			echo json_encode(array("respon_code"=>"000005"));
			die();
		}
	}
	
	//INSERT DATA KE TABEL MASTER
	$sqlins		=	sprintf("INSERT INTO m_perusahaan
							 (NAMA_PERUSAHAAN, ALAMAT_KANTOR, IDPROPINSI, IDKOTA, KODEPOS, TELPON,
							 EMAIL, WEBSITE, JENIS_USAHA, USERNAME, PASSWORD)
							 VALUES
							 ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',MD5('%s'))"
							, $db->db_text($nama)
							, $db->db_text($alamat)
							, $enkripsi->decode($propinsi)
							, $enkripsi->decode($kota)
							, $kodepos
							, $db->db_text($telpon)
							, $db->db_text($email)
							, $db->db_text($website)
							, $enkripsi->decode($jnsusaha)
							, $db->db_text($username)
							, $db->db_text($password1)
							);
	//EKSEKUSI SQL, DAPATKAN LAST ID
	$lastID			=	$db->execSQL($sqlins, 1);
	
	//JIKA LAST ID > 0, MAKA UPDATE ID_USERCHILD M_USER DENGAN LASTIDUSER
	if($lastID > 0){
		
		$message	=	"<html>
						 <head>
						 </head>
						
						 <body>
							
							<p>
							Halo ".$nama.",<br/><br/>
							selamat datang di Loker KursusLes.com. Anda mendaftar sebagai sebuah perusahaan di KursusLes Loker dengan data:<br /><br/>
							Username : ".$username."<br />
							Password : ".$password1."<br /><br/>
							Untuk melakukan aktivasi akun anda, harap klik tautan dibawah ini:<br /><br />
						
							".APP_URL."signup_activation/?u=".$enkripsi->encode($lastID)."&p=".md5($password1)."&tu=1
							<br /><br />
						
							setelah melakukan aktivasi, anda bisa melengkapi data profil.<br /><br /><br><br>
							
							Best regards,<br /><br />
							Admin Loker KursusLes.com
							</p>
						
						 </body>
						 </html>";
		$session->sendEmail($email, $username, "Selamat Bergabung di Loker KursusLes.com", $message);
		
		//DATA MASUK, KIRIM RESPON
		echo json_encode(array(
								"respon_code"=>"000000",
								"respon_msg"=>"<center><b>Terima kasih sudah mendaftar. Silakan cek kotak masuk pada email anda untuk memverfikasi akun. Cek juga pada kotak Spam</b></center>"
							  )
							);
		die();
			
	//JIKA LAST ID TIDAK VALID, MAKA KIRIM PESAN ERROR
	} else {

		echo json_encode(array(
							"respon_code"=>"000001")
						);
		die();

	}

//JIKA PENDAFTARAN KARYAWAN
} else if( $enkripsi->decode($_GET['func']) == "submitFormPencari" && isset($_GET['func'])){
	
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
	
	//CEK DATA KIRIMAN CAPTCHA
	if($_POST['g-recaptcha-response'] == "" || !isset($_POST['g-recaptcha-response'])){
		echo json_encode(array("respon_code"=>"000004"));
		die();
	} else {
		$cekdata	=	$session->cekGCaptcha($_POST['g-recaptcha-response']);
		if($cekdata <> 2){
			echo json_encode(array("respon_code"=>"000005"));
			die();
		}
	}
	
	//INSERT DATA KE TABEL MASTER
	$sqlins		=	sprintf("INSERT INTO m_karyawan
							 (NAMA, ALAMAT, IDPROPINSI, IDKOTA, TELPON, EMAIL, USERNAME, PASSWORD)
							 VALUES
							 ('%s','%s','%s','%s','%s','%s','%s',MD5('%s'))"
							, $db->db_text($nama)
							, $db->db_text($alamat)
							, $enkripsi->decode($propinsi)
							, $enkripsi->decode($kota)
							, $db->db_text($telpon)
							, $db->db_text($email)
							, $db->db_text($username)
							, $db->db_text($password1)
							);
	//EKSEKUSI SQL, DAPATKAN LAST ID
	$lastID			=	$db->execSQL($sqlins, 1);
	
	//JIKA LAST ID > 0, MAKA UPDATE ID_USERCHILD M_USER DENGAN LASTIDUSER
	if($lastID > 0){
		
		$message	=	"<html>
						 <head>
						 </head>
						
						 <body>
							
							<p>
							Halo ".$nama.",<br/><br/>
							selamat datang di Loker KursusLes.com. Anda mendaftar sebagai seorang pencari kerja di KursusLes Loker dengan data:<br /><br/>
							Username : ".$username."<br />
							Password : ".$password1."<br /><br/>
							Untuk melakukan aktivasi akun anda, harap klik tautan dibawah ini:<br /><br />
						
							".APP_URL."signup_activation/?u=".$enkripsi->encode($lastID)."&p=".md5($password1)."&tu=2
							<br /><br />
						
							setelah melakukan aktivasi, anda bisa melengkapi data profil.<br /><br /><br><br>
							
							Best regards,<br /><br />
							Admin Loker KursusLes.com
							</p>
						
						 </body>
						 </html>";
		$session->sendEmail($email, $username, "Selamat Bergabung di Loker KursusLes.com", $message);
		
		//DATA MASUK, KIRIM RESPON
		echo json_encode(array(
								"respon_code"=>"000000",
								"respon_msg"=>"<center><b>Terima kasih sudah mendaftar. Silakan cek kotak masuk pada email anda untuk memverfikasi akun. Cek juga pada kotak Spam</b></center>"
							  )
							);
		die();
			
	//JIKA LAST ID TIDAK VALID, MAKA KIRIM PESAN ERROR
	} else {

		echo json_encode(array("respon_code"=>"000001"));
		die();

	}	
	
//JIKA URL KIRIMAN TIDAK VALID	
} else {
	echo "Restricted Access";
	die();
}

?>