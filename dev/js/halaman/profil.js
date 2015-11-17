function showComposer(id){
	if($('#p'+id).hasClass('hide')){
		$('#p'+id).slideDown('fast');
		$('#c'+id).slideUp('fast');
		$('#p'+id).removeClass('hide');
	} else {
		$('#p'+id).slideUp('fast');
		$('#c'+id).slideDown('fast');
		$('#p'+id).addClass('hide');
	}
	return false;
}

function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}

function saveData(type){

	$('#message_response_container').slideUp('fast').html("");
	$("#cmisi textarea").prop('disabled', true);

	$.post("APP_URLphp/page/profil.php?func=ENCODE(saveData)ENCODE", {value: $('#t'+type).val(), type:type})
	.done(function( data ) {
		
		data			=	JSON.parse(data);

		if(data['respon_code'] == '-1'){
			$("#cmisi textarea").prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg('Tidak ada perubahan data'));
		}  else if(data['respon_code'] == '-2'){
			$("#cmisi textarea").prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg('Terdapat kesalahan diserver. Silakan coba lagi nanti'));
		} else if(data['respon_code'] == '1') {
			$("#cmisi textarea").prop('disabled', false);
			showComposer(type);
			$('#p'+type).html($('#t'+type).val());
			$('#message_response_container').slideDown('fast').html(generateMsg('Data tersimpan'));
		} else {
			$("#cmisi textarea").prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg('Terdapat kesalahan diserver. Silakan coba lagi nanti'));
		}
		
	});
}