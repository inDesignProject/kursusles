$(document).ready(function() {
	$("#loading").dialog({
		hide: 'fade',
		show: 'fade',
		draggable: false,
		autoOpen: false
	});
});

function usernameValidation(usernameValue){
	
	if(usernameValue.length < 8){
		$('#username-validation').html("<span class='validation-invalid'>Username minimal harus memiliki 8 karakter</span>");
	} else if(usernameValue.indexOf(' ') >= 0){
		$('#username-validation').html("<span class='validation-invalid'>Username tidak boleh mengandung spasi</span>");
	} else {
		$.ajax({
			url: 'php/lib/ajax_response.php?f=cekDataUsername&key='+usernameValue,
			data: {
				format: 'json'
			},
			error: function() {
				return 'nodata';
			},
			type: 'GET',
			dataType: 'text',
			success: function(data) {
				if(data == 1){
					$('#username-validation').html("<span class='validation-valid'>Username valid dan diterima</span>");
				} else {
					$('#username-validation').html("<span class='validation-invalid'>Harap pilih Username lain</span>");
				}
			}
	
		});
		
	}
}
	
function password1Validation(password1Value, UsernameValue){

	var textvalid		=	'';
	if(password1Value.length < 6){
		textvalid		=	'Password minimal harus memiliki 6 karakter';
	} else if(password1Value.indexOf(' ') >= 0){
		textvalid		=	'Password tidak boleh mengandung spasi';
	} else if(password1Value == UsernameValue){
		textvalid		=	'Password tidak boleh sama dengan username';
	} else {
		$('#password1-validation').html("<span class='validation-valid'>Password valid dan diterima</span>");
		return false;
	}
	$('#password1-validation').html("<span class='validation-invalid'>"+textvalid+"</span>");
	
}

function password2Validation(password2Value){
	var	password1Value	=	document.getElementById('password1').value;
	var textvalid		=	'';
	
	if(password1Value == '') {
		textvalid		=	'Pengulangan Password tidak valid';
	} else if(password1Value != '' && password1Value.length > 6){
		
		if(password2Value != password1Value){
			textvalid		=	'Pengulangan Password tidak valid / tidak cocok';
		} else {
			$('#password2-validation').html("<span class='validation-valid'>Pengulangan Password sudah benar</span>");
			return false;
		}
		
	} else if(password1Value != '' && password1Value.length < 6) {
		textvalid		=	'Password awal yang anda masukkan tidak valid';
	} else if(password2Value == '') {
		textvalid		=	'Harap masukkan pengulangan password';
	}
	
	$('#password2-validation').html("<span class='validation-invalid'>"+textvalid+"</span>");
	
}

function emailValidation(value){
	var tes = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	if(tes.test(value)){
		$('#email-validation').html("<span class='validation-valid'>Email valid</span>");
	} else {
		$('#email-validation').html("<span class='validation-invalid'>Email tidak valid</span>");
	}
}

function submitSignUpPengajar(){

	var postData = $("#signup").serializeArray();
	var formURL = $("#signup").attr("action");
	$.ajax({
		url : formURL,
		type: "POST",
		beforeSend: function(){
			$("#loading").dialog('open').html("<p>Harap tunggu, sedang menyimpan...</p>");
			$("#signup :input").prop("disabled", true);
		},
		data : postData,
		success:function(data){

			$("#loading").dialog('close');
			$("#signup :input").prop("disabled", false);
			$("#username, #password1, #password2").prop("disabled", true);
			$("#signup :hidden").prop("disabled", false);

			var result = data.slice(3);
			console.log(result);
			if(result == '000000000000000'){
				window.location.replace("/dev/pengajar_profil.php");
			} else {
				
				var arrInput	=	['alamat','email','jns_kelamin','kecamatan','kelurahan','kodepos','kota','nama','password1','password2','propinsi','telpon','tempat_lahir','tgl_lahir','username'];
				var j			=	0;
				var k			=	1;
				var pecahResult	=	'';
				var textValid	=	'';
				
				for (var i = 0; i < result.length; i++) {
					
					pecahResult	=	result.substring(j,k);
					j++;
					k++;
					
					if(pecahResult != '0'){
						if(i == '1'){
							textValid	=	'Email tidak valid';
						} else if(i == '2' || i == '3' || i == '4' || i == '6' || i == '10'){ //Pilihan propinsi, kota, kecamatan, kelurahan
							textValid	=	'Harap pilih data yang benar';
						} else if(i == '5'){
							if(pecahResult == '1'){
								textValid	=	'Harap isi dengan angka';
							} else {
								textValid	=	'Kodepos harus berisi 5 angka';
							}
						} else if(i == '13'){
							textValid	=	'Harap pilih tanggal yang valid';
						} else if(i == '14'){
							if(pecahResult == '1'){
								textValid	=	'Username wajib diisi';
							} else {
								textValid	=	'Masukkan username yang lain';
							}
						} else {
							textValid	=	'Harap isi dengan benar';
						}

						$('#'+arrInput[i]+'-validation').html("<span class='validation-invalid'>"+textValid+"</span>");

					} else {
						$('#'+arrInput[i]+'-validation').html(" ");
					}
				}
				
			}
			
		},
		error: function(e){
			alert('Error data.');
		}
	});
}
