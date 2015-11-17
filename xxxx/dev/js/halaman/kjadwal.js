var jumAsal	= DATA__jumAsal__DATA;

function showEditor(value){
	
	if(value == 'true'){
		$(".avilable").removeClass("avilable").addClass('unavilable');
		$("#ubahjadwal").hide();
		$(".checkJadwal").show();
		$("#simpanjadwal").show();
	} else {
		
		var checked = $('#Kjadwal_table input:checked');
		
		for (var i = 0; i < checked.length; i++) {
			$('#'+checked[i].id).parent().find(".fa-lg").addClass('avilable').removeClass('unavilable');
		}
		
		$("#simpanjadwal").hide();
		$(".checkJadwal").hide();
		$("#ubahjadwal").show();
	}

}

function saveJadwal(){
	
	var checked = $('#Kjadwal_table input:checked');
	var typePost;
	var arrInput = [];
	
	$('#msg_process').removeClass('hide').addClass('show msg_loading').html('Harap tunggu, sedang menyimpan...');
	$(".checkJadwal").prop('disabled', true);
	
	if(checked.length <= 0 && jumAsal == 0){
		$('#msg_process').removeClass('show msg_loading').addClass('hide');
		$(".checkJadwal").prop('disabled', false);
		showEditor('false');
		return false;
	} else if(checked.length <= 0){
		typePost	=	'reset';
		$('#ubahjadwal').val('Susun Jadwal');
	} else {
		typePost	=	'update';
		$('#ubahjadwal').val('Ubah Jadwal');
	}

	for (var i = 0; i < checked.length; i++) {
		arrInput.push(checked[i].id);
	}
	
	$.post(
			"APP_URLphp/page/Kjadwal.php?func=ENCODE(saveJadwal)ENCODE",
			{
				typePost : typePost,
				arrInput : arrInput,
				idpengajar : $('#idpengajar').val()
			}
	)
	.done(function( data ) {
		
		data			=	JSON.parse(data);
		$(".checkJadwal").prop('disabled', false);

		if(data['respon_code'] == 'success'){
			showEditor('false');
			$('#msg_process').removeClass('show msg_loading').addClass('hide');
			jumAsal	=	checked.length;
		} else if(data['respon_code'] == 'null'){
			$('#msg_process').removeClass('hide msg_loading').addClass('show').html('Tidak ada perubahan data. Silakan coba lagi');
		} else {
			$('#msg_process').removeClass('hide msg_loading').addClass('show').html('Gagal menyimpan data. Coba lagi nanti');
		}
		
	});
}