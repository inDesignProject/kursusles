<?php

include('db_connection.php');
include('../include/enkripsi.php');
require "../include/defines.php";

if(function_exists($_GET['f'])) {
   $_GET['f']();
} else {
	header("HTTP/1.1 404 Not Found");
}

//Respon data level admin by ajax
function getDataLevel(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDLEVEL, NAMA FROM admin_level ORDER BY NAMA");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDLEVEL']), $key['NAMA']));
			
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

//Respon data mapel by ajax, param idpropinsi
function getDataMapel(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	//Kondisional param idjenjang
	$idjenjang		=	$_GET['jenjang'];
	$conJenjang		=	$idjenjang == '' ? "1=1" : " IDJENJANG = '".$enkripsi->decode($idjenjang)."' ";
	
	$sql			=	sprintf("SELECT IDMAPEL, NAMA_MAPEL FROM m_mapel WHERE %s GROUP BY NAMA_MAPEL ORDER BY NAMA_MAPEL"
								, $conJenjang
								);
	$result			=	$db->query($sql);
	$data			=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDMAPEL']), $key['NAMA_MAPEL']));
			
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
	
				array_push($data, array($enkripsi->encode($key['IDPROPINSI']), $key['NAMA_PROPINSI']));
			
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

//Respon data bank by ajax
function getDataBank(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDBANK, NAMA_BANK FROM m_bank WHERE STATUS = 1 ORDER BY NAMA_BANK");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDBANK']), $key['NAMA_BANK']));
			
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

//Respon data kategori tutorial by ajax
function getDataKategoriTutor(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$sql		=	sprintf("SELECT IDKATEGORI,NAMA_KATEGORI FROM m_tutorial_kategori
							 WHERE STATUS = 1
							 ORDER BY NAMA_KATEGORI");
	$result		=	$db->query($sql);
	$data		=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDKATEGORI']), $key['NAMA_KATEGORI']));
			
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

//Respon data rekening by ajax
function getDataRekeningAdmin(){
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	
	$idpengajar		=	$enkripsi->decode($_GET['idpengajar']);
	$sql			=	sprintf("SELECT A.IDREKENING, CONCAT(B.NAMA_BANK, ' - ', A.NO_REKENING, ' an. ', A.ATAS_NAMA) AS KETERANGAN
								 FROM admin_rekening A
								 LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
								 WHERE A.STATUS = 1
								 ORDER BY NAMA_BANK"
								, $idpengajar
								);
	$result			=	$db->query($sql);
	$data			=	array();
	
	if($result){

		if(count($result) > 0){

			foreach($result as $key){
	
				array_push($data, array($enkripsi->encode($key['IDREKENING']), $key['KETERANGAN']));
			
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

?>