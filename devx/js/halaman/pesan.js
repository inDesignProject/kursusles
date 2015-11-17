function showComposer(id){
	if($('#'+id).hasClass('hide_container')){
		$('#'+id).removeClass('hide_container').addClass('show_container');
		$('#kirimpesan').hide();
		$('#tutuppesan').show();
		$('#compose_container').slideDown('fast');
	} else {
		$('#'+id).removeClass('show_container').addClass('hide_container');
		$('#tutuppesan').hide();
		$('#kirimpesan').show();
		$('#compose_container').slideUp('fast');
	}
}

function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}

function savePesan(){

	$('#message_response_container').slideUp('fast').html("");
	$('#msg_process').removeClass('hide').addClass('show msg_loading').html("Harap tunggu, sedang mengirim...");

	var sendData = $('#compose_container input, #compose_container textarea, #compose_container select, #compose_container radio, #compose_container hidden').serialize();

	$("#compose_container input, #compose_container textarea").prop('disabled', true);

	$.post("APP_URLphp/page/pesan.php?func=ENCODE(sendMessage)ENCODE",sendData)
	.done(function( data ) {
		
		data			=	JSON.parse(data);
		$('#msg_process').removeClass('show msg_loading').addClass('hide').html("");
		
		if(data['respon_code'] == '0'){
			$("#compose_container input, #compose_container textarea").prop('disabled', false);
			if($.inArray('nama',data['arrField']) > -1){$('#message_response_container').slideDown('fast').html(generateMsg('Harap isi nama anda'));};
			if($.inArray('email',data['arrField']) > -1){$('#message_response_container').slideDown('fast').html(generateMsg('Harap isi email anda'));};
			if($.inArray('pesan',data['arrField']) > -1){$('#message_response_container').slideDown('fast').html(generateMsg('Silakan isi pesan anda'));};
			if($.inArray('g-recaptcha-response',data['arrField']) > -1){$('#message_response_container').slideDown('fast').html(generateMsg('Klik pada kotak <b>Saya bukan robot</b> untuk mengirim pesan'));};
		} else if(data['respon_code'] == '-1'  || data['respon_code'] == '-2'){
			$("#compose_container input, #compose_container textarea").prop('disabled', false);
			$('#msg_process').removeClass('hide msg_loading');
			$('#message_response_container').slideDown('fast').html(generateMsg('Gagal mengirim pesan. Silakan coba lagi'));
			grecaptcha.reset();
		} else if(data['respon_code'] == '-3'){
			$("#compose_container input, #compose_container textarea").prop('disabled', false);
			$('#msg_process').removeClass('hide msg_loading');
			$('#message_response_container').slideDown('fast').html(generateMsg('Kode <b>Captcha</b> yang anda masukkan tidak valid'));
		} else if(data['respon_code'] == '1'){
			$("#compose_container input, #compose_container textarea").prop('disabled', false).val('');
			showComposer('showButton_container');
			$('#message_response_container').slideDown('fast').html(generateMsg('Pesan anda terkirim'));
			$('#apesan').click();
		}
		
	});
}