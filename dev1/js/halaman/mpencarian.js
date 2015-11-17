$(document).ready(function() {
	getDataOpt('getDataJenjang','noparam','idjenjang','','- Pilih Jenjang Pendidikan -');
	if($('#tglbatas').length > 0){
		$('#tglbatas').datetimepicker({
			timepicker:false,
			format:'Y-m-d',
			minDate: 0,
			lang:'id',
			closeOnDateSelect:true
		});
	}
});

$('#addForm').validate({
	rules: {
		judul: {minlength: 3, maxlength: 75, required: true},
		harga: {required: true, digits: true},
		jmlpertemuan: {minlength: 1, maxlength: 2, required: true, digits: true},
		jmlmurid: {minlength: 1, maxlength: 2, required: true, digits: true},
		tglbatas: {required: true},
		keterangan: {required: true},
		idmapel: {required: true}
	},
	highlight: function(element) {
		$(element).closest('.form-row').addClass('has-error');
	},
	unhighlight: function(element) {
		$(element).closest('.form-row').removeClass('has-error');
	},
	errorElement: 'span',
	errorClass: 'help-block',
	errorPlacement: function(error, element) {
		if(element.parent('.form-row').length) {
			error.insertAfter(element.parent());
		} else {
			error.insertAfter(element);
		}
	}
});

function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}
		
function addData(){
	$('#message_response_container').slideUp('fast').html("");
	var valid	=	$('#addForm').valid();
	
	if(valid == true){
		
		var data		=	$('#addForm input, #addForm textarea, #addForm select, #addForm radio').serialize();
				
		$.ajax({
			beforeSend	: function(){
				$('#message_response_container').slideUp('fast').html("");
				$('#addForm input, #addForm textarea, #addForm select, #addForm radio').prop('disabled', true);
			},
			complete	: function(){
				$('#addForm input, #addForm textarea, #addForm select, #addForm radio').prop('disabled', false);
			},
			type	: "POST",
			url		: "APP_URLphp/page/mpencarian.php?func=ENCODE(addData)ENCODE",
			data	: data,
			success : function(result) {
				
				data	=	JSON.parse(result);
				if(data['respon_code'] == "00000"){
					$('#message_response_container').slideDown('fast').html(generateMsg('Data tersimpan'));
					$('#tableData').prepend(data['respon_msg']);
					$('#addForm input, #addForm textarea, #addForm select').val('');
					getDataOpt('getDataJenjang','noparam','idjenjang','','- Pilih Jenjang Pendidikan -');
					$('#submit').val('Simpan');
					$('#jns_paket1').val('1');
					$('#jns_paket2').val('2');
					if($('#rownodata').length > 0){
						$('#rownodata').remove();
					}
				} else {
					$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
				}
				
				$('#addForm input, #addForm textarea, #addForm select, #addForm radio').prop('disabled', false);
			},
			error: function(){
				$('#message_response_container').slideDown('fast').html(generateMsg('Error di server. Silakan coba lagi nanti'));
			}
		});
	} else {
		$('#message_response_container').slideDown('fast').html(generateMsg('Lengkapi data isian anda'));
	}

}

function hpsData(value){

	$('#message_response_container').slideDown('fast').html(generateMsg("Harap tunggu, sedang menghapus"));

	$.post( "APP_URLphp/page/mpencarian.php?func=ENCODE(hpsData)ENCODE", {value : value})
	.done(function( data ) {
		
		data	=	JSON.parse(data);
		if(data['respon_code'] == '00000'){
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
			$('#row'+value).remove();
			if($('.rowdata').length <= 0){
				$('#tableData').prepend("<tr id='rownodata'><td colspan ='7'><center>Tidak ada data</center></td></tr>");
			}
		} else {
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
		}
		
	});
	
}