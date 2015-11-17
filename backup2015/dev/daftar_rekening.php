<?php

	include('php/lib/db_connection.php');
	include('php/lib/enkripsi.php');
	include('php/lib/session.php');
	require "php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	//FUNGSI DIGUNAKAN UNTUK TAMBAH REKENING
	if( $enkripsi->decode($_GET['func']) == "tambahRekening" && isset($_GET['func'])){
		
		$arrField	=	array();
		$respon_code=	'';

		//CEK JIKA ADA DATA KIRIMAN YANG KOSONG
		foreach($_POST as $key => $value){
			if($value == ''){
				$respon_code	=	0;
				array_push($arrField,$key);
			} else {
				if(count($arrField) == 0){
					$respon_code	=	1;
				}
			}
		}
		
		//JIKA ADA ERROR, KIRIM RESPON JSON
		if($respon_code <> 1){
			echo json_encode(array("respon_code"=>$respon_code,
								   "respon_msg"=>"Harap isi data dengan lengkap",
								   "arrField"=>$arrField)
						    );
			die();
		}
		
		//CEK DATA REKENING
		$jenis_pemilik	=	$enkripsi->decode($_POST['jenispemilik']) == 1 ? "2" : "1";
		$sqlCek			=	sprintf("SELECT A.IDREKENING, B.NAMA_BANK, A.NO_REKENING, A.UNIT_BANK, A.CABANG_BANK, 
											A.ATAS_NAMA, B.CLASS, A.REK_UTAMA, A.STATUS
									 FROM m_rekening A
									 LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
									 WHERE A.IDUSER_CHILD	=	'%s' AND
									 	   A.JENIS_PEMILIK	=	'%s' AND
										   A.NO_REKENING	=	'%s'
									 LIMIT 0,1"
									, $enkripsi->decode($_POST['iduser'])
									, $jenis_pemilik
									, $_POST['norek']
									);
		$resultCek		=	$db->query($sqlCek);
		
		//JIKA DATA ADA
		if($resultCek <> false && $resultCek <> 'null'){
			
			$resultCek	=	$resultCek[0];
			
			if($resultCek['STATUS'] == 1){
				
				echo json_encode(array("respon_code"=>'-1',
									   "respon_msg"=>"Data rekening sudah ada",
									   "arrField"=>'')
								);
				die();
				
			} else {
				
				$sqlUpd	=	sprintf("UPDATE m_rekening
									 SET STATUS 		= 1,
									 	 IDBANK			= '%s',
									 	 CABANG_BANK	= '%s',
										 UNIT_BANK		= '%s',
										 ATAS_NAMA		= '%s'
									 WHERE IDREKENING	= %s"
								    , $enkripsi->decode($_POST['bank'])
									, $_POST['cabang']
									, $_POST['unitBank']
									, $_POST['atasnama']
									, $resultCek['IDREKENING']
									);
				$db->execSQL($sqlUpd, 0);

			}
			
		} else {
			
			$sqlIns		=	sprintf("INSERT INTO m_rekening
									(IDUSER_CHILD,JENIS_PEMILIK,IDBANK,NO_REKENING,UNIT_BANK,CABANG_BANK,ATAS_NAMA,REK_UTAMA,STATUS)
									 VALUES 
									('%s','%s','%s','%s','%s','%s','%s','1','1')"
								   , $enkripsi->decode($_POST['iduser'])
								   , $jenis_pemilik
								   , $enkripsi->decode($_POST['bank'])
								   , $_POST['norek']
								   , $_POST['unitBank']
								   , $_POST['cabang']
								   , $_POST['atasnama']
								   );
			$db->execSQL($sqlIns, 0);
		}

		echo json_encode(array("respon_code"=>'1',
							   "respon_msg"=>"Data rekening disimpan"
							   )
						);
		die();

	}
	
	//FUNGSI DIGUNAKAN UNTUK HAPUS REKENING
	if( $enkripsi->decode($_GET['func']) == "hapusRekening" && isset($_GET['func'])){

		//AMBIL DATA POST
		$idrekening	=	$enkripsi->decode($_POST['idrekening']);
		
		//UPDATE STATUS REKENING
		$sqlUpd		=	sprintf("UPDATE m_rekening SET STATUS = 0 WHERE IDREKENING = %s"
								, $idrekening
								);
		$affected	=	$db->execSQL($sqlUpd, 0);

		//JIKA DATA SUDAH MASUK, KIRIM RESPON
		if($affected > 0){
			echo json_encode(array("respon_code"=>$respon_code));
		//JIKA GAGAL INSERT
		} else {
			echo json_encode(array("respon_code"=>'0'));
		}
		die();
		
	}
	
	if($session->cekSession() == 1){
		
		header("Location: login?authResult=".$enkripsi->encode('3')."&t=2");
		die();
		
	} else if($session->cekSession() == 2){
		
		$sql	=	sprintf("SELECT A.IDREKENING, B.NAMA_BANK, A.NO_REKENING, A.UNIT_BANK, A.CABANG_BANK, 
									A.ATAS_NAMA, B.CLASS, A.REK_UTAMA
							 FROM m_rekening A
							 LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
							 WHERE A.IDUSER_CHILD = %s AND A.JENIS_PEMILIK = 1 AND A.STATUS = 1
							 ORDER BY A.REK_UTAMA"
							, $_SESSION['KursusLes']['IDUSER']
							);
		$result		=	$db->query($sql);
		
		header('Content-type: text/html; charset=utf-8');
		
?>
<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<title>TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!</title>
		<meta name="description" content="TUNTUTLAH ILMU SAMPAI KE NEGERI MAYA 24 JAM SEHARI!">
		<meta name="author" content="inDesign Project">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssmain');?>.cssfile">
		<link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssanimate');?>.cssfile">
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile" />
        <link rel="stylesheet" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssdaftarrekening');?>.cssfile" />
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>

    <body>

    	<?=$session->getTemplate('header')?>

        <div class="container">
        	<h3 class="text-left text_kursusles page-header">DAFTAR REKENING BANK</h3>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Tambahkan Rekening</b></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <div class="inputDesc">Nama Bank</div>
                                    <span> : </span>
                                    <select id="bank" name="bank" class="form-control"></select>
                                    <div id="msg_bank" style="display:inline"></div>
                                </div>
                                <div class="form-group">
                                    <div class="inputDesc">Cabang</div>
                                    <span> : </span>
                                    <input type="text" id="cabang" name="cabang" maxlength="75" value="" class="form-control"/>
                                    <div id="msg_cabang" style="display:inline"></div>
                                </div>
                                <div class="form-group">
                                    <div class="inputDesc">Unit</div>
                                    <span> : </span>
                                    <input type="text" id="unitBank" name="unitBank" maxlength="75" value="" class="form-control"/>
                                    <div id="msg_unitBank" style="display:inline"></div>
                                </div>
                                <div class="form-group">
                                    <div class="inputDesc">No. Rekening</div>
                                    <span> : </span>
                                    <input type="text" id="norek" name="norek" maxlength="75" value="" class="form-control"/>
                                    <div id="msg_norek" style="display:inline"></div>
                                </div>
                                <div class="form-group">
                                    <div class="inputDesc">Atas Nama</div>
                                    <span> : </span>
                                    <input type="text" id="atasnama" name="atasnama" maxlength="75" value="" class="form-control"/>
                                    <div id="msg_atasnama" style="display:inline"></div>
                                </div>
                                <div class="form-group">
                                    <div id="msg_tambah" class="hide">Harap tunggu, sedang menyimpan...</div>
                                    <input type="button" name="submit" id="submit" value="Tambahkan" onclick="tambahRekening()" style="float:right" class="btn btn-sm btn-custom2"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="boxSquare">
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>Daftar Rekening</b></div>
                            <div class="panel-body">
                                <div id="msg_process" class="hide">Harap tunggu, sedang menghapus...</div>
                                <table id="standart_table" border="0" cellspacing="0">
                                    <thead>
                                        <tr align="center">
                                            <td> </td>
                                            <td>Nama Bank</td>
                                            <td>Cabang</td>
                                            <td>No Rekening</td>
                                            <td>Atas Nama</td>
                                            <td>Rek. Utama</td>
                                            <td> </td>
                                        </tr>
                                    </thead>
                                    <tbody id="list_rekening_container">
                                    <?php
                                    $i	=	0;
                                    if($result <> false && $result <> 'null'){
                                        foreach($result as $key){
                                            $modulus		=	$i%2;
                                            $class_seling	=	$modulus == 0 ? "seling" : "normal";
                                            $class_utama	=	$key['REK_UTAMA'] == 1 ? "avilable" : "";
                                    ?>
                                            <tr class="<?=$class_seling?> list_rekening" id="baris<?=$enkripsi->encode($key['IDREKENING'])?>">
                                                <td class="<?=$key['CLASS']?>"></td>                        
                                                <td><?=$key['NAMA_BANK']?></td>
                                                <td><?=$key['CABANG_BANK']." - ".$key['UNIT_BANK']?></td>
                                                <td><?=$key['NO_REKENING']?></td>
                                                <td><?=$key['ATAS_NAMA']?></td>
                                                <td class="<?=$class_utama?>"></td>
                                                <td>
                                                	<center>
                                                    	<input type="button" name="hapus" class="btn btn-sm btn-custom2 btn_hapus" id="hapus<?=$enkripsi->encode($key['IDREKENING'])?>" value="Hapus" onclick="hapusRekening('<?=$enkripsi->encode($key['IDREKENING'])?>', this.id);" />
                                                    </center>
                                                </td>
                                            </tr>
                                    <?php
                                            $i++;
                                        }
                                    } else {
                                        echo "<tr class=''><td colspan='7'><center>- Tidak ada data -</center></td></tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

		<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>
        <script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
        
        <script>
            $(document).ready(function() {
                getDataOpt('getDataBank','noparam','bank','','- Pilih Bank -');
            });
            
            function tambahRekening(){
                
                $('#msg_tambah').removeClass('hide').addClass('show msg_loading').html("Harap tunggu, sedang menyimpan...");
                $("#bank, #cabang, #unitBank, #norek, #atasnama").prop('disabled', true);
                
                $.post( "<?=APP_URL?>daftar_rekening.php?func=<?=$enkripsi->encode('tambahRekening')?>",
                        {
                            bank: $("#bank").val(),
                            cabang: $("#cabang").val(),
                            unitBank: $("#unitBank").val(),
                            norek: $("#norek").val(),
                            atasnama: $("#atasnama").val(),
                            iduser: '<?=$enkripsi->encode($_SESSION['KursusLes']['IDUSER'])?>',
                            jenispemilik: '<?=$enkripsi->encode($_SESSION['KursusLes']['TYPEUSER'])?>'
                        }
                )
                .done(function( data ) {
                    data			=	JSON.parse(data);
        
                    $('#msg_tambah').removeClass('show msg_loading').addClass('hide').html("");
                    $("#bank, #cabang, #unitBank, #norek, #atasnama").prop('disabled', false);
        
                    if(data['respon_code'] == '0'){
                        if($.inArray('bank',data['arrField']) > -1){$("#msg_bank").html("Bank wajib diisi"); $('#bank').focus();};
                        if($.inArray('cabang',data['arrField']) > -1){$("#msg_cabang").html("Cabang bank wajib diisi"); $('#cabang').focus();};
                        if($.inArray('unitBank',data['arrField']) > -1){$("#msg_unitBank").html("Unit bank wajib diisi"); $('#unitBank').focus();};
                        if($.inArray('norek',data['arrField']) > -1){$("#msg_norek").html("Nomor Rekening wajib diisi"); $('#norek').focus();};
                        if($.inArray('atasnama',data['arrField']) > -1){$("#msg_atasnama").html("Atas Nama wajib diisi"); $('#atasnama').focus();};
                    } else if(data['respon_code'] == '-1'){
                        $('#msg_tambah').removeClass('hide msg_loading').addClass('show').html("Data sudah ada, silakan masukkan nomor rekening lain");
                    } else {
                        window.location.reload();
                    }
                });
            }
            
            function hapusRekening(id, idTable){
                
                $('#msg_process').removeClass('hide').addClass('show msg_loading').html("Harap tunggu, sedang menghapus...");
                $(".btn_hapus").prop('disabled', true);
                
                $.post( "<?=APP_URL?>daftar_rekening.php?func=<?=$enkripsi->encode('hapusRekening')?>",
                        {idrekening: id}
                )
                .done(function( data ) {
                    data			=	JSON.parse(data);
                    $('#msg_process').removeClass('show msg_loading').addClass('hide').html("");
                    
                    if(data['respon_code'] == '0'){
                        $(".btn_hapus").prop('disabled', false);
                        $('#msg_process').removeClass('hide msg_loading').addClass('show').html("Gagal menghapus rekening. Silakan coba lagi");
                    } else {
                        $(".btn_hapus").prop('disabled', false);
                        $('#baris'+id).remove();
                        
                        if($('.list_rekening').length == 0){
                            $('#list_rekening_container').prepend("<tr class=''><td colspan='7'><center>- Tidak ada data -</center></td></tr>");
                        }
                    }
        
                });
            }
        </script>
    	<?=$session->getTemplate('footer')?>
	</body>
</html>
<?php
	}
?>