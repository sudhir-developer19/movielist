function showPage(page)
{
	var url =window.location.href;
	var sortFilter = $('#sortFilter').find(":selected").val();
	var generFiler = $('#generFiler').find(":selected").val();
	var languageFilter = $('#languageFilter').find(":selected").val();
	
	if (url.indexOf("?")>-1)
	{
		url = url.substr(0,url.indexOf("?"));
	}
	if(typeof page ==='undefined') var page = 1;
	url = url+"?page=" + page ;
	if (generFiler !=""){
		url = url+"&generFiler=" + generFiler ;
	}
	if (languageFilter !=""){
		url = url+"&languageFilter=" + languageFilter ;
	}
	if (sortFilter !=""){
		url = url+"&sortFilter=" + sortFilter ;
	}
	window.location.href = url ;
}