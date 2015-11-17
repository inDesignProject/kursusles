function getDataOpt(urlFunc, param, id, key, optPrefix, optPrefixVal){
		
	if(typeof optPrefixVal === 'undefined'){
		optPrefixVal	=	'';
	};

	$.ajax({
		url: 'php/lib/ajax_response.php?f='+urlFunc+'&'+param+'='+key,
		data: {
			format: 'json'
		},
		error: function() {
			return 'nodata';
		},
		type: 'GET',
		dataType: 'text',
		success: function(data) {

			var select		=	document.getElementById(id);
			select.options.length = 0;
			
			var optionUp	=	document.createElement("option");
			optionUp.text	=	optPrefix;
			optionUp.value	=	optPrefixVal;
			select.add(optionUp);

			if(data.length>0){ 
				
				data = JSON.parse(data);
				for(var i = 0; i < data.length; i++) {

					var obj			=	data[i];
					var option		=	document.createElement("option");
					option.text		=	obj[1];
					option.value	=	obj[0];
					select.add(option);

				}
				
			} else { 
				document.getElementById(id).value = "";
			}
			
		}

	});
	
}

function getDataKotaByInput(key, id){
		
	$.ajax({
		beforesend: $('#'+id).prop('readonly', true),
		//langsung ambil semua data kota via url
		url: 'php/lib/ajax_response.php?f=getDataKota',
		data: {
			format: 'json'
		},
		error: function() {
			return 'nodata';
		},
		type: 'GET',
		dataType: 'text',
		complete: $('#'+id).prop('readonly', false),
		success: function(data) {
			
			$('#'+id).autocomplete({
				source: $.parseJSON(data)
			});
			
		}

	});
	
}

function getDataPropinsiByInput(key, id){
		
	$.ajax({
		beforesend: $('#'+id).prop('readonly', true),
		url: 'php/lib/ajax_response.php?f=getDataPropinsi&type=input',
		data: {
			format: 'json'
		},
		error: function() {
			return 'nodata';
		},
		type: 'GET',
		dataType: 'text',
		complete: $('#'+id).prop('readonly', false),
		success: function(data) {
			
			$('#'+id).autocomplete({
				source: $.parseJSON(data)
			});
			
		}

	});
	
}

function getDataBidangByInput(key, id){
		
	$.ajax({
		beforesend: $('#'+id).prop('readonly', true),
		url: 'php/lib/ajax_response.php?f=getDataBidang&type=input',
		data: {
			format: 'json'
		},
		error: function() {
			return 'nodata';
		},
		type: 'GET',
		dataType: 'text',
		complete: $('#'+id).prop('readonly', false),
		success: function(data) {
			
			$('#'+id).autocomplete({
				source: $.parseJSON(data)
			});
			
		}

	});
	
}

function getDataPosisiByInput(key, id){
		
	$.ajax({
		beforesend: $('#'+id).prop('readonly', true),
		url: 'php/lib/ajax_response.php?f=getDataPosisi&type=input',
		data: {
			format: 'json'
		},
		error: function() {
			return 'nodata';
		},
		type: 'GET',
		dataType: 'text',
		complete: $('#'+id).prop('readonly', false),
		success: function(data) {
			
			$('#'+id).autocomplete({
				source: $.parseJSON(data)
			});
			
		}

	});
	
}

function getDataPendidikanByInput(key, id){
		
	$.ajax({
		beforesend: $('#'+id).prop('readonly', true),
		url: 'php/lib/ajax_response.php?f=getDataPendidikan&type=input',
		data: {
			format: 'json'
		},
		error: function() {
			return 'nodata';
		},
		type: 'GET',
		dataType: 'text',
		complete: $('#'+id).prop('readonly', false),
		success: function(data) {
			
			$('#'+id).autocomplete({
				source: $.parseJSON(data)
			});
			
		}

	});
	
}