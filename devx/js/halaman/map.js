var circle, map, marker, mapOptions, myLatlng;

function generateMsg(msg){
	return "<div class='alert alert-success alert-dismissible' role='alert' style='text-align:center; font-family: Verdana,Arial,Helvetica,sans-serif;'>"+
			"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+
			"<strong><small id='message_response'>"+msg+"</small></strong>"+
		"</div>";
}

function initialize() {
	$('#message_response_container').slideDown('fast').html(generateMsg('Harap tunggu, sedang memuat..'));
	myLatlng = new google.maps.LatLng(DATA__GPS__DATA);
	mapOptions = {	zoom: 11,center: myLatlng};
	map		=	new google.maps.Map(document.getElementById('map_content'), mapOptions);
	marker	=	new google.maps.Marker({
						position: myLatlng,
						map: map,
						draggable: false,
						title: 'Posisi saya',
						animation: google.maps.Animation.DROP
					});
	circle =	new google.maps.Circle({
					map: map,
					radius: DATA__RAD__DATA,
					fillColor: '#BFFF2C',
					strokeWeight: 1,
					strokeColor: '#E89C0C'
				});
	circle.bindTo('center', marker, 'position');
	
	google.maps.event.addListener(marker, 'dragend', function (event) {
		document.getElementById('gps_l').value	=	this.getPosition().lat();
		document.getElementById('gps_b').value	=	this.getPosition().lng();
	});

	google.maps.event.addListener(map, 'tilesloaded', function() {
		$('#message_response_container').slideUp('fast').html("");
	});

}

function updateRadius(rad){
	rad		=	rad * 1;
	circle.setMap(null);
	circle	=	new google.maps.Circle({
					map: map,
					radius: rad,
					fillColor: '#BFFF2C',
					strokeWeight: 1,
					strokeColor: '#E89C0C'
				});
	circle.bindTo('center', marker, 'position');
}

$(document).ready(function() {
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&callback=initialize';
  document.body.appendChild(script);
});

function showEditor(value){
	
	if(value == 'true'){
		$("#editor_button").hide();
		$('#editor_container').slideDown('fast');
		$('#message_response_container').slideDown('fast').html(generateMsg('Silakan geser marker sesuai dengan lokasi anda. Dan pilih radius untuk menentukan jangkauan mengajar'));
	} else {
		$("#editor_button").show();
		$('#editor_container').slideUp('fast');
		$('#message_response_container').slideUp('fast').html("");
	}

}

function saveLocation(){
	
	var gps_l	=	$("#gps_l").val();
	var gps_b	=	$("#gps_b").val();
	var radius	=	$("#jangkauan").val();

	$('#message_response_container').slideUp('fast').html("");
	$("#jangkauan").prop('disabled', true);
	marker.setOptions({draggable: false});
	$('#message_response_container').slideDown('fast').html(generateMsg('Sedang menyimpan data lokasi... Atur lokasi anda secara akurat agar murid dapat dengan mudah mencari anda.'));
	
	$.post(
			"APP_URLphp/page/map.php?func=ENCODE(setLokasi)ENCODE",
			{
				gps_l: gps_l,
				gps_b: gps_b,
				radius: radius
			}
	)
	.done(function( data ) {
		
		data			=	JSON.parse(data);
		
		if(data['respon_code'] == 'success'){
			showEditor('false');
			$('#message_response_container').slideDown('fast').html(generateMsg('Data lokasi tersimpan'));
			marker.setOptions({draggable: false});
		} else if(data['respon_code'] == 'null'){
			$('#message_response_container').slideDown('fast').html(generateMsg('Tidak ada perubahan data. Silakan coba lagi'));
			marker.setOptions({draggable: true});
		} else {
			$('#message_response_container').slideDown('fast').html(generateMsg('Telah terjadi kesalahan saat menyimpan. Silakan coba beberapa saat lagi'));
			marker.setOptions({draggable: true});
		}
		
		$("#jangkauan").prop('disabled', false);
		
	});
	
}

function closeEditor(){
	$('#message_response_container').slideUp('fast').html("");
}

function makeDraggable(){
	marker.setOptions({draggable: true});
}