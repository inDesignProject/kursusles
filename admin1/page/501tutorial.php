<?php
	include('../php/include/enkripsi.php');
	include('../php/include/session.php');
	include('../php/lib/db_connection.php');
	require "../php/include/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	if($session->cekSession() <> 2 && !isset($_GET['func'])){
		 echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode($session->cekSession())."'</script>";
		 die();
	}
	
	//FUNGSI DIGUNAKAN TAMBAH DATA
	if( $enkripsi->decode($_GET['func']) == "addData" && isset($_GET['func'])){
		
		//CEK KATEGORI
		if($_POST['kategori'] == '' || !isset($_POST['kategori'])){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Harap pilih kategori"));
			die();
		}
		
		$kategori		=	$enkripsi->decode($_POST['kategori']);
		$judul			=	str_replace("'","",$_POST['judul']);
		$isi			=	str_replace("'","",$_POST['isi']);

		//CEK JUDUL
		if($_POST['judul'] == '' || !isset($_POST['judul'])){
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Harap isi judul tutorial"));
			die();
		}
		
		//CEK ISI
		if($_POST['isi'] == '' || !isset($_POST['isi'])){
			echo json_encode(array("respon_code"=>"00003", "respon_msg"=>"Harap isi konten tutorial"));
			die();
		}
		
		$sqlIns			=	sprintf("INSERT INTO t_tutorial
									 (IDKATEGORI,IDUSERPOSTING,JUDUL,ISI,TGL_POSTING,STATUS)
									 VALUES
									 ('%s','%s','%s','%s',NOW(),'1')"
									, $kategori
									, $_SESSION['KursusLesAdmin']['IDUSER']
									, $judul
									, $isi
									);
		$lastID			=	$db->execSQL($sqlIns, 1);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($lastID > 0){
			$body	=	"	<div style='padding: 8px'>
								<div class='boxSquareWhite'>
									<a href='#'><span class='tutor_name'>".$judul."</span></a>
									<a href='#' id='nonA".$enkripsi->encode($lastID)."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($lastID)."\")'>
										Non-Aktifkan
									</a><br>
									<small class='tutorial_detail'><i class='fa fa-calendar'></i> ".date('Y-m-d')." | <i class='fa fa-user'></i> ".$_SESSION['KursusLesAdmin']['USERNAME']."</small>
									<p id='isi".$enkripsi->encode($lastID)."'>
										".substr($isi,0,300)."
									</p><br/>
									<a href='#' id='a".$enkripsi->encode($lastID)."' onclick='showDetail(\"".$enkripsi->encode($lastID)."\")'><b>Baca selengkapnya...</b></a>
								</div>
							</div>";
			echo json_encode(array("respon_code"=>'00000', 'respon_msg'=>'Data tersimpan', 'respon_body'=>$body));
			die();
		} else {
			echo json_encode(array("respon_code"=>'00004', 'respon_msg'=>'Gagal menyimpan data'));
			die();
		}
		
	}
	
	//FUNGSI DIGUNAKAN TAMBAH KATEGORI
	if( $enkripsi->decode($_GET['func']) == "addKategori" && isset($_GET['func'])){
		$sqlInsK	=	sprintf("INSERT IGNORE INTO m_tutorial_kategori SET NAMA_KATEGORI = '%s', STATUS = 1"
								, $_POST['kategori']
								);
		$db->execSQL($sqlInsK, 0);
		die();
	}
	
	//FUNGSI DIGUNAKAN LIHAT DATA DETAIL
	if( $enkripsi->decode($_GET['func']) == "getDetail" && isset($_GET['func'])){
		$idtutorial		=	$enkripsi->decode($_POST['iddata']);
		$sqlDetail		=	sprintf("SELECT ISI
									 FROM t_tutorial
									 WHERE IDTUTORIAL = %s"
									, $idtutorial
									);
		$resultDetail	=	$db->query($sqlDetail);
		echo $resultDetail[0]['ISI'];
		die();
	}
	
	//FUNGSI DIGUNAKAN AKTIFKAN / NON AKTIFKAN DATA
	if( $enkripsi->decode($_GET['func']) == "nonAktif" && isset($_GET['func'])){
		
		$idtutorial		=	$enkripsi->decode($_POST['iddata']);
		$sqlCek			=	sprintf("SELECT STATUS
									 FROM t_tutorial
									 WHERE IDTUTORIAL = %s"
									, $idtutorial
									);
		$resultCek		=	$db->query($sqlCek);
		$status			=	$resultCek[0]['STATUS'];
		
		if($status == 1){
			$respon_code	=	"00000";
			$updStatus		=	"0";
		} else {
			$respon_code	=	"00001";
			$updStatus		=	"1";
		}
		
		$sqlUpd			=	sprintf("UPDATE t_tutorial SET STATUS = %s
									 WHERE IDTUTORIAL = %s"
									, $updStatus
									, $idtutorial
									);
		$affected		=	$db->execSQL($sqlUpd, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected <= 0){
			$respon_code=	"00003";
			$respon_msg	=	"Gagal mengubah data";
		}
		
		echo json_encode(array("respon_code"=>$respon_code, "respon_msg"=>$respon_msg));
		
		die();
	}

	//FUNGSI DIGUNAKAN FILTER DATA
	if( $enkripsi->decode($_GET['func']) == "filterData" && isset($_GET['func'])){
		
		if(isset($_POST['status']) && $_POST['status'] <> ''){
			$conStatus	=	$_POST['status'] == 2 ? "1=1" : "A.STATUS = ".$_POST['status'];
		} else {
			$conStatus	=	"1=1";
		}
		
		if(isset($_POST['kategori']) && $_POST['kategori'] <> ''){
			$idkategori	=	$enkripsi->decode($_POST['kategori']);
			$conKategori=	"IDKATEGORI = ".$idkategori;			
		} else {
			$conKategori=	"1=1";
		}
		
		//SELECT DATA TUTORIAL
		$sql		=	sprintf("SELECT A.IDTUTORIAL, A.JUDUL, A.TGL_POSTING, B.USERNAME, LEFT(A.ISI, 300) AS ISI, A.STATUS
								 FROM t_tutorial A
								 LEFT JOIN m_user B ON A.IDUSERPOSTING = B.IDUSER
								 WHERE %s AND %s
								 ORDER BY A.STATUS DESC"
								, $conStatus
								, $conKategori
								);
		$result		=	$db->query($sql);
		$totData	=	0;
		$data		=	'';
		
		if($result <> '' && $result <> false){
			foreach($result as $key){
				$status	=	$key['STATUS'] == "1" ? "Non Aktifkan" : "Aktifkan";
				$data	.=	"	<div style='padding: 8px'>
									<div class='boxSquareWhite'>
										<a href='#'><span class='tutor_name'>".$key['JUDUL']."</span></a>
										<a href='#' id='nonA".$enkripsi->encode($key['IDTUTORIAL'])."' class='btn btn-kursusles btn-sm pull-right' style='padding: 3px;' onclick='nonAktif(\"".$enkripsi->encode($key['IDTUTORIAL'])."\")'>
                                            ".$status."
                                        </a><br>
										<small class='tutorial_detail'><i class='fa fa-calendar'></i> ".date('Y-m-d',strtotime($key['TGL_POSTING']))." | <i class='fa fa-user'></i> ".$key['USERNAME']."</small>
										<p id='isi".$enkripsi->encode($key['IDTUTORIAL'])."'>
											".$key['ISI']."
										</p><br/>
										<a href='#' id='a".$enkripsi->encode($key['IDTUTORIAL'])."' onclick='showDetail(\"".$enkripsi->encode($key['IDTUTORIAL'])."\")'><b>Baca selengkapnya...</b></a>
									</div>
								</div>";
				$totData++;
			}
		} else {
			$data	=	"	<div class='boxSquareWhite'>
								<center><b>Tidak ada data yang ditemukan</b></center>
							</div>";
		}
		
		echo json_encode(array("totData"=>$totData, "respon"=>$data));
		die();
	}
?>
<style>
	@import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141218'.'csstinyeditor');?>.cssfile");
</style>
<div class="pageheader notab">
    <ul class="breadcrumbs breadcrumbs2">
        <li><a href=""><i class="fa fa-lg fa-pencil"></i> Posting</a></li>
        <li>Tutorial</li>
    </ul>
    <br clear="all" />
    <h1 class="pagetitle">Posting Data Tutorial</h1>
</div>
<div id="contentwrapper" class="elements">
	<form method="post" enctype="multipart/form-data" class="stdform" action="<?=APP_URL?>501tutorial?func=<?=$enkripsi->encode('addTutorial')?>">
        <div id="divfilter">
            <p>
                <label>Status</label>
                <span class="field">
                    <input name="status" value="2" id="status1" onclick="filterData()" checked type="radio"> Semua &nbsp; 
                    <input name="status" value="1" id="status1" onclick="filterData()" type="radio"> Aktif &nbsp; 
                    <input name="status" value="0" id="status2" onclick="filterData()" type="radio"> Tidak Aktif
                </span>
            </p>
            <p>
                <label>Kategori</label>
                <span class="field">
                	<select id="kategori" name="kategori" onchange="filterData()" onfocus="getDataOpt('getDataKategoriTutor','noparam','kategori','','-Pilih Kategori-');">
                    	<option value="">-Pilih Kategori-</option>
                    </select>
                </span>
            </p>
        </div>
        <div id="divposting" style="display:none">
            <p>
                <label>Kategori</label>
                <span class="field">
                	<select id="post-kategori" name="post-kategori"
                    	onfocus="getDataOpt('getDataKategoriTutor','noparam','post-kategori','','- Pilih Kategori -');">
                    	<option value="">- Pilih Kategori -</option>
                    </select>
				</span>
			</p>
            <p>
                <label>Judul</label>
                <span class="field">
					<input type="text" id="post-judul" name="post-judul" maxlength="150" />
				</span>
			</p>
            <p>
                <label>Isi</label>
                <span class="field">
					<textarea id="post-isi" name="post-isi" style="height: 100px;"></textarea>
				</span>
			</p>
		</div>
        <div class="actionBar">
            <span id="showfilter" class="pull-left"></span>
            <input name="posting" id="posting" class="submit radius2 pull-right" value="Posting Tutorial" type="button" onclick="showComposer(true)">
            <input name="simpanposting" id="simpanposting" class="submit radius2 pull-right" value="Simpan" type="button" onclick="submitPosting()" style="display:none">
            <input name="batalposting" id="batalposting" class="reset radius2 pull-right" value="Batal" type="reset" onclick="showComposer(false)" style="display:none; margin-right:5px">
            <input name="tambahkat" id="tambahkat" class="submit radius2 pull-right" value="Tambah Kategori Baru" type="button" onclick="addKategori()" style="display:none; margin-right:5px">
        </div>
	</form>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="data-con">
            </div>
        </div>
    </div>
    <div id="dialog-confirm">
      <p id="text_dialog"></p>
    </div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jsstinyeditor');?>.jsfile"></script>
<script>
	function nonAktif(id){
		$.post( "<?=APP_URL?>page/501tutorial.php?func=<?=$enkripsi->encode('nonAktif')?>", {iddata: id})
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				$('#showfilter').html("1 Data Tutorial Dinon-aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Aktifkan');
			} else if(data['respon_code'] == '00001'){
				$('#showfilter').html("1 Data Tutorial Di Aktifkan");
				$('#message_response_container').slideDown('fast').html(generateMsg("Perubahan data disimpan"));
				$('#nonA'+id).html('Non Aktifkan');
			} else {
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			
		});
	}
	
	function addKategori(){
		$("#dialog-confirm").dialog({
			closeOnEscape: false,
			resizable: true,
			modal: true,
			minWidth: 500,
			title: "Tambah Kategori",
			position: {
				my: 'top', 
				at: 'top'
			},
			open: function() {
			  $(this).html("<br/>Nama Kategori : <input id='kategoribaru' name='kategoribaru' maxlength='150' type='text'>");
			},
			close: function() {
				$(this).dialog("close");
			},
			buttons: {
				"Simpan": function() {
					var kategori	=	document.getElementById("kategoribaru").value;
					$.post( "<?=APP_URL?>page/501tutorial.php?func=<?=$enkripsi->encode('addKategori')?>", {kategori:kategori});
					$(this).dialog("close");
					getDataOpt('getDataKategoriTutor','noparam','post-kategori','','- Pilih Kategori -');
				},
				"Batal": function() {
					$(this).dialog( "close" );
				}
			}
		});	
	}
	
	function submitPosting(){

		var kategori	= $('#post-kategori').val();
			judul		= $('#post-judul').val();
			isi 		= $('#post-isi').val();
		
		$("#divposting input, #divposting textarea, #divposting select").prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan...'));
		
		$.post( "<?=APP_URL?>page/501tutorial.php?func=<?=$enkripsi->encode('addData')?>", {kategori:kategori, judul:judul, isi:isi})
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] != '00000'){
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			} else {
				$('#post-kategori').val('');
				$('#post-judul').val('');
				$('#post-isi').val('');
				$('#data-con').prepend(data['respon_body']);
				$('#showfilter').html("1 Data Ditambahkan");
				showComposer(false);
				$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			}
			$("#divposting input, #divposting textarea, #divposting select").prop('disabled', false);
			
		});
		
	}
	
	function filterData(){
		var status	=	$('input[name=status]:checked', '#divfilter').val();
			kategori=	$('#kategori').val();

		$('#data-con').html("<center style='margin-top: 50px;'><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center>");
		$.post( "<?=APP_URL?>page/501tutorial.php?func=<?=$enkripsi->encode('filterData')?>", {status: status, kategori: kategori})
		.done(function( data ) {
			
			data	=	JSON.parse(data);
			$('#data-con').html(data['respon']);
			$('#showfilter').html(data['totData']+" Data Tutorial Ditemukan");
			
		});
	}
	
	function showDetail(id){
		$.post( "<?=APP_URL?>page/501tutorial.php?func=<?=$enkripsi->encode('getDetail')?>", {iddata: id})
		.done(function( data ) {
			
			$('#isi'+id).html(data);
			$('#a'+id).remove();
			
		});
	}
	
	function showComposer(status){
		if(status == true){
			$('#divfilter').slideUp('fast');
			$('#divposting').slideDown('fast');
			$('#posting').hide();
			$('#tambahkat').show();
			$('#simpanposting').show();
			$('#batalposting').show();
		} else {
			$('#divposting').slideUp('fast');
			$('#divfilter').slideDown('fast');
			$('#simpanposting').hide();
			$('#tambahkat').hide();
			$('#batalposting').hide();
			$('#posting').show();
		}
	}
	
	$(document).ready(function(){
		filterData();
	});
	
</script>