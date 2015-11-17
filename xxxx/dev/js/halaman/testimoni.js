function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}

function showComposerTesti(id){
	if($('#'+id).hasClass('hide_Testicontainer')){
		$('#'+id).removeClass('hide_Testicontainer').addClass('show_Testicontainer');
		$('#kirimtesti').hide();
		$('#tutuptesti').show();
		$('#composeTesti_container').slideDown('fast');
	} else {
		$('#'+id).removeClass('show_Testicontainer').addClass('hide_Testicontainer');
		$('#tutuptesti').hide();
		$('#kirimtesti').show();
		$('#composeTesti_container').slideUp('fast');
	}
}

function saveTesti(){

	$('#message_response_container').slideDown('fast').html("Harap tunggu, sedang mengirim...");
	$("#testi, #star-1, #star-2, #star-3, #star-4, #star-5").prop('disabled', true);
	
	$.post(
			"APP_URLphp/page/testimoni.php?func=ENCODE(sendTesti)ENCODE",
			{
				idpengajar: $("#idpengajar").val(),
				testi: $("#testi").val(),
				rate: $('input[name="star"]:checked', '#rating').val()
			}
	)
	.done(function( data ) {
		
		data			=	JSON.parse(data);
		$('#testimsg_process').removeClass('show testimsg_loading').addClass('hide').html("");
		
		if(data['respon_code'] == '0'){
			$("#testi, #star-1, #star-2, #star-3, #star-4, #star-5").prop('disabled', false);
			if($.inArray('testi',data['arrField']) > -1){$('#message_response_container').slideDown('fast').html(generateMsg("Harap tuliskan testimonial anda"))};
			if($.inArray('rate',data['arrField']) > -1){$('#message_response_container').slideDown('fast').html(generateMsg("Harap pilih rating yang diingikan"))};
		} else if(data['respon_code'] == '-1'  || data['respon_code'] == '-2'){
			$("#testi, #star-1, #star-2, #star-3, #star-4, #star-5").prop('disabled', false);
			$('#message_response_container').slideDown('fast').html(generateMsg("Gagal mengirim testimonial. Silakan coba lagi"));
		} else if(data['respon_code'] == '1'){
			$('#atestimoni').click();
		}
		
	});
}