function openWindowUploadMurid(){
	$("#dialog-confirm").dialog({
		closeOnEscape: false,
		resizable: true,
		modal: true,
		minWidth: 500,
		title: "Upload File",
		position: {
			my: 'top', 
			at: 'top'
		},
		open: function() {
		  $(this).html("<object id='object_uploader' type='type/html' data='APP_URLphp/page/upload_foto_murid.php' width='100%' height='400px'></object>");
		},
		close: function() {
			$(this).dialog( "close" );
		},
		buttons: {
			"Simpan": function() {
				
				var objUploader	=	document.getElementById("object_uploader");
				var contentUpl	=	objUploader.contentDocument;
				var resultUpload=	contentUpl.getElementById("resultupload").value;
				var msgElem		=	contentUpl.getElementById("editor_status");
				var msgTxt		=	contentUpl.getElementById("editor_message");
				var data;
				
				if(resultUpload != 1){
					blink(msgElem, 4, 150);
				} else {
					$.post("APP_URLphp/page/upload_foto_murid.php?func=ENCODE(setFoto)ENCODE")
					.done(function( data ) {
						
						data			=	JSON.parse(data);
						console.log(data['respon_code']);
						if(data['respon_code'] != 1){
							msgElem.className	=	"error";
							msgTxt.innerHTML	=	data['respon_message'];
							blink(msgElem, 4, 150);
							return false;
						} else {
							window.location.href	=	'APP_URLindex_murid';
						}
					});
				}
						
				$(this).dialog("close");

			},
			"Batal": function() {
				$( this ).dialog( "close" );
			}
		}
	});
}

function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}

function resetPwd(){

	$('#message_response_container').slideDown('fast').html(generateMsg("Harap tunggu, sedang menyimpan"));
	var sendData = $('#resetPwd input, #resetPwd textarea, #resetPwd select, #resetPwd radio, #resetPwd hidden').serialize();

	$("#resetPwd input, #resetPwd textarea").prop('disabled', true);

	$.post( "APP_URLphp/page/mprofil.php?func=ENCODE(resetPassword)ENCODE", sendData)
	.done(function( data ) {
		
		$("#resetPwd input, #resetPwd textarea").prop('disabled', false);

		if(data == '00001'){
			$('#message_response_container').slideDown('fast').html(generateMsg("Pengulangan password baru tidak sama"));
		} else if(data == '00002'){
			$('#message_response_container').slideDown('fast').html(generateMsg("Anda harus mengisi password baru dan minimal sepanjang 8 karakter"));
		} else if(data == '00003'){
			$('#message_response_container').slideDown('fast').html(generateMsg("Password lama yang anda masukkan tidak valid"));
		} else if(data == '00004'){
			$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data. Silakan masukkan password baru yang berbeda dari sebelumnya"));
		} else if(data == '00000'){
			$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan. Kami juga sudah mengirimkan email sebagai pengingat"));
			$("#passwordlama, #passwordbaru1, #passwordbaru2").val('');
		} else {
			$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
		}
		
	});
}
function showComposer(){
	if($('#ptentang').hasClass('hide')){
		$('#ptentang').slideDown('fast');
		$('#ctentang').slideUp('fast');
		$('#ptentang').removeClass('hide');
	} else {
		$('#ptentang').slideUp('fast');
		$('#ctentang').slideDown('fast');
		$('#ptentang').addClass('hide');
	}
	return false;
}
function editAbout(){

	$('#message_response_container').slideDown('fast').html(generateMsg("Harap tunggu, sedang menyimpan"));
	$("#ttentang").prop('disabled', true);

	$.post("APP_URLphp/page/mprofil.php?func=ENCODE(saveProfil)ENCODE", {value: $('#ttentang').val()})
	.done(function( data ) {
alert(data);
		$("#ttentang").prop('disabled', false);
		if(data == '00001'){
			$('#message_response_container').slideDown('fast').html(generateMsg("Tidak ada perubahan data"));
		} else if(data == '00000'){
			$('#message_response_container').slideDown('fast').html(generateMsg("Data tersimpan."));
			showComposer();
			$('#txttentang').html($("#ttentang").val());
		} else {
			$('#message_response_container').slideDown('fast').html(generateMsg("Error di server. Silakan coba lagi nanti"));
		}
		
	});
}