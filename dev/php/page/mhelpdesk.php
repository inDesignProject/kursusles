<?php

include('../lib/db_connection.php');
include('../lib/enkripsi.php');
include('../lib/session.php');
require "../lib/defines.php";

$db			=	new	Db_connection();
$enkripsi	=	new Enkripsi();
$session	=	new Session();

	session_start();
	
	$sql = '';
	$sql .= 'select idmurid from m_murid where iduser ='.$_SESSION['KursusLes']['IDPRIME'];
	
	$ismurid	=	$db->query($sql);
	
	
	$idmurid	=	$_GET['q'] == "" && !isset($_GET['q']) ? $ismurid[0]['idmurid'] : $enkripsi->decode($_GET['q']) ;
	
	//FUNGSI DIGUNAKAN DETAIL DATA
	if( $enkripsi->decode($_GET['func']) == "detailData" && isset($_GET['func'])){
		
		$idhelpdesk	=	$enkripsi->decode($_POST['iddata']);
		$sqlsel		=	sprintf("SELECT A.SUBYEK, A.PESAN, A.BALASAN, A.TGL_KIRIM, A.TGL_BALAS, B.USERNAME
								 FROM t_helpdesk A
								 LEFT JOIN admin_user B ON A.IDUSERBALAS = B.IDUSER
								 WHERE A.IDHELPDESK = %s
								 LIMIT 0,1"
								, $idhelpdesk
								);
		$result		=	$db->query($sqlsel);
		$result		=	$result[0];
		
		echo "<i class='fa fa-file-o'></i> Subyek<br/>
			  <p>".$result['SUBYEK']."</p>
			  <i class='fa fa-question-circle'></i> Pertanyaan<br/>
			  <p>".$result['PESAN']."</p>
			  Dikirim pada : <i class='fa fa-calendar'></i> ".date('d-m-Y', strtotime($result['TGL_KIRIM']))." <i class='fa fa-clock-o'></i> ".date('H:i:s', strtotime($result['TGL_KIRIM']))."<br/><br/>";
		if($result['USERNAME'] <> '' && $result['USERNAME'] <> 'null'){
			  echo	" <i class='fa fa-reply-all'></i> Balasan Admin<br/>
					  <p>".$result['BALASAN']."</p><br/>
					  <i class='fa fa-user'></i> ".$result['USERNAME']."<br/>
					  Pada : <i class='fa fa-calendar'></i> ".date('d-m-Y', strtotime($result['TGL_BALAS']))." <i class='fa fa-clock-o'></i> ".date('H:i:s', strtotime($result['TGL_BALAS']))."<br/>";
		} else {
			echo "<b>Belum ada balasan dari admin</b>";
		}
		die();		
		
	}
	
	//FUNGSI DIGUNAKAN SIMPAN DATA
	if( $enkripsi->decode($_GET['func']) == "sendToHD" && isset($_GET['func'])){

		if($_POST['subyek'] == '' || !isset($_POST['subyek'])){
			echo json_encode(array("respon_code"=>"00001", "respon_msg"=>"Harap masukkan subyek"));
			die();
		}

		if($_POST['isi'] == '' || !isset($_POST['isi'])){
			echo json_encode(array("respon_code"=>"00002", "respon_msg"=>"Harap masukkan pertanyaan yang ingin diajukan"));
			die();
		}

		$sqlIns		=	sprintf("INSERT INTO t_helpdesk
								 (IDPENGIRIM, TYPE_PENGIRIM, SUBYEK, PESAN, TGL_KIRIM)
								 VALUES
								 (%s, 2, '%s', '%s', NOW())"
								, $idmurid
								, $db->db_text($_POST['subyek'])
								, $db->db_text($_POST['isi'])
								);
		$affected	=	$db->execSQL($sqlIns, 0);
		
		if($affected > 0){
			echo json_encode(array("respon_code"=>"00000", "respon_msg"=>"Data terkirim"));
			die();
		} else {
			echo json_encode(array("respon_code"=>"00003", "respon_msg"=>"Gagal menyimpan. Silakan coba lagi nanti"));
			die();
		}
		die();
	}

	//SELECT DATA
	$sql		=	sprintf("SELECT SUBYEK, TGL_KIRIM, IDHELPDESK
							 FROM t_helpdesk 
							 WHERE IDPENGIRIM = %s AND TYPE_PENGIRIM = 2
							 ORDER BY TGL_KIRIM DESC"
							, $idmurid
							);
	$result		=	$db->query($sql);
	
	if($result <> '' && $result <> false){
		
		foreach($result as $key){
			
			$data	.=	"<tr id='row".$enkripsi->encode($key['IDHELPDESK'])."'>
							<td align='center'>".$key['TGL_KIRIM']."</td>
							<td>".$key['SUBYEK']."</td>
							<td>
								<input value='Detail' class='btn btn-sm btn-custom2' onclick='showDetail(\"".$enkripsi->encode($key['IDHELPDESK'])."\")' type='button'>
							</td>
						 </tr>";
		}
		
	} else {
		$data	=	"<tr><td colspan = '3'><center><b>Tidak ada data yang ditampilkan</b></center></td></tr>";
	}
	
?>
<style>
	.rowDetail{
		border-bottom: 3px solid #ccc;
		border-left: 3px solid #ccc;
		border-right: 3px solid #ccc;
		max-height: 300px;
		overflow:scroll;
	}
	.rowData-show{
		border-top: 3px solid #ccc;
		border-left: 3px solid #ccc;
		border-right: 3px solid #ccc;
	}
</style>
<div class="boxSquareWhite">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <h4>Help Desk</h4>
            <div class="disclaimer">
            	Hubungi admin untuk menanyakan sesuatu yang tidak anda ketahui.
            </div>
            <input type="button" name="compose" id="compose" value="Kirim Pesan" class="btn btn-sm btn-custom2 pull-right" onclick="openForm(true)">
            <div id="composeContainer" style="display:none">
                <p>
                    <label>Subyek </label><br/>
                    <span class="field">
                        <input type="text" id="subyek" name="subyek" maxlength="150" style="width: 50%" />
                    </span>
                </p>
                <p>
                    <label>Pertanyaan </label><br/>
                    <span class="field">
                        <textarea id="isi" name="isi" style="height: 100px; width:96%"></textarea>
                    </span>
                </p>
                <input type="button" name="batal" id="batal" value="Batal" class="btn btn-sm btn-custom2 pull-right" onclick="openForm(false)" style="display:none">
                <input type="button" name="kirim" id="kirim" value="Kirim" class="btn btn-sm btn-custom2 pull-right" onclick="sendToHD()" style="display:none; margin-right:5px;">
            </div>
		</div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <h4>Historis</h4>
         <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" width="100">Tanggal</th>
                    <th class="text-center">Subyek</th>
                    <th class="text-center" width="75"> </th>
                </tr>
            </thead>
            <tbody id="bodyData">
            	<?=$data?>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script>
	function sendToHD(){
		var data	=	$('#composeContainer input, #composeContainer textarea').serialize();
		$('#composeContainer input, #composeContainer textarea').prop('disabled', true);
		$('#message_response_container').slideDown('fast').html(generateMsg("Sedang menyimpan"));
		
		$.post( "<?=APP_URL?>php/page/mhelpdesk.php?func=<?=$enkripsi->encode('sendToHD')?>", data)
		.done(function( data ) {
			
			data			=	JSON.parse(data);
			if(data['respon_code'] == '00000'){
				openForm(false);
				$('#mhelpdesk').click();
			}
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			$('#composeContainer input, #composeContainer textarea').prop('disabled', false).val('');
			
		});
	}
	function openForm(status){
		if(status == true){
			$('#composeContainer').slideDown('fast');
			$('#compose').hide();
			$('#kirim').show();
			$('#batal').show();
		} else {
			$('#composeContainer').slideUp('fast');
			$('#kirim').hide();
			$('#batal').hide();
			$('#compose').show();
		}
	}
	function showDetail(value){
		
		$('.rowDetail').slideUp('fast').remove();
		$('#bodyData').find('tr').removeClass('rowData-show');
		$("#row"+value).after("<tr class='rowDetail'><td colspan ='3' id='con-"+value+"'><center><img src='<?=APP_IMG_URL?>loading.gif'/><br/>Sedang memuat</center></td></tr>").addClass('rowData-show');
		
		$.post( "<?=APP_URL?>php/page/mhelpdesk.php?func=<?=$enkripsi->encode('detailData')?>", {iddata : value})
		.done(function( data ) {
			$("#con-"+value).html(data);
		});
		
	}

</script>