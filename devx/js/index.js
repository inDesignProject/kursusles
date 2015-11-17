function emptyOpt(id, optionPrefix){
	var select		=	document.getElementById(id);
	select.options.length = 0;
	
	var optionUp	=	document.createElement("option");
	optionUp.text	=	optionPrefix;
	optionUp.value	=	'';
	select.add(optionUp);

}