<?php

/*
	Database function in class Db_connection :
	-	connect
	-	query
	-	exec
*/

class Db_connection{

	var $dbname		=	'devkursu_db';
	var $hostname	=	'localhost';
	var $username	=	'devkursu_arfan';
	var $password	=	'kursusles2015';
		
	//Fungsi koneksi
	function connect(){
	
		$connect 	=	mysql_connect($this->hostname, $this->username, $this->password) OR DIE('Unable to connect to database!');
		
		return $connect;
	
	}
	
	//Fungsi pengganti real escape string
	function db_text($string){
		$link 	=	mysqli_connect($this->hostname, $this->username, $this->password) OR DIE('Unable to connect to database!');
		return mysqli_real_escape_string($link, $string);
	}
	
	//Fungsi select
	function query($sql){
		
		$connect	=	$this->connect();
		mysql_select_db($this->dbname);
		
		$query		=	mysql_query($sql);
		
		if (!$query) {
			return false;
		}
		
		$result		=	array();
		
		//Jika hasil tidak kosong
		if(mysql_num_rows($query) <> 0){
		
			while ($data=	mysql_fetch_array($query, MYSQL_ASSOC)){
		
				array_push($result, $data);
	
			}
			
			return($result);

		} else {
			return false;
		}
		mysql_close($connect);
		
	}
	
	//Fungsi eksekusi SQL insert, update, delete
	function execSQL($sql = '', $type = 0){
		
		$connect	=	$this->connect();
		mysql_select_db($this->dbname);
		
		$query		=	mysql_query($sql);
		
		if (!mysql_error()){
			switch($type){
				//Get Last ID
				case "1"	:	$result	=	mysql_insert_id(); break;
				//Default - Get Affected Rows
				default		:	$result	=	mysql_affected_rows(); break;
			}
		} else {
			$result	=	'null';
		}
		
		mysql_close($connect);

		return $result;
		die();
				
	}
	
}

?>