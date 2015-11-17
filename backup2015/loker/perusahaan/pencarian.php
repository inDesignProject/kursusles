<?php

	include('../php/lib/db_connection.php');
	include('../php/lib/enkripsi.php');
	include('../php/lib/session.php');
	require "../php/lib/defines.php";

	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	$idpers		=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLesLoker']['IDUSER'] : $enkripsi->decode($_GET['q']) ;

	//FUNGSI ADD BOOKMARK
	if( $enkripsi->decode($_GET['func']) == "addBookmark" && isset($_GET['func'])){
		
		if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 1){
			echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
			die();
		} else {
			$idpers	=	$_SESSION['KursusLesLoker']['IDUSER'];
		}

		$idp		=	$enkripsi->decode($_POST['iddata']);
		$sqlCek		=	sprintf("SELECT IDBOOKMARK FROM t_bookmark
								 WHERE IDPEMILIK = %s AND JNSPEMILIK = 1 AND JNSBOOKMARK = 3 AND IDCHILD = %s"
								, $idpers
								, $idp
								);
		$resultCek	=	$db->query($sqlCek);
		
		if($resultCek <> false && $resultCek <> ''){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Data bookmark sudah ada sebelumnya"));
			die();
		} else {
			
			$sqlInsB=	sprintf("INSERT t_bookmark
								 (JNSPEMILIK, JNSBOOKMARK, IDPEMILIK, IDCHILD, TGLTAMBAH)
								 VALUES
								 (1, 3, %s, %s, NOW())"
								, $idpers
								, $idp
								);	
			$affected	=	$db->execSQL($sqlInsB, 0);

			//JIKA DATA SUDAH MASUK, KIRIM RESPON
			if($affected > 0){
				echo json_encode(array("respon_code"=>"00000", "respon_msg"=>""));
				die();
			} else {
				echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Gagal menambahkan data bookmark. Silakan coba lagi nanti"));
				die();
			}

		}

	}	

	//JIKA ADA KIRIMAN DATA PENCARIAN
	if($enkripsi->decode($_GET['func']) == "searchData" && isset($_GET['func'])){
		
		$dataPerPage	=	3;
		$pageCount		=	1;
		
		//CEK KONDISIONAL JENIS KELAMIN
		if(isset($_POST['jkp']) && $_POST['jkp'] <> "" && (!isset($_POST['jkw']) || $_POST['jkw'] == "")){
			$con_jk		=	"A.JK = 1";
		} else if(isset($_POST['jkw']) && $_POST['jkw'] <> "" && (!isset($_POST['jkp']) || $_POST['jkp'] == "")){
			$con_jk		=	"A.JK = 2";
		} else {
			$con_jk		=	"1=1";
		}
		//HABIS -- CEK KONDISIONAL JENIS KELAMIN

		//KONDISIONAL LAINNYA
		$conusia		=	$_POST['usia'] == "" ? "1=1" : "A.TGL_LAHIR <= '".date('Y-m-d', strtotime(date('Y-m-d').' -'.$_POST['usia'].' year'))."'";
		$conpropinsi	=	$_POST['propinsi'] == "" ? "1=1" : "A.IDPROPINSI = ".$enkripsi->decode($_POST['propinsi']);
		$conkota		=	$_POST['kota'] == "" ? "1=1" : "A.IDKOTA = ".$enkripsi->decode($_POST['kota']);
		$conbidang		=	$_POST['bidang'] == "" ? "1=1" : "A.IDBIDANG = ".$enkripsi->decode($_POST['bidang']);
		$conposisi		=	$_POST['posisi'] == "" ? "1=1" : "A.IDPOSISI = ".$enkripsi->decode($_POST['posisi']);
		$conpendidikan	=	$_POST['pendidikan'] == "" ? "1=1" : "A.IDPENDIDIKAN = ".$enkripsi->decode($_POST['pendidikan']);
		$conjurusan		=	$_POST['jurusan'] == "" ? "1=1" : "A.JURUSAN LIKE '%".$_POST['jurusan']."%'";
		
		//QUERY PENCARIAN
		$sqlSearch		=	sprintf("SELECT A.NAMA, A.ALAMAT, A.TELPON, A.EMAIL, A.FOTO, A.TGL_LAHIR, A.JK, B.NAMA_POSISI, 
											C.NAMA_BIDANG, D.NAMA_PENDIDIKAN, A.TGL_AWALKERJA, A.TGL_AKHIRKERJA, 
											A.TENTANG, A.IDBIDANG, A.IDPOSISI, A.IDPENDIDIKAN, A.JURUSAN, A.IDKARYAWAN,
    										E.NAMA_PROPINSI, F.NAMA_KOTA
									 FROM m_karyawan A
									 LEFT JOIN m_posisi B ON A.IDPOSISI = B.IDPOSISI
									 LEFT JOIN m_bidang C ON A.IDBIDANG = C.IDBIDANG
									 LEFT JOIN m_pendidikan D ON A.IDPENDIDIKAN = D.IDPENDIDIKAN
									 LEFT JOIN m_propinsi E ON A.IDPROPINSI = E.IDPROPINSI
									 LEFT JOIN m_kota F ON A.IDKOTA = F.IDKOTA
									 WHERE %s AND %s AND %s AND %s AND %s AND %s AND %s AND %s
									 ORDER BY A.NAMA"
									, $con_jk
									, $conusia
									, $conpropinsi
									, $conkota
									, $conbidang
									, $conposisi
									, $conpendidikan
									, $conjurusan
									);
		$sqlCSearch		=	sprintf("SELECT COUNT(IDKARYAWAN) AS JUMLAH_DATA FROM (%s) AS X", $sqlSearch);
		$resultCSearch	=	$db->query($sqlCSearch);
		
		if($resultCSearch <> "" && $resultCSearch <> false && $resultCSearch[0]['JUMLAH_DATA'] <> 0){ 
			$totData	=	$resultCSearch[0]['JUMLAH_DATA'];
			$pageCount	=	ceil($totData / $dataPerPage);
			$startData	=	$_POST['page'] * $dataPerPage - $dataPerPage + 1;
			$endData	=	$_POST['page'] == $pageCount ? $totData : $_POST['page'] * $dataPerPage;
		} else {
			$totData	=	0;
			$pageCount	=	0;
			$startData	=	0;
			$endData	=	0;
		}
		
		$sqlSearch		=	sprintf("SELECT * FROM (%s) AS A LIMIT %s, %s", $sqlSearch, ($startData -1), $dataPerPage);
		$resultSearch	=	$db->query($sqlSearch);
		
		if($resultSearch <> "" && $resultSearch <> false){
			
			foreach($resultSearch as $key){
				
				switch($key['JK']){
					case "L"	:	$jeniskelamin	=	"Laki-Laki"; break;
					case "P"	:	$jeniskelamin	=	"Perempuan"; break;
					default		:	$jeniskelamin	=	"Tidak Diketahui"; break;
				}
				$idkaryawan	=	$enkripsi->encode($key['IDKARYAWAN']);
				
				$dataresult	.=	"<div class='boxSquareWhite' style='padding: 1%'>
                                  <div class='row'>
									<div class='col-lg-2 col-md-2 col-sm-2 col-xs-12' style='text-align: center;'>
										<a href='".APP_URL."profil_kary?q=".$enkripsi->encode($key['IDKARYAWAN'])."'>
											<img src='".APP_IMG_URL."generate_pic.php?type=kr&q=".$enkripsi->encode($key['FOTO'])."&w=90&h=90' class='img-responsive img-circle img-profile' style='margin-left:auto; margin-right:auto'>
										</a>
									</div>
									<div class='col-lg-10 col-md-10 col-sm-10 col-xs-12'>
									  <h4><a href='".APP_URL."profil_kary?q=".$enkripsi->encode($key['IDKARYAWAN'])."'>".$key['NAMA']."</a></h4>
									  <div class='info'>
										<div class='infolist'>
										  <i class='fa fa-home'></i> ".$key['ALAMAT']." ".$key['NAMA_KOTA']." ".$key['NAMA_PROPINSI']."<br/>
										  <i class='fa fa-envelope-o'></i> ".$key['EMAIL']."<br/>
										  <i class='fa fa-phone'></i> ".$key['TELPON']."<br/>
										  <i class='fa fa-user'></i> ".$jeniskelamin."<br/>
										  <i class='fa fa-calendar'></i> ".$key['TGL_LAHIR']."<br/>
										  <i class='fa fa-graduation-cap'></i> ".$key['NAMA_PENDIDIKAN']." ".$key['JURUSAN']."<br/>
										  <i class='fa fa-building'></i> ".$key['NAMA_BIDANG']." ".$key['NAMA_POSISI']."<br/>
										</div>
										<a class='btn btn-custom btn-xs pull-right' onclick='addBookmark(\"".$idkaryawan."\")' style='padding: 4px'>
											<i class='fa fa-bookmark'></i> Bookmark
										</a>										
									  </div>
									</div>
                                </div>";
			}
		} else {
			$dataresult	=	"<tr>
								<td><center><b>Tidak ada data yang ditemukan</b></center></td>
							</tr>";
		}
		
		$pageData		=	"<div id='ketData'>
								<div style='float:left'>
									<small><b>Menampilkan data ke ".$startData." sampai ".$endData."</b></small>
								</div>
								<div style='float:right'>
									<small><b>".$totData." Data ditemukan.</b></small>
								</div>
							 </div>";
		
		//PAGING SEARCH RESULT
		for($x=1; $x<=$pageCount; $x++){
			$pageButtonList	.=	$x.",";
		}
		
		if($_POST['page'] == 1 && $pageCount > 1){
			if($pageCount == 2){
				$pageButtonList	=	$pageButtonList."next,";
			} else {
				$pageButtonList	=	$pageButtonList."next,last,";
			}
		} else if($_POST['page'] > 1 && $pageCount > 2 && $_POST['page'] <> $pageCount){
			$pageButtonList	=	"first,prev,".$pageButtonList."next,last,";
		} else if($totData <> 0 && $pageCount > 1) {
			if($pageCount == 2){
				$pageButtonList	=	"prev,".$pageButtonList;
			} else {
				$pageButtonList	=	"first,prev,".$pageButtonList;
			}
		} else if($pageCount == 1){
			$pageButtonList	=	"first,prev,".$pageButtonList;
		} else {
			$pageButtonList	=	"";
		}
		
		if($pageButtonList <> ""){
			$expl_pageButtonList	=	explode(",", substr($pageButtonList,0,strlen($pageButtonList)-1));
			foreach($expl_pageButtonList as $keyz){
				if($keyz == "first"){
					$numSearchData	=	1;
					$txtSearchData	=	" <i class='fa fa-angle-double-left'></i> ";
				} else if($keyz == "prev") {
					$numSearchData	=	$_POST['page'] - 1;
					$txtSearchData	=	" <i class='fa fa-angle-left'></i> ";
				} else if($keyz == "next") {
					$numSearchData	=	$_POST['page'] + 1;
					$txtSearchData	=	" <i class='fa fa-angle-right'></i> ";
				} else if($keyz == "last") {
					$numSearchData	=	$pageCount;
					$txtSearchData	=	" <i class='fa fa-angle-double-right'></i> ";
				} else {
					$numSearchData	=	$keyz;
					$txtSearchData	=	$keyz;
				}

				$active		=	$keyz == $_POST['page'] ? "class='active'" : "";				
				$pageButton	.=	"</li><li role='presentation' ".$active.">
									<a href='#' role='tab' data-toggle='tab' style='padding: 4px 10px;' onClick='applyFilter(".$numSearchData.")'>
										<b><center>".$txtSearchData."</center></b>
									</a>
								</li>";
				
			}
		} else {
			$pageButton	=	"";
		}
		//HABIS -- PAGGING SEARCH RESULT
		
		echo json_encode(array("result"=>$dataresult,
							   "pageData"=>$pageData,
							   "pageCount"=>$pageCount,
							   "pageButton"=>$pageButton
							  )
						);
		die();
	}
?>
<div class="boxSquareWhite">
    <h4>Cari Pelamar Kerja</h4><br/>
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" id="form-filter">
            <div class="form-group">
                <input name="jkp" value="1" id="jkp" type="checkbox"> Pria
                <input name="jkw" value="1" id="jkw" type="checkbox"> Wanita
            </div>
            <div class="form-group">
                <input type="text" name="usia" id="usia" class="form-control" maxlength="2" placeholder="Usia Maksimal" autocomplete="off" />
            </div>
            <div class="form-group">
                <select id="propinsi" name="propinsi" class="form-control" required onchange="getDataOpt('getDataKota','propinsi='+$('#propinsi').val(),'kota','','- Pilih Kota -')">
                    <option value="">- Pilih Propinsi -</option>
                </select>
            </div>
            <div class="form-group">
                <select id="kota" name="kota" class="form-control" required>
                    <option value="">- Pilih Kota -</option>
                </select>
            </div>
            <div class="form-group">
                <select id="bidang" name="bidang" class="form-control" required>
                    <option value="">- Pilih Bidang Pekerjaan -</option>
                </select>
            </div>
            <div class="form-group">
                <select id="posisi" name="posisi" class="form-control" required>
                    <option value="">- Pilih Posisi Pekerjaan Terakhir -</option>
                </select>
            </div>
            <div class="form-group">
                <select id="pendidikan" name="pendidikan" class="form-control" required>
                    <option value="">- Pilih Pendidikan Terakhir -</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="jurusan" id="jurusan" class="form-control" maxlength="75" placeholder="Jurusan Pendidikan" autocomplete="off" />
            </div>
            <div class="form-group">
                <input type="hidden" name="page" id="page" value="1" />
                <input type="button" value="Cari" class="btn btn-sm btn-custom2" onclick="applyFilter(1)">
			</div>
    	</div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="info">
	        <div class="panel panel-default">
                <div class="panel-heading"><b>Informasi</b></div>
                <div class="panel-body">
                    <p>
                       Isikan filter disamping kiri untuk mencari data pelamar yang anda inginkan
                    </p>
                </div>
            </div>
        </div>
	</div>
</div>
<hr />
<div class="boxSquareWhite">
    <h4>Hasil Pencarian</h4>
    <div class="row" style="margin: 5px 0;" id="pageData">
        <div id="ketData">
            <div style="float:left">
                <small><b>-</b></small>
            </div>
            <div style="float:right">
                <small><b>0 Data ditemukan.</b></small>
            </div>
        </div>
    </div>
    <nav>
        <ul class="nav nav-tabs pagination" role="tablist" style="margin: 0 auto" id="pageButton">
        </ul>
    </nav><br/>
	<div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered">
            <tbody id="bodyData">
            	<tr>
                	<td><center><b>Silakan isi data filter pencarian</b></center></td>
                </tr>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssvalidate');?>.jsfile"></script>
<script>
	getDataOpt('getDataPropinsi','noparam','propinsi','','- Pilih Propinsi -','');
	getDataOpt('getDataBidang','noparam','bidang','','- Pilih Bidang Pekerjaan -','');
	getDataOpt('getDataPosisi','noparam','posisi','','- Pilih Posisi Pekerjaan Terakhir -','');
	getDataOpt('getDataPendidikan','noparam','pendidikan','','- Pilih Pendidikan Terakhir -','');
	function generateMsg(msg){
		return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
				"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
				"<strong><small id='message_response'>"+msg+"</small></strong>"+
			"</div>";
	}
	function addBookmark(value){
		$('#message_response_container').slideUp('fast').html("");
		$.post("<?=APP_URL?>perusahaan/pencarian.php?func=<?=$enkripsi->encode('addBookmark')?>", {iddata : value})
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == "00000"){
				$('#message_response_container').slideDown('fast').html(generateMsg("Bookmark sudah ditambahkan. Cek bookmark di halaman utama pada tab Kandidat"));
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
	
		});
		
	}
	function applyFilter(type){
	
		if(type == 'default'){
			$('#page').val('1');
		} else {
			$('#page').val(type);
		}
		
		var data		=	$("#form-filter input, #form-filter select, #form-filter checkbox").serialize();
		
		$.ajax({
			beforeSend	: function(){
				$('#searchResult').slideUp('fast').html("<center><img src='<?=APP_IMAGE_URL?>loading.gif'/><br/>Sedang Memuat...</center>");
				$("#form-filter input, #form-filter select, #form-filter checkbox").prop('disabled', true);
			},
			complete	: function(){
			},
			type	: "POST",
			url		: "<?=APP_URL?>perusahaan/pencarian.php?func=<?=$enkripsi->encode('searchData')?>",
			data	: data,
			success : function(result) {
				
				data	=	JSON.parse(result);
				$('#bodyData').slideDown('fast').html(data['result']);
				$('#pageData').html(data['pageData']);
				$('#pageButton').html(data['pageButton']);
				$("#form-filter input, #form-filter select, #form-filter checkbox").prop('disabled', false);
				
			},
			error: function(){
				alert('Error saat mengirim permintaan');
			}
		});
		return true;
	
	}
</script>