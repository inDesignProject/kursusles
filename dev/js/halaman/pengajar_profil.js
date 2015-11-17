function goToPaket(){
	window.location.href='APP_URLbelipaket?q='+$('input[name=paket_belajar]:checked', '#paket-container').val();
}
function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}
function getContent(id){
	
	$("#tab_content").html("<div class='modal loading'></div>");
	
	var content	=	"";
	switch(id) {
		case 'map'		:	content	=	"APP_URLphp/page/map.php?q=DATA__PARAM__DATA";
							break;
		case 'pesan'	:	content	=	"APP_URLphp/page/pesan.php?s=profile&q=DATA__PARAM__DATA";
							break;
		case 'level'	:	content	=	"APP_URLphp/page/level.php?s=profile&q=DATA__PARAM__DATA";
							break;
		case 'testimoni':	content	=	"APP_URLphp/page/testimoni.php?s=profile&q=DATA__PARAM__DATA";
							break;
		case 'Kjadwal'	:	content	=	"APP_URLphp/page/Kjadwal.php?s=profile&q=DATA__PARAM__DATA";
							break;
		case 'profil'	:	content	=	"APP_URLphp/page/profil.php?s=profile&q=DATA__PARAM__DATA";
							break;
		default			:	content	=	"APP_URLphp/page/map.php?q=DATA__PARAM__DATA";
							break;
	}
	
	if( id != 'level'){
		$("#tab_content").load(content);
	} else {
		$("#segmenKeahlian").load(content);
	}
}

function openWindowUpload(){
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
		  $(this).html("<object id='object_uploader' type='type/html' data='APP_URLphp/page/upload_foto.php' width='100%' height='400px'></object>");
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
					$.post("APP_URLphp/page/upload_foto.php?func=ENCODE(setFoto)ENCODE")
					.done(function( data ) {
						
						data			=	JSON.parse(data);
						if(data['respon_code'] != 1){
							msgElem.className	=	"error";
							msgTxt.innerHTML	=	data['respon_message'];
							blink(msgElem, 4, 150);
							return false;
						} else {
							$("#photo_detail").html("<img class='img-circle' style='width: 132px;' src='APP_IMAGE_URLgenerate_pic.php?type=pr&w=132&h=132&q="+data['respon_message']+"'/>");
							$("#dialog-confirm").html("");
							window.location.href	=	'APP_URLpengajar_profil';
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
function blink(elem, times, speed) {
	if (times > 0 || times < 0) {
		if ($(elem).hasClass("blink")) {
			$(elem).removeClass("blink");
		} else {
			$(elem).addClass("blink");
		}
	}

	clearTimeout(function () {
		blink(elem, times, speed);
	});

	if (times > 0 || times < 0) {
		setTimeout(function () {
			blink(elem, times, speed);
		}, speed);
		times -= .5;
	}
}
function closeEditor(){
	$("#editor_status").hide().removeClass("loading success error");
	$("#editor_message").html("");
}
function addBookmark(value){
	$('#message_response_container').slideUp('fast').html("");
	$.post("APP_URLpengajar_profil?func=ENCODE(addBookmark)ENCODE", {iddata : value})
	.done(function( data ) {
		
		data			=	JSON.parse(data);
		if(data['respon_code'] == "00000"){
			$('#message_response_container').slideDown('fast').html(generateMsg("Bookmark sudah ditambahkan. Cek bookmark di halaman utama pada tab Bookmark"));
		} else {
			$('#message_response_container').slideDown('fast').html(generateMsg(data['respon_msg']));
		}

	});
	
}

$('#apesan').click();