<?php

include('db_connection.php');
include('enkripsi.php');
require "defines.php";

if(function_exists($_GET['f'])) {
   $_GET['f']();
} else {
	header("HTTP/1.1 404 Not Found");
}

//Respon data jenjang by ajax
function getDataJenjang(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDJENJANG, NAMA_JENJANG FROM m_jenjang ORDER BY IDJENJANG");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDJENJANG']), $key['NAMA_JENJANG']));
			
			}

		} else {
	
			array_push($data, array('','Data tidak ditemukan'));
	
		}

	} else {

		array_push($data, array('','Data tidak ditemukan'));

	}

	echo json_encode($data);
	die();
		
}

//Respon data jenis usaha by ajax
function getDataJenisUsaha(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDUSAHA, NAMA_USAHA FROM m_jenis_usaha ORDER BY NAMA_USAHA");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDUSAHA']), $key['NAMA_USAHA']));
			
			}

		} else {
	
			array_push($data, array('','Data tidak ditemukan'));
	
		}

	} else {

		array_push($data, array('','Data tidak ditemukan'));

	}

	echo json_encode($data);
	die();
		
}

//Respon data bidang kerja by ajax
function getDataBidang(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDBIDANG, NAMA_BIDANG FROM m_bidang ORDER BY NAMA_BIDANG");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				if(!isset($_GET['type'])){
					array_push($data, array($enkripsi->encode($key['IDBIDANG']), $key['NAMA_BIDANG']));
				} else {
					array_push($data, $key['NAMA_BIDANG']);
				}
			
			}

		} else {
	
			if(!isset($_GET['type'])){
				array_push($data, array('', '- Data tidak ditemukan -'));
			} else {
				array_push($data, 'Data tidak ditemukan');
			}
	
		}

	} else {

		if(!isset($_GET['type'])){
			array_push($data, array('', '- Data tidak ditemukan -'));
		} else {
			array_push($data, 'Data tidak ditemukan');
		}

	}

	echo json_encode($data);
	die();
		
}

//Respon data posisi by ajax, no param
function getDataPosisi(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql			=	sprintf("SELECT IDPOSISI, NAMA_POSISI FROM m_posisi ORDER BY NAMA_POSISI");
	$result			=	$db->query($sql);
	$data			=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				if(!isset($_GET['type'])){
					array_push($data, array($enkripsi->encode($key['IDPOSISI']), $key['NAMA_POSISI']));
				} else {
					array_push($data, $key['NAMA_POSISI']);
				}
			
			}

		} else {
			
			if(!isset($_GET['type'])){
				array_push($data, array('', '- Data tidak ditemukan -'));
			} else {
				array_push($data, 'Data tidak ditemukan');
			}
			
		}

	} else {

		if(!isset($_GET['type'])){
			array_push($data, array('', '- Data tidak ditemukan -'));
		} else {
			array_push($data, 'Data tidak ditemukan');
		}

	}

	echo json_encode($data);
	die();
		
}

//Respon data pendidikan by ajax
function getDataPendidikan(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDPENDIDIKAN, NAMA_PENDIDIKAN FROM m_pendidikan ORDER BY NAMA_PENDIDIKAN");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				if(!isset($_GET['type'])){
					array_push($data, array($enkripsi->encode($key['IDPENDIDIKAN']), $key['NAMA_PENDIDIKAN']));
				} else {
					array_push($data, $key['NAMA_PENDIDIKAN']);
				}
			
			}

		} else {
	
			if(!isset($_GET['type'])){
				array_push($data, array('', '- Data tidak ditemukan -'));
			} else {
				array_push($data, 'Data tidak ditemukan');
			}
	
		}

	} else {

		if(!isset($_GET['type'])){
			array_push($data, array('', '- Data tidak ditemukan -'));
		} else {
			array_push($data, 'Data tidak ditemukan');
		}

	}

	echo json_encode($data);
	die();
		
}

//Respon data propinsi by ajax
function getDataPropinsi(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDPROPINSI, NAMA_PROPINSI FROM m_propinsi ORDER BY NAMA_PROPINSI");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				if(!isset($_GET['type'])){
					array_push($data, array($enkripsi->encode($key['IDPROPINSI']), $key['NAMA_PROPINSI']));
				} else {
					array_push($data, $key['NAMA_PROPINSI']);
				}
			}

		} else {
	
			if(!isset($_GET['type'])){
				array_push($data, array('', '- Data tidak ditemukan -'));
			} else {
				array_push($data, 'Data tidak ditemukan');
			}
	
		}

	} else {

		if(!isset($_GET['type'])){
			array_push($data, array('', '- Data tidak ditemukan -'));
		} else {
			array_push($data, 'Data tidak ditemukan');
		}

	}

	echo json_encode($data);
	die();
		
}

//Respon data kota by ajax, param idpropinsi
function getDataKota(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	//Kondisional param idpropinsi
	$idpropinsi 	=	$_GET['propinsi'];
	$conPropinsi	=	$idpropinsi == '' ? "1=1" : " IDPROPINSI = '".$enkripsi->decode($idpropinsi)."' ";
	
	//Kondisional param nama kota
	$nama_kota 		=	$_GET['namakota'];
	$nama_kota		=	str_split($nama_kota);
	$key_condition	=	"";
	foreach($nama_kota as $key){
		$key_condition	.=	$key."%";
	}
	$key_condition	=	"%".$key_condition;
	$conNamaKota	=	$nama_kota == '' ? "1=1" : " NAMA_KOTA LIKE '".$key_condition."' ";

	//Kondisional limit -- tidak terpakai (no limit)
	$conLimit		=	$nama_kota == '' && $idpropinsi <> '' ? " " : " LIMIT 0,10 ";

	$sql			=	sprintf("SELECT IDKOTA, NAMA_KOTA FROM m_kota WHERE %s AND %s ORDER BY NAMA_KOTA"
								, $conPropinsi
								, $conNamaKota
								);
	$result			=	$db->query($sql);
	$data			=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				if(isset($_GET['propinsi'])){
					array_push($data, array($enkripsi->encode($key['IDKOTA']), $key['NAMA_KOTA']));
				} else {
					array_push($data, $key['NAMA_KOTA']);
				}
			
			}

		} else {
	
			if(isset($_GET['propinsi'])){
				array_push($data, array('', '- Data tidak ditemukan -'));
			} else {
				array_push($data, 'Data tidak ditemukan');
			}
	
		}

	} else {

		if(isset($_GET['propinsi'])){
			array_push($data, array('', '- Data tidak ditemukan -'));
		} else {
			array_push($data, 'Data tidak ditemukan');
		}

	}

	echo json_encode($data);
	die();
		
}

//Respon data kecamatan by ajax, param idkota
function getDataKecamatan(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	//Kondisional param idkota
	$idkota 		=	$_GET['kota'];
	$conKota		=	$idkota == '' ? "1=1" : " IDKOTA = '".$enkripsi->decode($idkota)."' ";
	
	$sql			=	sprintf("SELECT IDKECAMATAN, NAMA_KECAMATAN FROM m_kecamatan WHERE %s ORDER BY NAMA_KECAMATAN"
								, $conKota
								);
	$result			=	$db->query($sql);
	$data			=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDKECAMATAN']), $key['NAMA_KECAMATAN']));
			
			}

		} else {
	
			array_push($data, array('', '- Data tidak ditemukan -'));
	
		}

	} else {

		array_push($data, array('', '- Data tidak ditemukan -'));

	}

	echo json_encode($data);
	die();
		
}

//Respon data kelurahan by ajax, param idkecamatan
function getDataKelurahan(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	//Kondisional param idkota
	$idkecamatan	=	$_GET['kecamatan'];
	$conKecamatan	=	$idkecamatan == '' ? "1=1" : " IDKECAMATAN = '".$enkripsi->decode($idkecamatan)."' ";
	
	$sql			=	sprintf("SELECT IDKELURAHAN, NAMA_KELURAHAN FROM m_kelurahan WHERE %s ORDER BY NAMA_KELURAHAN"
								, $conKecamatan
								);
	$result			=	$db->query($sql);
	$data			=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDKELURAHAN']), $key['NAMA_KELURAHAN']));
			
			}

		} else {
	
			array_push($data, array('', '- Data tidak ditemukan -'));
	
		}

	} else {

		array_push($data, array('', '- Data tidak ditemukan -'));

	}

	echo json_encode($data);
	die();
		
}

//Respon validasi username jika sudah ada atau belum
function cekDataUsername(){
	
	$db			=	new	Db_connection();
	
	$sql		=	sprintf("SELECT IDUSER FROM m_user WHERE USERNAME = '%s' LIMIT 0,1"
							, $_GET['key']
							);
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		echo 0;

	} else {

		echo 1;

	}
	die();
		
}

//Respon data ras by ajax
function getDataRas(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDRAS, NAMA_RAS FROM m_ras ORDER BY NAMA_RAS");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDRAS']), $key['NAMA_RAS']));
			
			}

		} else {
	
			array_push($data, array('','Data tidak ditemukan'));
	
		}

	} else {

		array_push($data, array('','Data tidak ditemukan'));

	}

	echo json_encode($data);
	die();
		
}

//Respon data agama by ajax
function getDataAgama(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDAGAMA, NAMA_AGAMA FROM m_agama ORDER BY NAMA_AGAMA");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDAGAMA']), $key['NAMA_AGAMA']));
			
			}

		} else {
	
			array_push($data, array('','Data tidak ditemukan'));
	
		}

	} else {

		array_push($data, array('','Data tidak ditemukan'));

	}

	echo json_encode($data);
	die();
		
}

?>