<?php

	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();

	session_start();
	$sql		=	sprintf("SELECT FOTO FROM m_pengajar WHERE IDPENGAJAR = %s", $_SESSION['KursusLes']['IDUSER']);
	$result		=	$db->query($sql);
	$foto		=	$result[0]['FOTO'];
	
	//FUNGSI DIGUNAKAN UNTUK MENYIMPAN DATA PERUBAHAN FOTO PROFILE
	if( $enkripsi->decode($_GET['func']) == "setFoto" && isset($_GET['func'])){
		
		$filename	=	$enkripsi->encode($_SESSION['KursusLes']['IDPRIME']).'.jpg';
		
		//RENAME FILE LAMA JIKA ADA
		if(file_exists('../../images/profile/'.$filename)){
			rename('../../images/profile/'.$filename, '../../images/profile/rn_'.date('YmdHis').$filename);
		}
		
		//RENAME TEMPORARY FILE
		if(file_exists('../../images/profile/tmp_'.$filename)){
			
			$sqlUpdate	=	sprintf("UPDATE m_pengajar
									 SET FOTO			=	'%s'
									 WHERE IDPENGAJAR	=	%s"
									, $filename
									, $_SESSION['KursusLes']['IDUSER']
									);
			$affected	=	$db->execSQL($sqlUpdate, 0);
	
			//JIKA DATA SUDAH UPDATE DAN RENNAME TMP
			if(rename('../../images/profile/tmp_'.$filename, '../../images/profile/'.$filename)){
				echo json_encode(array("respon_code"=>"1",
								   	   "respon_message"=>$enkripsi->encode($filename)));
			} else {
				echo json_encode(array("respon_code"=>"0",
									   "respon_message"=>"Gagal menyimpan data. Silakan ulangi lagi")
								);
			}
		
		} else {
		
			echo json_encode(array("respon_code"=>"-1",
								   "respon_message"=>"Foto yang anda upload tidak kami temukan. Silakan ulangi lagi"));
			
		}
		
		die();
		
	}
	
?>
<!doctype html>
<html>
<head>

    <link rel="stylesheet" type="text/css" media="all" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryjcrop');?>.cssfile" />
    <link rel="stylesheet" type="text/css" media="all" href="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssfileuploader');?>.cssfile" />
	<style>
    @import url("<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'cssjqueryui');?>.cssfile");
	<?php
	if($foto <> "" && $foto <> 'null'){
		$style_foto		=	"background: url('".APP_IMG_URL."generate_pic.php?type=pr&q=".$enkripsi->encode($foto)."') no-repeat scroll -8px top / cover transparent;";
	}
	?>
	</style>
</head>
<body>
	<div id="upload_container">
        <div id="userpic" class="userpic">
            <div class="js-preview userpic__preview" style="<?=$style_foto?>"></div>
            <div class="btn btn-success js-fileapi-wrapper">
                <div class="js-browse">
                    <span class="btn-txt">Pilih File</span>
                    <input type="file" name="filedata"/>
                    <input type="hidden" name="resultupload" id="resultupload" />
                </div>
                <div class="js-upload" style="display: none;">
                    <div class="progress progress-success"><div class="js-progress bar"></div></div>
                    <span class="btn-txt">Mengunggah</span>
                </div>
            </div>
        </div>
	</div>
	<div id="popup" class="popup" style="display: none;">
		<div class="popup__body"><div class="js-img"></div></div>
		<div style="margin: 0 0 5px; text-align: center;">
			<div class="js-upload btn btn_browse btn_browse_small">Unggah</div>
		</div>
	</div>

    <div id="editor_status" class="info">
        <span id="close_editor" onClick="closeEditor()" title="Tutup pesan">x</span>
        <p id="editor_message">
        	<?=$foto == '' || $foto == 'null' ? "Anda belum memiliki foto profil. Silakan pilih dan upload dengan klik tombol <b>Pilih File</b>" : "Silakan pilih file yang akan diunggah"?>
        </p>
    </div>

    <div id="dialog-confirm">
      <p id="text_dialog"></p>
    </div>

	<script src="../lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymin');?>.jsfile"></script>
	<script src="../lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryuimin');?>.jsfile"></script>

	<script>

		var examples = [];

		examples.push(function (){
			var response;
			$('#userpic').fileapi({
				url: '../lib/upload_foto_ctrl.php',
				accept: 'image/jpg,image/jpeg',
				imageSize: { minWidth: 20, minHeight: 20 },
				elements: {
					active: { show: '.js-upload', hide: '.js-browse' },
					preview: {
						el: '.js-preview',
						width: 200,
						height: 200
					},
					progress: '.js-progress'
				},
				onSelect: function (evt, ui){
					var file = ui.files[0];
					
					var fileExtension = file.name.substr((file.name.lastIndexOf('.') + 1));
					
					if($.inArray( fileExtension, ['jpg','jpeg'] ) < 0){
						$("#dialog-confirm").dialog({
							closeOnEscape: false,
							resizable: false,
							modal: true,
							width: "33%",
							title: "Pemberitahuan",
							open: function() {
								$(this).html("File ekstensi yang anda pilih tidak diperbolehkan.<br/>File harus berupa .jpg / .jpeg");
								$("#editor_status").removeClass("loading success info").addClass("error");
								$("#editor_status").show();
								$("#close_editor").show();
								$("#editor_message").html("Jenis file harus jpg ata jpeg");

							},
							close: function() {
								$(this).dialog( "close" );
							},
							buttons: {
								"Ok": function() {
									$( this ).dialog( "close" );
								}
							}
						});
						return false;
					}
					
					if( !FileAPI.support.transform ) {
						alert('Your browser does not support Flash :(');
					} else if( file ) {
						$('#popup').modal({
							closeOnEsc: true,
							closeOnOverlayClick: true,
							onOpen: function (overlay){
								$(overlay).on('click', '.js-upload', function (){
									$.modal().close();
									$('#userpic').fileapi('upload');
								});

								$('.js-img', overlay).cropper({
									file: file,
									bgColor: '#fff',
									maxSize: [$(window).width()-100, $(window).height()-100],
									minSize: [200, 200],
									selection: '90%',
									onSelect: function (coords){
										$('#userpic').fileapi('crop', file, coords);
									}
								});
							}
						}).open();
					}
				},
				
				onComplete: function (err, xhr, file){
					
					response		=	xhr.result;
					var message;
					
					$("#editor_status").show();
					$("#resultupload").val(response);

					switch(response){
						case -1		:	message = 'Gagal mengunggah. Silakan coba lagi'; break;
						case -2		:	message = 'File yang anda unggah rusak. Silakan pilih file lainnya'; break;
						case -3		:	message = 'Ukuran file melebihi batas. Pilih file lainnya'; break;
						case -4		:	message = 'Gagal mengunggah foto. Silakan coba lagi'; break;
						case -5		:	message = 'Foto yang diunggah terlalu kecil. Silakan pilih file lain'; break;
						case 1		:	message = 'Berhasil mengunggah. Simpan pengaturan profil anda?'; break;
						default		:	message = 'Error Lainnya. Silakan coba lagi nanti'; break;
					}
					
					if(response != 1){

						if(response == -5){
							$(".js-preview").html("");
						}

						$("#editor_status").removeClass("loading success info").addClass("error");
						$("#close_editor").show();
						$("#editor_message").html(message);
					} else {
						$("#editor_status").removeClass("loading error info").addClass("success");
						$("#close_editor").hide();
						$("#editor_message").html(message);
					}

				}
			
			});
			
		});
		
		var FileAPI = {
			  debug: true
			, media: true
			, staticPath: 'FileAPI/'
		};
		
		$(function ($){
			function _getCode(node, all){
				var code = FileAPI.filter($(node).prop('innerHTML').split('\n'), function (str){ return !!str; });
				if( !all ){
					code = code.slice(1, -2);
				}

				var tabSize = (code[0].match(/^\t+/) || [''])[0].length;

				return $('<div/>')
					.text($.map(code, function (line){
						return line.substr(tabSize).replace(/\t/g, '   ');
					}).join('\n'))
					.prop('innerHTML')
						.replace(/ disabled=""/g, '')
						.replace(/&amp;lt;%/g, '<%')
						.replace(/%&amp;gt;/g, '%>')
				;
			}
			FileAPI.each(examples, function (fn){
				fn();
			});
		});
	</script>
	<script src="../lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssfileapiexif');?>.jsfile"></script>
	<script src="../lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssfileapi');?>.jsfile"></script>
	<script src="../lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryfileapi');?>.jsfile"></script>
	<script src="../lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjqueryjcrop');?>.jsfile"></script>
	<script src="../lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssjquerymodal');?>.jsfile"></script>

</body>
</html>