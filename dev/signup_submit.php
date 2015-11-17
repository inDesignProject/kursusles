<?php

include('php/lib/db_connection.php');
include('php/lib/enkripsi.php');
include('php/lib/session.php');
require "php/lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

if( $enkripsi->decode($_GET['func']) == "submitForm" && isset($_GET['func'])){
	
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
	
	//CEK DATA KOTA
	$sqlkota		=	sprintf("SELECT IDKOTA FROM m_kota WHERE NAMA_KOTA = '%s' LIMIT 0,1", $tempat_lahir);
	$resultkota		=	$db->query($sqlkota);
	
	if(isset($resultkota) && $resultkota <> false) {
		$idkotalahir=	$resultkota[0]['IDKOTA'];
	} else {
		echo json_encode(array(
							"respon_code"=>"000003")
						);
		die();
	}
	//HABIS -- CEK DATA KOTA
	
	//CEK DATA KIRIMAN CAPTCHA
	if($_POST['g-recaptcha-response'] == "" || !isset($_POST['g-recaptcha-response'])){
		echo json_encode(array(
								"respon_code"=>"000004")
							);
		die();
	} else {
		$cekdata	=	$session->cekGCaptcha($_POST['g-recaptcha-response']);
		if($cekdata <> 2){
			echo json_encode(array(
								"respon_code"=>"000005")
							);
			die();
		}
	}
	
	//$tabel			=	$jenis_akun == "Pengajar" ? "m_pengajar" : "m_murid";
	//$idlevel		=	$jenis_akun == "Pengajar" ? "1" : "2";
	$idlevel		= "1";
	$tabel			= "m_murid";
	
	//SIMPAN USERNAME TERLEBIH DAHULU
	$sqlUser	=	sprintf("INSERT IGNORE INTO m_user (USERNAME,PASSWORD,TGL_DAFTAR, STATUS) VALUES ('%s',MD5('%s'),NOW(), 0)"
							, $username
							, $password1
							);
	
	$lastIDUser	=	$db->execSQL($sqlUser, 1);

	//JIKA INSERT BERHASIL, LASTIDUSER SEHARUSNYA > 0
	if($lastIDUser > 0){
		
		//INSERT DATA KE TABEL M_MURID / M_PENGAJAR
		$sqlins		=	sprintf("INSERT INTO %s (NAMA,ALAMAT,JNS_KELAMIN,TELPON,TGL_LAHIR,IDKOTA_LAHIR,IDKOTA_TINGGAL,IDKECAMATAN,IDKELURAHAN,KODEPOS,EMAIL, iduser)
									VALUES
									('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s')"
								, $tabel
								, $nama
								, $alamat
								, $jns_kelamin
								, $telpon
								, $tgl_lahir
								, $idkotalahir
								, $enkripsi->decode($kota)
								, $enkripsi->decode($kecamatan)
								, $enkripsi->decode($kelurahan)
								, $kodepos
								, $email
								, $lastIDUser	
								);
		//EKSEKUSI SQL, DAPATKAN LAST ID
		$lastID			=	$db->execSQL($sqlins, 1);
		
		$tabel			= "m_pengajar";
		$sqlins		=	sprintf("INSERT INTO %s (NAMA,ALAMAT,JNS_KELAMIN,TELPON,TGL_LAHIR,IDKOTA_LAHIR,IDKOTA_TINGGAL,IDKECAMATAN,IDKELURAHAN,KODEPOS,EMAIL, iduser)
									VALUES
									('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s')"
								, $tabel
								, $nama
								, $alamat
								, $jns_kelamin
								, $telpon
								, $tgl_lahir
								, $idkotalahir
								, $enkripsi->decode($kota)
								, $enkripsi->decode($kecamatan)
								, $enkripsi->decode($kelurahan)
								, $kodepos
								, $email
								, $lastIDUser	
								);
		//EKSEKUSI SQL, DAPATKAN LAST ID
		$lastID			=	$db->execSQL($sqlins, 1);
		
		$sql = "";
		$sql .= "INSERT INTO m_perusahaan (iduser) VALUES(".$lastIDUser.")";
		$db->execSQL($sql, 0);
		
		$sql = "";
		$sql .= "INSERT INTO m_karyawan (iduser,NAMA,ALAMAT,JK,TELPON,TGL_LAHIR,IDKOTA)"; 
		$sql .= "VALUES(".$lastIDUser.", ";
		$sql .= "'".$nama."', ";
		$sql .= "'".$alamat."', ";
		$sql .= "'".$jns_kelamin."', ";
		$sql .= "'".$telpon."', ";
		$sql .= "'".$tgl_lahir."', ";
		$sql .= $enkripsi->decode($kota).") ";
		$db->execSQL($sql, 0);
		
		//JIKA LAST ID > 0, MAKA UPDATE ID_USERCHILD M_USER DENGAN LASTIDUSER
		if($lastID > 0){
			
			
			
			//JIKA DATA SUDAH MASUK, KIRIM EMAIL VERIFIKASI
			
				
				$message	=	"<html>
								<head>
								</head>
								
								<body>
									
									<p>
									Halo ".$nama.",<br/><br/>
									selamat datang di KursusLes.com. Anda mendaftar sebagai seorang ".$jenis_akun." di KursusLes dengan data:<br /><br/>
									Username : ".$username."<br />
									Password : ".$password1."<br /><br/>
									Untuk melakukan aktivasi akun anda, harap klik tautan dibawah ini:<br /><br />
								
									".APP_URL."signup_activation/?u=".$enkripsi->encode($lastIDUser)."&p=".md5($password1)."
									<br /><br />
								
									setelah melakukan aktivasi, anda bisa melengkapi data profil.<br /><br /><br><br>
									
									Best regards,<br /><br />
									Admin KursusLes.com
									</p>
								
								</body>
								</html>";
				$session->sendEmail($email, $nama, "Selamat Bergabung di KursusLes.com", $message);
				
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
	
	//USERNAME SUDAH ADA, KIRIM RESPON
	} else {
		
		echo json_encode(array(
							"respon_code"=>"000002")
						);
		die();
		
	}

//JIKA URL KIRIMAN TIDAK VALID	
} else {
	echo "Restricted Access";
	die();
}

?>