function addRemoveList(value, id){
	
	$("#loading").addClass("loading");
	
	$.post( "APP_URLphp/page/level.php?func=ENCODE(cekDataMapel)ENCODE", {listMapel: value})
	.done(function( data ) {
		
		$("#loading").removeClass("loading");
		data			=	JSON.parse(data);

		var responCode	=	data['respon_code'];
		var strLevel	=	data['level'];
		var strMapel	=	data['nama_mapel'];
		alert(data);
		if(responCode == '0'){
			$("#dialog-confirm").dialog({
				closeOnEscape: false,
				modal: true,
				width: "33%",
				title: "Pemberitahuan",
				draggable: false,
				resizable: false,
				position: { my: "center", at: "top" },
				open: function() {
				  $(this).html("Data yang anda cari tidak ditemukan");
				},
				close: function() {
					$(this).dialog( "close" );
					$("#"+id).prop('checked', false);
				},
				buttons: {
					"Ok": function() {
						$( this ).dialog( "close" );
						$("#"+id).prop('checked', false);
					}
				}
			});
			
		} else if(responCode == '1') {
			$("#dialog-confirm").dialog({
				closeOnEscape: false,
				modal: true,
				width: "33%",
				title: "Konfirmasi",
				draggable: false,
				resizable: false,
				position: { my: "center", at: "top" },
				open: function() {
				  $(this).html("Yakin menambahkan keahlian "+strMapel+" - ("+strLevel+") ke dalam list anda?");
				},
				close: function() {
					$(this).dialog( "close" );
					$("#"+id).prop('checked', false);
				},
				buttons: {
					"Ya": function() {
						$(this).dialog( "close" );
						$("#loading").addClass("loading");
						$.post( "APP_URLphp/page/level.php?func=ENCODE(tambahDataMapel)ENCODE", {listMapel: value})
						.done(function( datatambah ) {
							datatambah			=	JSON.parse(datatambah);
							
							if(datatambah['respon_code'] != "success"){
								$("#dialog-confirm").dialog({
									closeOnEscape: false,
									modal: true,
									width: "33%",
									title: "Kesalahan",
									draggable: false,
									resizable: false,
									position: { my: "center", at: "top" },
									open: function() {
									  $(this).html("Gagal menambahkan data keahlian. Silakan coba lagi nanti");
									},
									close: function() {
										$(this).dialog( "close" );
										$("#"+id).prop('checked', false);
									},
									buttons: {
										"Ok": function() {
											$( this ).dialog( "close" );
											$("#"+id).prop('checked', false);
										}
									}
								});
							} else {
								window.location.reload();
							}
							
						});
						$("#loading").removeClass("loading");
					},
					"Tidak": function() {
						$(this).dialog( "close" );
						$("#"+id).prop('checked', false);
					}
				}
			});
		} else {
			$("#dialog-confirm").dialog({
				closeOnEscape: false,
				modal: true,
				width: "33%",
				title: "Konfirmasi",
				draggable: false,
				resizable: false,
				position: { my: "center", at: "top" },
				close: function() {
					$(this).dialog( "close" );
					$("#"+id).prop('checked', true);
				},
				open: function() {
				  $(this).html("Yakin menghapus keahlian "+strMapel+" ("+strLevel+") dari list anda?");
				},
				buttons: {
					"Ya": function() {
						$(this).dialog( "close" );
						$("#loading").addClass("loading");
						$.post( "APP_URLphp/page/level.php?func=ENCODE(kurangDataMapel)ENCODE", {listMapel: value})
						.done(function( datakurang ) {
							datakurang			=	JSON.parse(datakurang);
							
							if(datakurang['respon_code'] != "success"){
								$("#dialog-confirm").dialog({
									closeOnEscape: false,
									modal: true,
									width: "33%",
									title: "Kesalahan",
									draggable: false,
									resizable: false,
									position: { my: "center", at: "top" },
									open: function() {
									  $(this).html("Gagal menghapus data keahlian. Silakan coba lagi nanti");
									},
									close: function() {
										$(this).dialog( "close" );
										$("#"+id).prop('checked', true);
									},
									buttons: {
										"Ok": function() {
											$( this ).dialog( "close" );
											$("#"+id).prop('checked', true);
										}
									}
								});
							} else {
								window.location.reload();
							}
							
						});
						$("#loading").removeClass("loading");
					},
					"Tidak": function() {
						$( this ).dialog( "close" );
						$("#"+id).prop('checked', true);
					}
				}
			});
		}
	});
}

function openPaketEditor(id){
	var splitData	=	id.split("-");
		idEditor	=	"paket-"+splitData[2];
	
	if(!$('#'+id).hasClass('opened')){
		$('.tab-paket2-editor').slideUp('fast').removeClass('tab-paket2-editor-opened');
		$('.open-paket-editor').removeClass('opened');
		$('#'+id).addClass('opened');
		$('#'+idEditor).slideDown('fast').addClass('tab-paket2-editor-opened');
	}
	
	return false;
}

function simpanPaket(id){
	
	var splitData	=	id.split("-");
		idMain		=	splitData[1];
		idEditor	=	"paket-"+splitData[1];
		data		=	$('#'+idEditor+" input, #"+idEditor+" textarea, #"+idEditor+" select").serialize();

	$.ajax({
		beforeSend	: function(){
			$('#message_response_container').slideUp('fast').html("");
			$('#'+idEditor+' input, #'+idEditor+' textarea, #'+idEditor+' select').prop('disabled', true);
		},
		complete	: function(){
		},
		type	: "POST",
		url		: "APP_URLphp/page/level.php?func=ENCODE(simpanDataPaket)ENCODE",
		data	: data,
		success : function(result) {
			
			data	=	JSON.parse(result);
			if(data['respon_code'] == "success"){
				$('#message_response_container').slideDown('fast').html(""+
				"<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
					"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
					"<strong><small id='message_response'>Data Paket Tersimpan</small></strong>"+
				"</div>");
				$('#'+idEditor).slideUp('fast').removeClass('tab-paket2-editor-opened');
				$('#btn-'+idEditor).removeClass('opened').val('Lihat Paket');
			} else {
				$('#message_response_container').slideDown('fast').html(""+
				"<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
					"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
					"<strong><small id='message_response'>"+data['respon_msg']+"</small></strong>"+
				"</div>");
			}
			
			$('#'+idEditor+' input, #'+idEditor+' textarea, #'+idEditor+' select').prop('disabled', false);
		},
		error: function(){
			
		}
	});
	return true;
}