function searchData(type){
	
	if(type == 'default'){
		$('#page').val('1');
	} else {
		$('#page').val(type);
	}
	
	var data		=	$("input, textarea, select, checkbox").serialize();
	
	$.ajax({
		beforeSend	: function(){
			$('#searchResult').slideUp('fast').html("<center><img src='APP_IMAGE_URLloading.gif'/><br/>Sedang Memuat...</center>");
			$('input, textarea, select, checkbox').prop('disabled', true);
		},
		complete	: function(){
		},
		type	: "POST",
		url		: "APP_URLsearch_detail.php?func=ENCODE(searchData)ENCODE",
		data	: data,
		success : function(result) {
			
			data	=	JSON.parse(result);
			$('#searchResult').slideDown('fast').html(data['result']);
			$('#pageData').html(data['pageData']);
			$('#pageButton').html(data['pageButton']);
			$('input, textarea, select, checkbox').prop('disabled', false);
			
		},
		error: function(){
			alert('Error saat mengirim permintaan');
		}
	});
	return true;
}